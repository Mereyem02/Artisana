<?php

namespace App\Controller;

use App\Service\CartService;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private ProductService $productService
    ) {
    }

    #[Route('', name: 'app_cart_index', methods: ['GET'])]
    public function index(): Response
    {
        $items = $this->cartService->getCartItems();
        $total = $this->cartService->getTotal();
        $errors = $this->cartService->validateCart();

        return $this->render('cart/index.html.twig', [
            'items' => $items,
            'total' => $total,
            'errors' => $errors,
            'itemCount' => $this->cartService->getItemCount()
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request): Response
    {
        $product = $this->productService->getById($id);
        
        if (!$product) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Produit introuvable'], 404);
            }
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('app_product');
        }

        if (!$product->isActive()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Ce produit n\'est plus disponible'], 400);
            }
            $this->addFlash('error', 'Ce produit n\'est plus disponible');
            return $this->redirectToRoute('app_product');
        }

        $quantity = max(1, (int) $request->request->get('quantity', 1));

        if ($product->getStock() < $quantity) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Stock insuffisant'], 400);
            }
            $this->addFlash('error', 'Stock insuffisant');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        $this->cartService->addItem($id, $quantity);
        
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'itemCount' => $this->cartService->getItemCount(),
                'total' => $this->cartService->getTotal()
            ]);
        }
        
        $this->addFlash('success', 'Produit ajouté au panier');

        $referer = $request->headers->get('referer');
        if ($referer && !str_contains($referer, '/cart')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $quantity = (int) $request->request->get('quantity', 0);
        $this->cartService->updateQuantity($id, $quantity);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'itemCount' => $this->cartService->getItemCount(),
                'total' => $this->cartService->getTotal()
            ]);
        }

        $this->addFlash('success', 'Panier mis à jour');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST', 'DELETE'])]
    public function remove(int $id, Request $request): Response
    {
        if (!$request->isXmlHttpRequest() && !$this->isCsrfTokenValid('remove' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $this->cartService->removeItem($id);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'itemCount' => $this->cartService->getItemCount(),
                'total' => $this->cartService->getTotal()
            ]);
        }

        $this->addFlash('success', 'Produit retiré du panier');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('clear_cart', $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $this->cartService->clear();
        $this->addFlash('success', 'Panier vidé');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/count', name: 'app_cart_count', methods: ['GET'])]
    public function count(): Response
    {
        return $this->json([
            'count' => $this->cartService->getItemCount()
        ]);
    }
}
