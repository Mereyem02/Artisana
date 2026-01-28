<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un compte administrateur'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existingAdmin = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();

        if (!empty($existingAdmin)) {
            $io->warning('Un compte admin existe déjà :');
            foreach ($existingAdmin as $admin) {
                $io->text('- ' . $admin->getEmail());
            }

            if (!$io->confirm('Voulez-vous créer un autre compte admin ?', false)) {
                return Command::SUCCESS;
            }
        }

        $email = $io->ask('Email de l\'administrateur', 'admin@artisana.ma', function ($value) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Email invalide');
            }
            return $value;
        });

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existingUser) {
            if ($io->confirm('Cet email existe déjà. Voulez-vous le promouvoir en admin ?', true)) {
                $roles = $existingUser->getRoles();
                if (!in_array('ROLE_ADMIN', $roles)) {
                    $roles[] = 'ROLE_ADMIN';
                    $existingUser->setRoles($roles);
                    $this->entityManager->flush();

                    $io->success('L\'utilisateur ' . $email . ' a été promu administrateur !');
                    return Command::SUCCESS;
                } else {
                    $io->info('Cet utilisateur est déjà administrateur');
                    return Command::SUCCESS;
                }
            } else {
                return Command::FAILURE;
            }
        }

        $firstName = $io->ask('Prénom', 'Admin');
        $lastName = $io->ask('Nom', 'Artisana');
        $password = $io->askHidden('Mot de passe', function ($value) {
            if (strlen($value) < 6) {
                throw new \RuntimeException('Le mot de passe doit contenir au moins 6 caractères');
            }
            return $value;
        });

        $admin = new User();
        $admin->setEmail($email);
        $admin->setFirstName($firstName);
        $admin->setLastName($lastName);
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setUpdatedAt(new \DateTime());

        $hashedPassword = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setPassword($hashedPassword);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success('Compte administrateur créé avec succès !');
        $io->table(
            ['Email', 'Nom', 'Rôles'],
            [[$admin->getEmail(), $admin->getFirstName() . ' ' . $admin->getLastName(), implode(', ', $admin->getRoles())]]
        );

        $io->note('Vous pouvez maintenant vous connecter avec : ' . $email);

        return Command::SUCCESS;
    }
}
