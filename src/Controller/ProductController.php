<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use \Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\ProductMedia;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException ;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException ;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException ;
use \Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ProductController extends AbstractController
{
    public function __construct(
        private ProductService $productService
    ) {
    }

    #[Route('/product', name: 'app_product', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginatorInterface): Response
    {
        $products = $this->productService->getAll();
        $products = $paginatorInterface->paginate(
            $products,
            $request->query->getInt('page', 1),
            8
        );
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $product = $this->productService->getById($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isArtisan = $this->isGranted('ROLE_ARTISAN');

        if (!$isAdmin && !$isArtisan) {
            if ($user instanceof User && $user->getArtisan()) {
                $artisan = $user->getArtisan();
                if ($artisan->getApprovalStatus() === 'PENDING') {
                    $this->addFlash('warning', 'Votre demande pour devenir artisan est en cours d\'examen. Vous pourrez ajouter des produits une fois votre profil approuvé par un administrateur.');
                } else if ($artisan->getApprovalStatus() === 'REJECTED') {
                    $this->addFlash('error', 'Votre demande artisan a été rejetée. Veuillez contacter un administrateur.');
                } else {
                    $this->addFlash('info', 'Votre profil artisan nécessite une approbation pour ajouter des produits.');
                }
            } else {
                $this->addFlash('error', 'Vous devez être artisan pour ajouter un produit. <a href="' . $this->generateUrl('app_become_artisan') . '">Devenir artisan</a>');
            }
            return $this->redirectToRoute('app_home');
        }

        if ($isArtisan && !$isAdmin && (!$user instanceof User || !$user->getArtisan())) {
            $this->addFlash('error', 'Complétez votre profil artisan avant d\'ajouter un produit.');
            return $this->redirectToRoute('app_home');
        }

        $product = new Product();
        $product->setIsActive(true);
        $product->setCreatedAt(new \DateTimeImmutable());
        $product->setUpdatedAt(new \DateTime());

        $artisanOwner = null;
        if ($isArtisan && !$isAdmin && $user instanceof User) {
            $artisanOwner = $user->getArtisan();
            if ($artisanOwner) {
                $product->addArtisan($artisanOwner);
                if ($artisanOwner->getCooperative()) {
                    $product->setCooperative($artisanOwner->getCooperative());
                }
            }
        }

        $form = $this->createForm(ProductType::class, $product, [
            'is_admin' => $isAdmin,
            'is_artisan' => $isArtisan
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $product->getTitre())));
                $product->setSlug($slug);

                $errors = [];
                if ($productRepository->findDuplicateBySlug($slug)) {
                    $errors[] = 'Un produit avec le même titre (slug) existe déjà.';
                }

                $coopId = $product->getCooperative()?->getId();
                if ($productRepository->findDuplicateByTitreAndCooperative($product->getTitre(), $coopId)) {
                    $errors[] = 'Un produit avec le même titre existe déjà dans cette coopérative.';
                }

                if ($artisanOwner && $productRepository->findDuplicateByTitreForArtisan($product->getTitre(), $artisanOwner)) {
                    $errors[] = 'Vous avez déjà un produit avec ce titre.';
                }

                if (!empty($errors)) {
                    foreach ($errors as $e) {
                        $this->addFlash('error', $e);
                    }
                    return $this->render('product/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                $em->persist($product);

            $photoFiles = $form->get('photos')->getData();
            if ($photoFiles) {
                foreach ($photoFiles as $photoFile) {
                    if ($photoFile instanceof UploadedFile && $photoFile->isValid()) {
                        $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                        $mimeType = $photoFile->getMimeType();

                        $photoFile->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads/products',
                            $newFilename
                        );

                        $media = new ProductMedia();
                        $media->setFilename('/uploads/products/' . $newFilename);
                        $media->setType($mimeType);
                        $media->setCaption($product->getTitre());
                        $media->setUpdatedAt(new \DateTime());
                        $media->setOrderIt(0);
                        $product->addMedium($media);
                    }
                }
            }

                $em->flush();

                $this->addFlash('success', 'Produit créé avec succès !');
                return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
            } catch (NotNullConstraintViolationException $e) {
                preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
                $fieldName = $matches[1] ?? 'un champ obligatoire';
                $this->addFlash('error', "Le champ '$fieldName' est obligatoire et ne peut pas être vide.");
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cette valeur existe déjà. Veuillez utiliser un titre ou slug unique.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données. Veuillez vérifier que tous les champs obligatoires sont correctement remplis.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création du produit : ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez les corriger.');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_product_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ProductRepository $productRepository): Response
    {
        $product = $this->productService->getById($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        /** @var User $user */
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isArtisan = $this->isGranted('ROLE_ARTISAN');

        if (!$isAdmin) {
            $artisan = ($user instanceof User) ? $user->getArtisan() : null;
            if (!$isArtisan || !$artisan || !$product->getArtisans()->contains($artisan)) {
                throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres produits.');
            }
        }

        $form = $this->createForm(ProductType::class, $product, [
            'is_admin' => $isAdmin,
            'is_artisan' => $isArtisan
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $product->setUpdatedAt(new \DateTime());

                $coopId = $product->getCooperative()?->getId();
                $duplicate = $productRepository->findDuplicateByTitreAndCooperative($product->getTitre(), $coopId, $product->getId());
                if ($duplicate) {
                    $this->addFlash('error', 'Un produit avec le même titre existe déjà dans cette coopérative.');
                    return $this->render('product/edit.html.twig', [
                        'form' => $form->createView(),
                        'product' => $product,
                    ]);
                }

                $photoFiles = $form->get('photos')->getData();
            if ($photoFiles) {
                foreach ($photoFiles as $photoFile) {
                    if ($photoFile instanceof UploadedFile && $photoFile->isValid()) {
                        $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                        $mimeType = $photoFile->getMimeType();

                        try {
                            $photoFile->move(
                                $this->getParameter('kernel.project_dir') . '/public/uploads/products',
                                $newFilename
                            );
                        } catch (FileException $fe) {
                            $this->addFlash('error', "Erreur lors du téléchargement de l'image: " . $fe->getMessage());
                            continue;
                        }

                        $media = new ProductMedia();
                        $media->setFilename('/uploads/products/' . $newFilename);
                        $media->setType($mimeType);
                        $media->setCaption($product->getTitre());
                        $media->setUpdatedAt(new \DateTime());
                        $media->setOrderIt(0);
                        $product->addMedium($media);
                    }
                }
            }
    $em->flush();

                $this->addFlash('success', 'Produit modifié avec succès !');
                return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
            } catch (NotNullConstraintViolationException $e) {
                preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
                $fieldName = $matches[1] ?? 'un champ obligatoire';
                $this->addFlash('error', "Le champ '$fieldName' est obligatoire et ne peut pas être vide.");
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cette valeur existe déjà. Veuillez utiliser un titre ou slug unique.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données. Veuillez vérifier que tous les champs obligatoires sont correctement remplis.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/product/{id}/delete', name: 'app_product_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $product = $this->productService->getById($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        /** @var User $user */
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isArtisan = $this->isGranted('ROLE_ARTISAN');

        if (!$isAdmin) {
            if (!$isArtisan || !$product->getArtisans()->contains($user->getArtisan())) {
                throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres produits.');
            }
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($product);
                $em->flush();
                $this->addFlash('success', 'Produit supprimé avec succès !');
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash('error', 'Impossible de supprimer ce produit car il est lié à des commandes ou autres éléments. Veuillez d\'abord supprimer ces éléments.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données lors de la suppression.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_product');
    }
}
