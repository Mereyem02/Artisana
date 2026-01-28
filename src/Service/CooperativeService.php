<?php

namespace App\Service;

use App\Entity\Cooperative;
use App\Repository\CooperativeRepository;
use Doctrine\ORM\EntityManagerInterface;

class CooperativeService
{
    public function __construct(
        private CooperativeRepository $cooperativeRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getAllQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->cooperativeRepository
            ->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC');
    }

    public function getById(int $id): ?Cooperative
    {
        return $this->cooperativeRepository->find($id);
    }

    public function create(array $data): Cooperative
    {
        $now = new \DateTimeImmutable();

        $cooperative = new Cooperative();
        $cooperative->setNom($data['nom'] ?? '');
        $cooperative->setAdresse($data['adresse'] ?? '');
        $cooperative->setDescription($data['description'] ?? '');
        $cooperative->setLogo($data['logo'] ?? '');
        $cooperative->setContact($data['contact'] ?? '');
        $cooperative->setEmail($data['email'] ?? '');
        $cooperative->setTelephone($data['telephone'] ?? '');
        $cooperative->setStatus($data['status'] ?? 'active');
        $cooperative->setCreatedAt($now);
        $cooperative->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($cooperative);
        $this->entityManager->flush();

        return $cooperative;
    }

    public function update(int $id, array $data): ?Cooperative
    {
        $cooperative = $this->cooperativeRepository->find($id);
        if (!$cooperative) {
            return null;
        }

        if (isset($data['nom']))
            $cooperative->setNom($data['nom']);
        if (isset($data['adresse']))
            $cooperative->setAdresse($data['adresse']);
        if (isset($data['description']))
            $cooperative->setDescription($data['description']);
        if (isset($data['logo']))
            $cooperative->setLogo($data['logo']);
        if (isset($data['contact']))
            $cooperative->setContact($data['contact']);
        if (isset($data['email']))
            $cooperative->setEmail($data['email']);
        if (isset($data['telephone']))
            $cooperative->setTelephone($data['telephone']);
        if (isset($data['status']))
            $cooperative->setStatus($data['status']);

        $cooperative->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $cooperative;
    }

    public function delete(int $id): bool
    {
        $cooperative = $this->cooperativeRepository->find($id);
        if (!$cooperative) {
            return false;
        }

        $this->entityManager->remove($cooperative);
        $this->entityManager->flush();

        return true;
    }
}
