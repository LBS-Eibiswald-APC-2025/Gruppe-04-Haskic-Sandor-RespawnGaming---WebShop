<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<main class="cart-hero-section">
    <!-- Ganzseitiger Hero-Hintergrund -->
    <div class="cart-hero-background">

        <!-- Zentriertes Panel mit abgerundeten Ecken -->
        <div class="cart-hero-content">
            <h1>Mein Warenkorb</h1>

            <?php
            $cartItems = $this->data['cartItems'] ?? [];
            $totalPrice = 0.0;
            foreach ($cartItems as $item) {
                $totalPrice += $item->price * $item->quantity;
            }
            ?>

            <?php if (empty($cartItems)): ?>
                <p>Dein Warenkorb ist leer.</p>
            <?php else: ?>
                <table class="steam-cart-table">
                    <thead>
                    <tr>
                        <th>Spiel</th>
                        <th>Preis</th>
                        <th>Menge</th>
                        <th>Aktion</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>€<?php echo number_format($item->price, 2, ',', '.'); ?></td>
                            <td><?php echo (int)$item->quantity; ?></td>
                            <td>
                                <form action="<?php echo Config::get('URL'); ?>cart/removeFromCart" method="post">
                                    <input type="hidden" name="game_id" value="<?php echo (int)$item->id; ?>">
                                    <button type="submit" class="remove-btn">Entfernen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-total">
                    <strong>Gesamt: </strong>
                    €<?php echo number_format($totalPrice, 2, ',', '.'); ?>
                </div>

                <form action="<?php echo Config::get('URL'); ?>cart/checkout" method="post">
                    <button type="submit" class="checkout-btn">Jetzt bestellen</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
