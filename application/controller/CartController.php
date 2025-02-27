<?php

class CartController
{
    public function index(): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }
        $cartItems = CartModel::getCartItemsWithDetails($_SESSION['user_id']);
        require APP . 'view/cart/index.php';
    }

    public function addToCart(int $game_id): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        if (!GameModel::getGameById($game_id)) {
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=game_not_found');
            exit();
        }

        CartModel::addToCart($_SESSION['user_id'], $game_id);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    public function removeFromCart(int $game_id): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        if (!CartModel::isGameInCart($_SESSION['user_id'], $game_id)) {
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=not_in_cart');
            exit();
        }

        CartModel::removeFromCart($_SESSION['user_id'], $game_id);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    public function checkout(): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        if (!CartModel::hasItems($_SESSION['user_id'])) {
            header('Location: /cart?error=empty_cart');
            exit();
        }

        $orderId = CartModel::checkout($_SESSION['user_id']);
        if ($orderId) {
            $_SESSION['cart'] = []; // Warenkorb leeren
            header('Location: /order/confirmation?orderId=' . $orderId);
        } else {
            header('Location: /cart?error=checkout_failed');
        }
        exit();
    }

    public function clearOldCarts(): void
    {
        CartModel::clearOldCarts();
    }
}
