<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class CartModel {
    public static function getCartItems() {
        return $_SESSION['cart'] ?? [];
    }

    public static function getTotalItems(): int {
        return isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
    }
}
