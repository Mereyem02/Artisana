<?php

namespace App\Controller;

use App\Entity\Cooperative;
use App\Form\CooperativeType;
use App\Service\CooperativeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use \Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\UserRepository;
use App\Repository\CooperativeRepository;
use \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \Doctrine\DBAL\Exception;
use App\Entity\Artisan;
use Symfony\Component\HttpFoundation\File\UploadedFile;


final class CooperativeController extends AbstractController
{
    public function __construct(
        private CooperativeService $cooperativeService
    ) {
    }

    #[Route('/cooperative', name: 'app_cooperative', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $cooperatives = $paginator->paginate(
            $this->cooperativeService->getAllQueryBuilder(),
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('cooperative/index.html.twig', [
            'cooperatives' => $cooperatives,
        ]);
    }

    #[Route('/cooperative/{id}', name: 'app_cooperative_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $cooperative = $this->cooperativeService->getById($id);
        if (!$cooperative) {
            throw $this->createNotFoundException('Cooperative not found');
        }

        return $this->render('cooperative/show.html.twig', [
            'cooperative' => $cooperative,
        ]);
    }

    #[Route('/cooperative/new', name: 'app_cooperative_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, CooperativeRepository $cooperativeRepository): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cooperative = new Cooperative();
        $cooperative->setCreatedAt(new \DateTimeImmutable());
        $cooperative->setUpdatedAt(new \DateTime());

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $form = $this->createForm(CooperativeType::class, $cooperative);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = [];

            if ($cooperativeRepository->findDuplicateByEmail($cooperative->getEmail())) {
                $errors[] = 'Cet email est déjà utilisé par une autre coopérative.';
            }

            if ($cooperativeRepository->findDuplicateByTelephone($cooperative->getTelephone())) {
                $errors[] = 'Ce numéro de téléphone est déjà utilisé par une autre coopérative.';
            }

            if ($cooperative->getSiteWeb() && $cooperativeRepository->findDuplicateBySiteWeb($cooperative->getSiteWeb())) {
                $errors[] = 'Ce site web est déjà utilisé par une autre coopérative.';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('cooperative/new.html.twig', [
                    'form' => $form,
                ]);
            }
            try {
                $logoFile = $form->get('logo')->getData();
                if ($logoFile instanceof UploadedFile && $logoFile->isValid()) {
                    $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                    $logoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/cooperatives',
                        $newFilename
                    );
                    $cooperative->setLogo('/uploads/cooperatives/' . $newFilename);
                }

                if ($isAdmin) {
                    $cooperative->setStatus('APPROVED');
                } else {
                    if ($user->getArtisan()) {
                        $this->addFlash('warning', 'Vous avez déjà un profil artisan (ou une demande en cours).');
                        return $this->redirectToRoute('app_home');
                    }

                    $cooperative->setStatus('PENDING');

                    $artisan = new Artisan();
                    $artisan->setNom($user->getFirstName() . ' ' . $user->getLastName());
                    $artisan->setEmail($user->getEmail());
                    $artisan->setTelephone($cooperative->getTelephone());
                    $artisan->setBio("Responsable de la coopérative: " . $cooperative->getNom() . "\n" . $cooperative->getDescription());
                    $artisan->setCreatedAt(new \DateTimeImmutable());
                    $artisan->setUpdatedAt(new \DateTime());
                    $artisan->setVerified(false);
                    $artisan->setApprovalStatus('PENDING');

                    $artisan->setUser($user);
                    $artisan->setCooperative($cooperative);

                    $em->persist($artisan);
                }

                $em->persist($cooperative);
                $em->flush();

                if ($isAdmin) {
                    $this->addFlash('success', 'Coopérative créée avec succès !');
                    return $this->redirectToRoute('app_cooperative_show', ['id' => $cooperative->getId()]);
                } else {
                    $this->addFlash('success', 'Votre demande de création de coopérative a été envoyée avec succès et est en attente de validation.');
                    return $this->redirectToRoute('app_home');
                }
            } catch (NotNullConstraintViolationException $e) {
                preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
                $fieldName = $matches[1] ?? 'un champ obligatoire';
                $this->addFlash('error', "Le champ '$fieldName' est obligatoire et ne peut pas être vide.");
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cette valeur existe déjà. Veuillez utiliser un nom unique.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données. Veuillez vérifier que tous les champs obligatoires sont correctement remplis.');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez les corriger.');
        }

        return $this->render('cooperative/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/cooperative/{id}/edit', name: 'app_cooperative_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(int $id, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, UserRepository $userRepository, CooperativeRepository $cooperativeRepository): Response
    {
        $cooperative = $this->cooperativeService->getById($id);
        if (!$cooperative) {
            throw $this->createNotFoundException('Coopérative non trouvée');
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $form = $this->createForm(CooperativeType::class, $cooperative);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = [];

            if ($cooperativeRepository->findDuplicateByEmail($cooperative->getEmail(), $cooperative->getId())) {
                $errors[] = 'Cet email est déjà utilisé par une autre coopérative.';
            }

            if ($cooperativeRepository->findDuplicateByTelephone($cooperative->getTelephone(), $cooperative->getId())) {
                $errors[] = 'Ce numéro de téléphone est déjà utilisé par une autre coopérative.';
            }

            if ($cooperative->getSiteWeb() && $cooperativeRepository->findDuplicateBySiteWeb($cooperative->getSiteWeb(), $cooperative->getId())) {
                $errors[] = 'Ce site web est déjà utilisé par une autre coopérative.';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('cooperative/edit.html.twig', [
                    'form' => $form,
                    'cooperative' => $cooperative,
                ]);
            }
            try {
                $logoFile = $form->get('logo')->getData();
                if ($logoFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && $logoFile->isValid()) {
                    $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                    $logoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/cooperatives',
                        $newFilename
                    );
                    $cooperative->setLogo('/uploads/cooperatives/' . $newFilename);
                }

                $cooperative->setUpdatedAt(new \DateTime());
                $em->flush();

                $this->addFlash('success', 'Coopérative modifiée avec succès !');
                return $this->redirectToRoute('app_cooperative_show', ['id' => $cooperative->getId()]);
            } catch (NotNullConstraintViolationException $e) {
                preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
                $fieldName = $matches[1] ?? 'un champ obligatoire';
                $this->addFlash('error', "Le champ '$fieldName' est obligatoire et ne peut pas être vide.");
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cette valeur existe déjà. Veuillez utiliser un nom unique.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données. Veuillez vérifier que tous les champs obligatoires sont correctement remplis.');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez les corriger.');
        }

        return $this->render('cooperative/edit.html.twig', [
            'form' => $form,
            'cooperative' => $cooperative,
        ]);
    }

    #[Route('/cooperative/{id}/delete', name: 'app_cooperative_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $cooperative = $this->cooperativeService->getById($id);
        if (!$cooperative) {
            throw $this->createNotFoundException('Coopérative non trouvée');
        }

        if ($this->isCsrfTokenValid('delete' . $cooperative->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($cooperative);
                $em->flush();
                $this->addFlash('success', 'Coopérative supprimée avec succès !');
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash('error', 'Impossible de supprimer cette coopérative car elle est liée à des artisans ou des produits. Veuillez d\'abord supprimer ces éléments.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données lors de la suppression.');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_cooperative');
    }
}
