<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<?php
$cartItems = $this->data['cartItems'];
$totalPrice = 0.0;
// Gesamtpreis berechnen anhand der Artikelpreise und Mengen.
foreach ($cartItems as $item) {
    $totalPrice += $item->price * $item->quantity;
}
?>

<main class="container my-5">
    <h1>Mein Warenkorb</h1>

    <?php if (empty($cartItems)): ?>
        <p>Dein Warenkorb ist leer.</p>
    <?php else: ?>
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
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
                    <td>
                        <!-- Spieltitel sicher ausgeben -->
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td>
                        <!-- Preis formatiert darstellen -->
                        €<?php echo number_format($item->price, 2, ',', '.'); ?>
                    </td>
                    <td>
                        <?php echo (int)$item->quantity; ?>
                    </td>
                    <td>
                        <!-- Formular zum Entfernen des Spiels aus dem Warenkorb -->
                        <form action="<?php echo Config::get('URL'); ?>cart/removeFromCart" method="post" class="d-inline">
                            <input type="hidden" name="game_id" value="<?php echo (int)$item->id; ?>">
                            <button type="submit" class="btn btn-danger">
                                Entfernen
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-end fs-5 mt-3">
            <strong>Gesamt:</strong>
            €<?php echo number_format($totalPrice, 2, ',', '.'); ?>
        </div>

        <div class="mt-4 text-end">
            <!-- Formular für den Checkout -->
            <form action="<?php echo Config::get('URL'); ?>cart/checkout" method="post">
                <button type="submit" class="btn btn-primary">
                    Jetzt bestellen
                </button>
            </form>
        </div>
    <?php endif; ?>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
