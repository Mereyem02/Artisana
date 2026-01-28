<?php

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use Doctrine\ORM\EntityManagerInterface;

class ArtisanService
{
    public function __construct(
        private ArtisanRepository $artisanRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getAll(): array
    {
        return $this->artisanRepository->findAll();
    }

    public function getFindAllQuery(): \Doctrine\ORM\Query
    {
        return $this->artisanRepository->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery();
    }

    public function getById(int $id): ?Artisan
    {
        return $this->artisanRepository->find($id);
    }

    public function create(array $data): Artisan
    {
        $now = new \DateTimeImmutable();

        $artisan = new Artisan();
        $artisan->setNom($data['nom'] ?? '');
        $artisan->setBio($data['bio'] ?? '');
        $artisan->setTelephone($data['telephone'] ?? '');
        $artisan->setEmail($data['email'] ?? '');
        $artisan->setPhoto((string) ($data['photo'] ?? ''));
        $artisan->setCompetences($data['competences'] ?? []);
        $artisan->setVerified(false);
        $artisan->setCreatedAt($now);
        $artisan->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($artisan);
        $this->entityManager->flush();

        return $artisan;
    }

    public function update(int $id, array $data): ?Artisan
    {
        $artisan = $this->artisanRepository->find($id);
        if (!$artisan) {
            return null;
        }

        if (isset($data['bio']))
            $artisan->setBio($data['bio']);
        if (isset($data['nom']))
            $artisan->setNom($data['nom']);
        if (isset($data['telephone']))
            $artisan->setTelephone($data['telephone']);
        if (isset($data['email']))
            $artisan->setEmail($data['email']);
        if (isset($data['photo']))
            $artisan->setPhoto((string) $data['photo']);
        if (isset($data['competences']))
            $artisan->setCompetences($data['competences']);

        $artisan->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $artisan;
    }

    public function delete(int $id): bool
    {
        $artisan = $this->artisanRepository->find($id);
        if (!$artisan) {
            return false;
        }

        $this->entityManager->remove($artisan);
        $this->entityManager->flush();

        return true;
    }
}
