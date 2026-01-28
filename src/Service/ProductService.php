<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CooperativeRepository;
use App\Repository\ProductMediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private CooperativeRepository $cooperativeRepository,
        private ProductMediaRepository $productMediaRepository,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
    }

    public function getAll(): array
    {
        return $this->productRepository->findAll();
    }

    public function getById(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    public function create(array $data): Product
    {
        // Validation des données
        if (empty($data['titre'])) {
            throw new \InvalidArgumentException('Le titre du produit est obligatoire');
        }
        
        if (!isset($data['prix']) || !is_numeric($data['prix']) || $data['prix'] < 0) {
            throw new \InvalidArgumentException('Le prix du produit doit être un nombre positif');
        }
        
        if (!isset($data['stock']) || !is_numeric($data['stock']) || $data['stock'] < 0) {
            throw new \InvalidArgumentException('Le stock doit être un nombre positif ou zéro');
        }

        $now = new \DateTimeImmutable();

        $product = new Product();
        $product->setTitre($data['titre']);
        $product->setDescription($data['description'] ?? '');
        $product->setPrix((string) $data['prix']);
        $product->setStock((int) $data['stock']);
        $product->setDimensions($data['dimensions'] ?? '');
        $product->setMateriaux($data['materiaux'] ?? []);
        $product->setIsActive($data['is_active'] ?? true);

        $slug = $this->slugger->slug($product->getTitre())->lower();
        $product->setSlug((string) $slug);

        $product->setCreatedAt($now);
        $product->setUpdatedAt(new \DateTime());

        if (isset($data['cooperative_id'])) {
            $cooperative = $this->cooperativeRepository->find($data['cooperative_id']);
            if ($cooperative) {
                $product->setCooperative($cooperative);
            }
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    public function update(int $id, array $data): ?Product
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return null;
        }

        // Validation des données si présentes
        if (isset($data['prix']) && (!is_numeric($data['prix']) || $data['prix'] < 0)) {
            throw new \InvalidArgumentException('Le prix doit être un nombre positif');
        }
        
        if (isset($data['stock']) && (!is_numeric($data['stock']) || $data['stock'] < 0)) {
            throw new \InvalidArgumentException('Le stock doit être un nombre positif ou zéro');
        }

        if (isset($data['titre']) && !empty($data['titre'])) {
            $product->setTitre($data['titre']);
            $slug = $this->slugger->slug($data['titre'])->lower();
            $product->setSlug((string) $slug);
        }
        
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        
        if (isset($data['prix'])) {
            $product->setPrix((string) $data['prix']);
        }
        
        if (isset($data['stock'])) {
            $product->setStock((int) $data['stock']);
        }
        
        if (isset($data['sku'])) {
            $product->setSku($data['sku']);
        }
        
        if (isset($data['dimensions'])) {
            $product->setDimensions($data['dimensions']);
        }
        
        if (isset($data['materiaux'])) {
            $product->setMateriaux($data['materiaux']);
        }
        
        if (isset($data['is_active'])) {
            $product->setIsActive((bool) $data['is_active']);
        }

        if (isset($data['cooperative_id'])) {
            $cooperative = $this->cooperativeRepository->find($data['cooperative_id']);
            if ($cooperative) {
                $product->setCooperative($cooperative);
            }
        }

        if (isset($data['product_media_id'])) {
            $media = $this->productMediaRepository->find($data['product_media_id']);
            if ($media) {
                $product->addMedium($media);
            }
        }

        $product->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $product;
    }

    public function delete(int $id): bool
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return false;
        }

        try {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            // Log l'erreur si nécessaire
            throw new \RuntimeException('Erreur lors de la suppression du produit : ' . $e->getMessage());
        }
    }
}
