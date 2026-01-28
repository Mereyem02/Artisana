<?php

namespace App\Controller;

use App\Entity\Artisan;
use App\Form\BecomeArtisanType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException ;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException ;
use \Doctrine\DBAL\Exception;

class AccountController extends AbstractController
{
    #[Route('/compte/devenir-artisan', name: 'app_become_artisan')]
    #[IsGranted('ROLE_USER')]
    public function becomeArtisan(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user->getArtisan()) {
            $this->addFlash('info', 'Vous avez déjà un profil artisan.');
            return $this->redirectToRoute('app_artisan_show', ['id' => $user->getArtisan()->getId()]);
        }

        $artisan = new Artisan();
        $artisan->setUser($user);
        $artisan->setEmail($user->getEmail());
        $artisan->setTelephone($user->getPhone() ?? '');
        $artisan->setNom($user->getLastName() . ' ' . $user->getFirstName());
        $artisan->setApprovalStatus('PENDING');

        $form = $this->createForm(BecomeArtisanType::class, $artisan);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        //Le sauvegarde de fichier est dans le serveur 
                        $this->getParameter('kernel.project_dir') . '/public/uploads/artisans',
                        $newFilename
                    );
                    $artisan->setPhoto('/uploads/artisans/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo');
                }
            }

            $competencesString = $form->get('competences')->getData();
            if ($competencesString) {
                $competences = array_map('trim', explode(',', $competencesString));
                $artisan->setCompetences($competences);
            }

            $artisan->setCreatedAt(new \DateTimeImmutable());
            $artisan->setUpdatedAt(new \DateTime());
            $artisan->setVerified(false);

            try {
                $em->persist($artisan);
                $em->flush();

                $this->addFlash('success', 'Votre demande pour devenir artisan a été soumise avec succès. Un administrateur l\'examinera bientôt.');

                return $this->redirectToRoute('app_artisan_status');
            } catch (NotNullConstraintViolationException $e) {
                preg_match("/Column '(\w+)'/", $e->getMessage(), $matches);
                $fieldName = $matches[1] ?? 'un champ obligatoire';
                $this->addFlash('error', "Le champ '$fieldName' est obligatoire et ne peut pas être vide.");
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cette valeur existe déjà dans la base de données.');
            }catch (Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('account/become_artisan.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/compte/statut-artisan', name: 'app_artisan_status')]
    #[IsGranted('ROLE_USER')]
    public function artisanStatus(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user->getArtisan()) {
            $this->addFlash('info', 'Vous n\'avez pas encore soumis de demande artisan.');
            return $this->redirectToRoute('app_become_artisan');
        }

        $artisan = $user->getArtisan();

        return $this->render('account/artisan_status.html.twig', [
            'artisan' => $artisan,
        ]);
    }
}
