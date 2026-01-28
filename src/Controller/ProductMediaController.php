<?php

namespace App\Controller;

use App\Service\ProductMediaService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use  \Knp\Component\Pager\PaginatorInterface;

final class ProductMediaController extends AbstractController
{
    public function __construct(
        private ProductMediaService $productMediaService
    ) {
    }

    #[Route('/product/media', name: 'app_product_media', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginatorInterface): Response
    {
        $media = $this->productMediaService->getAll();
        $media = $paginatorInterface->paginate(
            $media,
            $request->query->getInt('page', 1),
            8
        );
        return $this->render('product_media/index.html.twig', [
            'media' => $media,
        ]);
    }

    #[Route('/product/media/{id}', name: 'app_product_media_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $media = $this->productMediaService->getById($id);
        if (!$media) {
            throw $this->createNotFoundException('ProductMedia not found');
        }
        return $this->render('product_media/show.html.twig', [
            'media' => $media,
        ]);
    }

    #[Route('/product/media', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $media = $this->productMediaService->create($data);

        return $this->json([
            'status' => 201,
            'message' => 'ProductMedia created',
            'id' => $media->getId()
        ], 201);
    }

    #[Route('/product/media/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $media = $this->productMediaService->update($id, $data);

        if (!$media) {
            return $this->json(['message' => 'ProductMedia not found'], 404);
        }

        return $this->json(['message' => 'ProductMedia updated']);
    }

    #[Route('/product/media/{id}', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $deleted = $this->productMediaService->delete($id);
        if (!$deleted) {
            return $this->json(['message' => 'ProductMedia not found'], 404);
        }

        return $this->json(['message' => 'ProductMedia deleted']);
    }

    #[Route('/product/media/{id}/delete', name: 'app_product_media_delete', methods: ['POST'])]
    public function deleteFromUi(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $media = $this->productMediaService->getById($id);
        if (!$media) {
            $this->addFlash('error', 'Média introuvable.');
            return $this->redirectToRoute('app_product');
        }

        $product = $media->getProduct();
        if (!$product) {
            $this->addFlash('error', 'Produit associé introuvable.');
            return $this->redirectToRoute('app_product');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            /** @var User|null $user */
            $user = $this->getUser();
            $artisan = ($user instanceof User) ? $user->getArtisan() : null;
            if (!$this->isGranted('ROLE_ARTISAN') || !$artisan || !$product->getArtisans()->contains($artisan)) {
                $this->addFlash('error', "Vous n'avez pas l'autorisation de supprimer cette image.");
                return $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);
            }
        }

        if (!$this->isCsrfTokenValid('delete_media' . $media->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);
        }

        $filename = $media->getFilename();
        if ($filename && str_starts_with($filename, '/uploads')) {
            $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $filename;
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        $em->remove($media);
        $em->flush();

        $this->addFlash('success', 'Image supprimée avec succès.');
        return $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);
    }
}
