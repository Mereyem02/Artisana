<?php

namespace App\Controller;

use App\Entity\Artisan;
use App\Service\ArtisanService;
use App\Repository\ArtisanRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use \Knp\Component\Pager\PaginatorInterface;
use App\Form\ArtisanType;
use Symfony\Component\String\Slugger\SluggerInterface;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \Doctrine\DBAL\Exception;
use \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
final class ArtisanController extends AbstractController
{
    public function __construct(
        private ArtisanService $artisanService
    ) {
    }

    #[Route('/artisan', name: 'app_artisan', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->artisanService->getFindAllQuery();

        $artisans = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('artisan/index.html.twig', [
            'artisans' => $artisans,
        ]);
    }

    #[Route('/artisan/{id}', name: 'app_artisan_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $artisan = $this->artisanService->getById($id);

        if (!$artisan) {
            throw $this->createNotFoundException('Artisan non trouvé');
        }

        $products = $productRepository->findByArtisan($artisan);
        return $this->render('artisan/show.html.twig', [
            'artisan' => $artisan,
            'products' => $products,
        ]);
    }

    #[Route('/artisan/new', name: 'app_artisan_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, UserRepository $userRepository, ArtisanRepository $artisanRepository): Response
    {
        $artisan = new Artisan();
        $form = $this->createForm(ArtisanType::class, $artisan, ['show_user_selection' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = [];

            if ($artisanRepository->findDuplicateByEmail($artisan->getEmail())) {
                $errors[] = 'Cet email est déjà utilisé par un autre artisan.';
            }

            if ($artisanRepository->findDuplicateByTelephone($artisan->getTelephone())) {
                $errors[] = 'Ce numéro de téléphone est déjà utilisé par un autre artisan.';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('artisan/new.html.twig', [
                    'artisan' => $artisan,
                    'form' => $form->createView(),
                ]);
            }

            try {
                $photoFile = $form->get('photo')->getData();
                if ($photoFile) {
                    $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                    $photoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/artisans',
                        $newFilename
                    );
                    $artisan->setPhoto('/uploads/artisans/' . $newFilename);
                }

                $competencesString = $form->get('competences')->getData();
                if ($competencesString) {
                    $competences = array_map('trim', explode(',', $competencesString));
                    $artisan->setCompetences($competences);
                }

                $artisan->setCreatedAt(new \DateTimeImmutable());
                $artisan->setUpdatedAt(new \DateTime());
                $artisan->setVerified($artisan->getApprovalStatus() === 'APPROVED');

                $email = $artisan->getEmail();
                $existingUser = $userRepository->findOneBy(['email' => $email]);

                if ($existingUser) {
                    $artisan->setUser($existingUser);
                } elseif ($artisan->getUser()) {

                    $artisan->getUser()->setEmail($email);
                }

                $em->persist($artisan);
                $em->flush();

                $this->addFlash('success', 'Artisan créé avec succès !');
                return $this->redirectToRoute('app_artisan');
            } catch (NotNullConstraintViolationException $e) {
                preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
                $fieldName = $matches[1] ?? 'un champ obligatoire';
                $this->addFlash('error', "Le champ '$fieldName' est obligatoire et ne peut pas être vide.");
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cette valeur existe déjà dans la base de données. Veuillez utiliser une valeur unique.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données. Veuillez vérifier que tous les champs obligatoires sont correctement remplis.');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création : ' . $e->getMessage());
            }
        }

        return $this->render('artisan/new.html.twig', [
            'artisan' => $artisan,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/artisan/{id}/edit', name: 'app_artisan_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Artisan $artisan, EntityManagerInterface $em, SluggerInterface $slugger, UserRepository $userRepository, ArtisanRepository $artisanRepository): Response
    {
        $form = $this->createForm(ArtisanType::class, $artisan, [
            'is_edit;' => true,
            'show_user_selection' => true
        ]);

        if (!$form->isSubmitted()) {
            $form->get('competences')->setData(implode(', ', $artisan->getCompetences()));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = [];

            if ($artisanRepository->findDuplicateByEmail($artisan->getEmail(), $artisan->getId())) {
                $errors[] = 'Cet email est déjà utilisé par un autre artisan.';
            }

            if ($artisanRepository->findDuplicateByTelephone($artisan->getTelephone(), $artisan->getId())) {
                $errors[] = 'Ce numéro de téléphone est déjà utilisé par un autre artisan.';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('artisan/edit.html.twig', [
                    'artisan' => $artisan,
                    'form' => $form->createView(),
                ]);
            }

            try {
                $photoFile = $form->get('photo')->getData();
                if ($photoFile) {
                    $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                    $photoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/artisans',
                        $newFilename
                    );
                    $artisan->setPhoto('/uploads/artisans/' . $newFilename);
                }

                $competencesString = $form->get('competences')->getData();
                if ($competencesString) {
                    $competences = array_map('trim', explode(',', $competencesString));
                    $artisan->setCompetences($competences);
                }

                $artisan->setUpdatedAt(new \DateTime());
                $artisan->setVerified($artisan->getApprovalStatus() === 'APPROVED');

                $email = $artisan->getEmail();
                $existingUser = $userRepository->findOneBy(['email' => $email]);

                if ($existingUser) {
                    $artisan->setUser($existingUser);
                } elseif ($artisan->getUser()) {
                    $artisan->getUser()->setEmail($email);
                }

                $em->flush();

                $this->addFlash('success', 'Artisan mis à jour avec succès !');
                return $this->redirectToRoute('app_artisan');
            } catch (NotNullConstraintViolationException $e) {
                preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
                $fieldName = $matches[1] ?? 'un champ obligatoire';
                $this->addFlash('error', "Le champ '$fieldName' est obligatoire et ne peut pas être vide.");
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cette valeur existe déjà dans la base de données. Veuillez utiliser une valeur unique.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données. Veuillez vérifier que tous les champs obligatoires sont correctement remplis.');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            }
        }

        return $this->render('artisan/edit.html.twig', [
            'artisan' => $artisan,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/artisan/{id}', name: 'app_artisan_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Artisan $artisan, EntityManagerInterface $em): Response
    {

        if ($this->isCsrfTokenValid('delete' . $artisan->getId(), $request->request->get('_token'))) {
            try {
                $user = $artisan->getUser();
                if ($user) {
                    $roles = $user->getRoles();
                    $key = array_search('ROLE_ARTISAN', $roles);
                    if ($key !== false) {
                        unset($roles[$key]);
                        $user->setRoles(array_values($roles));
                    }
                    $artisan->setUser(null);
                }

                $em->remove($artisan);
                $em->flush();
                $this->addFlash('success', 'Artisan supprimé avec succès.');
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash('error', 'Impossible de supprimer cet artisan car il est lié à d\'autres éléments (produits, commandes, etc.). Veuillez d\'abord supprimer ces éléments.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données lors de la suppression.');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_artisan');
    }

    #[Route('/admin/requests', name: 'admin_requests', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRequestList(ArtisanRepository $repository): Response
    {
        $artisans = $repository->findBy(['approvalStatus' => 'PENDING'], ['createdAt' => 'DESC']);

        return $this->render('admin/requests.html.twig', [
            'artisans' => $artisans,
        ]);
    }

    #[Route('/admin/request/{id}/approve', name: 'admin_request_approve', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function approveRequest(int $id, ArtisanRepository $repository, EntityManagerInterface $em): Response
    {
        $artisan = $repository->find($id);
        if (!$artisan) {
            throw $this->createNotFoundException('Artisan not found');
        }

        try {
            $artisan->setApprovalStatus('APPROVED');
            $artisan->setVerified(true);

            if ($artisan->getCooperative()) {
                $artisan->getCooperative()->setStatus('APPROVED');
            }

            $user = $artisan->getUser();
            if ($user) {
                $roles = $user->getRoles();
                if (!in_array('ROLE_ARTISAN', $roles)) {
                    $roles[] = 'ROLE_ARTISAN';
                    $user->setRoles($roles);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Artisan (et sa coopérative si applicable) approuvé avec succès.');
        } catch (NotNullConstraintViolationException $e) {
            $this->addFlash('error', 'Erreur : Certains champs obligatoires sont manquants. Veuillez vérifier que tous les champs requis sont remplis.');
        } catch (Exception $e) {
            $this->addFlash('error', 'Erreur de base de données : Impossible d\'approuver l\'artisan. Vérifiez les données saisies.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Une erreur inattendue s\'est produite : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_requests');
    }

    #[Route('/admin/request/{id}/reject', name: 'admin_request_reject', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function rejectRequest(int $id, ArtisanRepository $repository, EntityManagerInterface $em): Response
    {
        $artisan = $repository->find($id);
        if (!$artisan) {
            throw $this->createNotFoundException('Artisan not found');
        }

        try {
            $artisan->setApprovalStatus('REJECTED');

            if ($artisan->getCooperative()) {
                $artisan->getCooperative()->setStatus('REJECTED');
            }

            $em->flush();

            $this->addFlash('success', 'Demande refusée.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erreur lors du rejet de la demande : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_requests');
    }

}