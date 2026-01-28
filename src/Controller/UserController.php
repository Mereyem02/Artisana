<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception;


final class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    #[Route('/user', name: 'app_user', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $users = $this->userService->getAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(int $id, Request $request): Response
    {
        $user = $this->userService->getById($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN') && $currentUser->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que votre propre profil.');
        }

        $form = $this->createFormBuilder($user)
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = [
                'firstName' => $form->get('firstName')->getData(),
                'lastName' => $form->get('lastName')->getData(),
                'email' => $form->get('email')->getData(),
                'phone' => $form->get('phone')->getData(),
            ];

            $this->userService->update($id, $data);
            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_user_show', ['id' => $id]);
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}', name: 'app_user_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(int $id): Response
    {
        $user = $this->userService->getById($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
                /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN') && $currentUser->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez voir que votre propre profil.');
        }
        
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user', name: 'app_user_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json(['message' => 'Données invalides'], 400);
            }
            
            $user = $this->userService->create($data);

            return $this->json([
                'status' => 201,
                'message' => 'Utilisateur créé',
                'id' => $user->getId()
            ], 201);
        } catch (NotNullConstraintViolationException $e) {
            preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
            $fieldName = $matches[1] ?? 'un champ obligatoire';
            return $this->json(['message' => "Le champ '$fieldName' est obligatoire et ne peut pas être vide."], 400);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['message' => 'Cette valeur (email ou nom d\'utilisateur) existe déjà.'], 400);
        } catch (Exception $e) {
            return $this->json(['message' => 'Erreur de base de données. Vérifiez que tous les champs obligatoires sont remplis.'], 400);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 400,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], 400);
        }
    }

    #[Route('/user/{id}', name: 'app_user_update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(int $id, Request $request): Response
    {
        try {
            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();
            if (!$this->isGranted('ROLE_ADMIN') && $currentUser->getId() !== $id) {
                return $this->json(['message' => 'Accès refusé'], 403);
            }
            
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json(['message' => 'Données invalides'], 400);
            }
            
            $user = $this->userService->update($id, $data);

            if (!$user) {
                return $this->json(['message' => 'Utilisateur non trouvé'], 404);
            }

            return $this->json(['message' => 'Utilisateur modifié avec succès']);
        } catch (NotNullConstraintViolationException $e) {
            preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
            $fieldName = $matches[1] ?? 'un champ obligatoire';
            return $this->json(['message' => "Le champ '$fieldName' est obligatoire et ne peut pas être vide."], 400);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['message' => 'Cette valeur (email ou nom d\'utilisateur) existe déjà.'], 400);
        } catch (Exception $e) {
            return $this->json(['message' => 'Erreur de base de données. Vérifiez que tous les champs obligatoires sont remplis.'], 400);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la modification : ' . $e->getMessage()
            ], 400);
        }
    }

    #[Route('/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): Response
    {
        try {
            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();
            if ($currentUser->getId() === $id) {
                return $this->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte'], 403);
            }
            
            $deleted = $this->userService->delete($id);
            
            if (!$deleted) {
                return $this->json(['message' => 'Utilisateur non trouvé'], 404);
            }

            return $this->json(['message' => 'Utilisateur supprimé avec succès']);
        } catch (ForeignKeyConstraintViolationException $e) {
            return $this->json(['message' => 'Impossible de supprimer cet utilisateur car il est lié à d\'autres éléments (artisan, commandes, etc.).'], 400);
        } catch (Exception $e) {
            return $this->json(['message' => 'Erreur de base de données lors de la suppression.'], 400);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 400);
        }
    }
}
