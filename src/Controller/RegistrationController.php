<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = [];
            
            if ($userRepository->findDuplicateByEmail($user->getEmail())) {
                $errors[] = 'Cet email est déjà utilisé.';
            }
            
            if ($userRepository->findDuplicateByPhone($user->getPhone())) {
                $errors[] = 'Ce numéro de téléphone est déjà utilisé.';
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $user->setRoles(['ROLE_CLIENT']);

            $now = new \DateTimeImmutable();
            $user->setCreatedAt($now);
            $user->setUpdatedAt(new \DateTime());

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte a été créé avec succès ! Connectez-vous maintenant.');

                return $this->redirectToRoute('app_login');
            } catch (NotNullConstraintViolationException $e) {
                preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
                $fieldName = $matches[1] ?? 'un champ obligatoire';
                $this->addFlash('error', "Le champ '$fieldName' est obligatoire et ne peut pas être vide.");
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cet email est déjà utilisé. Veuillez en choisir un autre.');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erreur de base de données. Veuillez vérifier que tous les champs obligatoires sont correctement remplis.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du compte : ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
