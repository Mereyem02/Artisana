<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function getAll(): array
    {
        return $this->userRepository->findAll();
    }

    public function getById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function getByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    public function create(array $data): User
    {
        $now = new \DateTimeImmutable();

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setFirstName($data['firstName'] ?? '');
        $user->setLastName($data['lastName'] ?? '');
        $user->setPhone($data['phone'] ?? '');
        $user->setRoles($data['roles'] ?? []);

        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $data['password']
            );
            $user->setPassword($hashedPassword);
        }

        $user->setCreatedAt($now);
        $user->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function update(int $id, array $data): ?User
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return null;
        }

        if (isset($data['email']))
            $user->setEmail($data['email']);
        if (isset($data['firstName']))
            $user->setFirstName($data['firstName']);
        if (isset($data['lastName']))
            $user->setLastName($data['lastName']);
        if (isset($data['phone']))
            $user->setPhone($data['phone']);
        if (isset($data['roles']))
            $user->setRoles($data['roles']);

        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $data['password']
            );
            $user->setPassword($hashedPassword);
        }

        $user->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $user;
    }

    public function delete(int $id): bool
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return false;
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return true;
    }
}
