<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<main class="cart-page">
    <div class="cart-container">
        <h2 class="cart-title">Dein Warenkorb</h2>

        <?php
        // Controller-Daten
        $cartItems  = $this->data['cartItems'] ?? [];
        $totalPrice = 0.0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->price * $item->quantity;
        }
        ?>

        <?php if (empty($cartItems)): ?>
            <div class="cart-empty">
                <p>Dein Warenkorb ist leer.</p>
            </div>
        <?php else: ?>
            <div class="cart-table-container">
                <table class="cart-table">
                    <thead>
                    <tr>
                        <th class="cart-col-game">Artikel</th>
                        <th class="cart-col-price">Preis</th>
                        <th class="cart-col-qty">Menge</th>
                        <th class="cart-col-action">Aktion</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr class="cart-item">
                            <td class="cart-game">
                                <div class="cart-gameinfo">
                                    <?php if (!empty($item->cover_image)): ?>
                                        <img src="<?= htmlspecialchars($item->cover_image); ?>"
                                             alt="<?= htmlspecialchars($item->title); ?>"
                                             class="cart-cover">
                                    <?php endif; ?>

                                    <div class="cart-gametitle">
                                        <?= htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                            </td>
                            <td class="cart-price">
                                €<?= number_format($item->price, 2, ',', '.'); ?>
                            </td>
                            <td class="cart-quantity">
                                <?= (int)$item->quantity; ?>
                            </td>
                            <td class="cart-remove">
                                <form action="<?= Config::get('URL'); ?>cart/removeFromCart" method="post">
                                    <input type="hidden" name="game_id" value="<?= (int)$item->id; ?>">
                                    <button type="submit" class="cart-remove-btn">Entfernen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-footer">
                <div class="cart-total">
                    <span class="cart-total-label">GESAMT:</span>
                    <span class="cart-total-value">
                        €<?= number_format($totalPrice, 2, ',', '.'); ?>
                    </span>
                </div>
                <form action="<?= Config::get('URL'); ?>cart/checkout" method="post">
                    <button type="submit" class="cart-checkout-btn">Jetzt bestellen</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
