<?php
class CartModel
{
    /**
     * Fügt ein Spiel zum Warenkorb (in der Session) hinzu.
     * Existiert das Spiel bereits, wird die Menge erhöht.
     *
     * @param int $game_id ID des hinzuzufügenden Spiels.
     */
    public static function addToCart(int $game_id): void
    {
        // Wenn noch kein Warenkorb existiert, anlegen.
        if (Session::get('cart') === null || !is_array(Session::get('cart'))) {
            Session::set('cart', []);
        }

        // Falls das Spiel schon im Warenkorb ist, Menge erhöhen.
        if (isset(Session::get('cart')[$game_id])) {
            Session::set('cart', Session::get('cart')[$game_id] + 1, $game_id);
        } else {
            Session::set('cart', 1, $game_id);
        }
    }

    /**
     * Entfernt ein Spiel aus dem Warenkorb (Session).
     *
     * @param int $game_id ID des zu entfernenden Spiels.
     */
    public static function removeFromCart(int $game_id): void
    {
        if (isset(Session::get('cart')[$game_id])) {
            if (Session::get('cart')[$game_id] > 1) {
                Session::set('cart', Session::get('cart')[$game_id] - 1, $game_id);
            } else {
                Session::remove('cart', $game_id);
            }
        }
    }

    /**
     * Holt alle Warenkorbeinträge mitsamt Spieldetails.
     * Hierzu werden die Spiel-IDs aus der Session gelesen und
     * die Details per GameModel::getGameById geholt.
     *
     * @return array Enthält Objekte mit Spielinformationen und der Menge.
     */
    public static function getCartItemsWithDetails(): array
    {
        $cartItems = [];
        if (empty(Session::get('cart'))) {
            return $cartItems;
        }

        // Über jeden Eintrag im Session-Warenkorb iterieren.
        foreach (Session::get('cart') as $game_id => $quantity) {
            $game = GameModel::getGameById($game_id);
            if ($game) {
                // Menge als Eigenschaft hinzufügen, sodass die View sie nutzen kann.
                $game->quantity = $quantity;
                $cartItems[] = $game;
            }
        }
        return $cartItems;
    }

    /**
     * Führt den Checkout durch:
     * - Berechnet den Gesamtpreis
     * - Legt die Bestellung in der Datenbank an
     * - Fügt die Bestellpositionen hinzu
     * - Leert den Session-Warenkorb
     *
     * @param int $user_id ID des Benutzers, der bestellt.
     * @return int|null ID der Bestellung oder null bei Fehler.
     */
    public static function checkout(int $user_id): ?int
    {
        $cartItems = self::getCartItemsWithDetails();
        $totalPrice = 0.0;

        foreach ($cartItems as $item) {
            $totalPrice += $item->price * $item->quantity;
        }

        if ($totalPrice <= 0) {
            return null;
        }

        // Bestellung in der Datenbank anlegen.
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO orders (user_id, total_price, status) VALUES (:user_id, :total_price, 'pending')";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id, ':total_price' => $totalPrice]);
        $orderId = $database->lastInsertId();

        // Für jedes Spiel einen Eintrag in der Bestellpositionen-Tabelle anlegen.
        foreach ($cartItems as $item) {
            $sql = "INSERT INTO order_items (order_id, game_id, price_at_purchase) 
                    VALUES (:order_id, :game_id, :price)";
            $query = $database->prepare($sql);
            $query->execute([
                ':order_id' => $orderId,
                ':game_id' => $item->id,
                ':price' => $item->price
            ]);
        }

        // Warenkorb in der Session leeren.
        Session::remove('cart');
        return $orderId;
    }

    /**
     * Prüft, ob ein bestimmtes Spiel im Warenkorb enthalten ist.
     *
     * @param int $game_id ID des zu prüfenden Spiels.
     * @return bool true, wenn vorhanden.
     */
    public static function isGameInCart(int $game_id): bool
    {
        return Session::get('cart', $game_id) !== null;
    }

    /**
     * Überprüft, ob der Warenkorb Einträge enthält.
     *
     * @return bool true, wenn mindestens ein Eintrag vorhanden ist.
     */
    public static function hasItems(): bool
    {
        return !empty(Session::get('cart'));
    }

    /**
     * Alte Warenkörbe bereinigen – mit Session ist das nicht notwendig,
     * deshalb bleibt diese Methode leer.
     */
    public static function clearOldCarts(): void
    {
        // Keine Aktion nötig, da Session-Warenkorb nicht zeitabhängig ist.
    }
}
