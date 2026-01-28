<?php

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_SESSION_KEY = 'cart';

    public function __construct(
        private RequestStack $requestStack,
        private ProductService $productService
    ) {
    }

    /**
     * Get the current cart from session
     */
    private function getCart(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get(self::CART_SESSION_KEY, []);
    }

   
    private function saveCart(array $cart): void
    {
        $session = $this->requestStack->getSession();
        $session->set(self::CART_SESSION_KEY, $cart);
    }

    public function addItem(int $productId, int $quantity = 1): void
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity
            ];
        }

        $this->saveCart($cart);
    }


    public function removeItem(int $productId): void
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->saveCart($cart);
        }
    }

    
    public function updateQuantity(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($productId);
            return;
        }

        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            $this->saveCart($cart);
        }
    }

    public function clear(): void
    {
        $this->saveCart([]);
    }

    /**
     * Get cart items with full product details
     */
    public function getCartItems(): array
    {
        $cart = $this->getCart();
        $items = [];

        foreach ($cart as $productId => $cartItem) {
            $product = $this->productService->getById($productId);

            if ($product) {
                $items[] = [
                    'product' => $product,
                    'quantity' => $cartItem['quantity'],
                    'subtotal' => (float) $product->getPrix() * $cartItem['quantity']
                ];
            }
        }

        return $items;
    }

  
    public function getItemCount(): int
    {
        $cart = $this->getCart();
        $count = 0;

        foreach ($cart as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

  
    public function getTotal(): float
    {
        $items = $this->getCartItems();
        $total = 0;

        foreach ($items as $item) {
            $total += $item['subtotal'];
        }

        return $total;
    }

  
    public function isEmpty(): bool
    {
        return empty($this->getCart());
    }


    public function validateCart(): array
    {
        $items = $this->getCartItems();
        $errors = [];

        foreach ($items as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];

            if ($product->getStock() < $quantity) {
                $errors[] = "Stock insuffisant pour {$product->getTitre()} (disponible: {$product->getStock()})";
            }

            if (!$product->isActive()) {
                $errors[] = "Le produit {$product->getTitre()} n'est plus disponible";
            }
        }

        return $errors;
    }
}
