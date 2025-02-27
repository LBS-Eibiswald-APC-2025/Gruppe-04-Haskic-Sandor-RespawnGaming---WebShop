<?php

class CartModel
{
    public static function addToCart(int $user_id, int $game_id): void
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO cart (user_id, game_id, quantity, added_at) 
                VALUES (:user_id, :game_id, 1, NOW())
                ON DUPLICATE KEY UPDATE quantity = quantity + 1, added_at = NOW()";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id, ':game_id' => $game_id]);
    }

    public static function removeFromCart(int $user_id, int $game_id): void
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "DELETE FROM cart WHERE user_id = :user_id AND game_id = :game_id";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id, ':game_id' => $game_id]);
    }

    public static function getCartItemsWithDetails(int $user_id): array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT games.id, games.title, games.price, cart.quantity
                FROM cart
                JOIN games ON cart.game_id = games.id
                WHERE cart.user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll();
    }

    public static function checkout(int $user_id): ?int
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $cartItems = self::getCartItemsWithDetails($user_id);
        $totalPrice = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cartItems));

        if ($totalPrice <= 0) {
            return null;
        }

        $sql = "INSERT INTO orders (user_id, total_price, status) VALUES (:user_id, :total_price, 'pending')";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id, ':total_price' => $totalPrice]);
        $orderId = $database->lastInsertId();

        foreach ($cartItems as $item) {
            $sql = "INSERT INTO order_items (order_id, game_id, price_at_purchase) 
                    VALUES (:order_id, :game_id, :price)";
            $query = $database->prepare($sql);
            $query->execute([
                ':order_id' => $orderId,
                ':game_id' => $item['id'],
                ':price' => $item['price']
            ]);
        }

        $sql = "DELETE FROM cart WHERE user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id]);

        return $orderId;
    }

    public static function isGameInCart(int $user_id, int $game_id): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT COUNT(*) FROM cart WHERE user_id = :user_id AND game_id = :game_id";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id, ':game_id' => $game_id]);
        return $query->fetchColumn() > 0;
    }

    public static function hasItems(int $user_id): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT COUNT(*) FROM cart WHERE user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id]);
        return $query->fetchColumn() > 0;
    }


    //löscht automatisch Warenkörbe, die älter als 24 Stunden sind.
    public static function clearOldCarts(): void
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "DELETE FROM cart WHERE added_at < NOW() - INTERVAL 1 DAY";
        $query = $database->prepare($sql);
        $query->execute();
    }
}
