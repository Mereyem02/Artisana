<?php

namespace App\Service;

use App\Entity\ProductMedia;
use App\Repository\ProductMediaRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductMediaService
{
    public function __construct(
        private ProductMediaRepository $productMediaRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getAll(): array
    {
        return $this->productMediaRepository->findAll();
    }

    public function getById(int $id): ?ProductMedia
    {
        return $this->productMediaRepository->find($id);
    }

    public function create(array $data): ProductMedia
    {
        $media = new ProductMedia();
        $media->setFilename($data['filename'] ?? '');
        $media->setType($data['type'] ?? '');
        $media->setCaption($data['caption'] ?? '');
        $media->setOrderIt($data['order_it'] ?? 0);

        $media->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return $media;
    }

    public function update(int $id, array $data): ?ProductMedia
    {
        $media = $this->productMediaRepository->find($id);
        if (!$media) {
            return null;
        }

        if (isset($data['filename']))
            $media->setFilename($data['filename']);
        if (isset($data['type']))
            $media->setType($data['type']);
        if (isset($data['caption']))
            $media->setCaption($data['caption']);
        if (isset($data['order_it']))
            $media->setOrderIt($data['order_it']);

        $media->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $media;
    }

    public function delete(int $id): bool
    {
        $media = $this->productMediaRepository->find($id);
        if (!$media) {
            return false;
        }

        $this->entityManager->remove($media);
        $this->entityManager->flush();

        return true;
    }
}
