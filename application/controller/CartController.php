<?php

use JetBrains\PhpStorm\NoReturn;

class CartController {
    public function index(): void
    {
        session_start();
        $cart = $_SESSION['cart'] ?? [];
        require APP . 'view/cart/index.php';
    }

    #[NoReturn] public function addToCart(int $productId): void
    {
        session_start();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 1;
        } else {
            $_SESSION['cart'][$productId]++;
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    #[NoReturn] public function removeFromCart($productId): void
    {
        session_start();
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    #[NoReturn] public function clearCart(): void
    {
        session_start();
        unset($_SESSION['cart']);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

