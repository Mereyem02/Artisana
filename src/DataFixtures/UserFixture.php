<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    private array $usersData = [
        [
            'email' => 'admin@artisana.ma',
            'firstName' => 'Mohammed',
            'lastName' => 'Admin',
            'phone' => '+212 6 00 00 00 00',
            'password' => 'Admin123!',
            'roles' => ['ROLE_ADMIN']
        ],
        [
            'email' => 'user@artisana.ma',
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'phone' => '+212 6 11 11 11 11',
            'password' => 'User123!',
            'roles' => ['ROLE_USER']
        ],
        [
            'email' => 'client@artisana.ma',
            'firstName' => 'Marie',
            'lastName' => 'Martin',
            'phone' => '+212 6 22 22 22 22',
            'password' => 'Client123!',
            'roles' => ['ROLE_USER']
        ],
        [
            'email' => 'cooperative.manager@artisana.ma',
            'firstName' => 'Karim',
            'lastName' => 'Cooperative',
            'phone' => '+212 6 33 33 33 33',
            'password' => 'Coop123!',
            'roles' => ['ROLE_USER']
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->usersData as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setPhone($userData['phone']);
            $user->setRoles($userData['roles']);

            // Hash le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);

            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTime());

            $manager->persist($user);
        }

        $manager->flush();
    }
}
