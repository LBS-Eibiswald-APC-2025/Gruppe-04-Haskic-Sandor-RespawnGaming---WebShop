<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<?php
// Prüfen, ob User eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . Config::get('URL') . 'login?error=not_logged_in');
    exit();
}

// Warenkorb-Daten aus der Datenbank holen
$cartItems = CartModel::getCartItemsWithDetails($_SESSION['user_id']);

// Gesamtsumme berechnen
$totalPrice = 0.0;
foreach ($cartItems as $item) {
    // $item ist ein stdClass-Objekt, darum -> statt []
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
                        <!-- Titel des Spiels -->
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td>
                        <!-- Preis formatieren -->
                        €<?php echo number_format($item->price, 2, ',', '.'); ?>
                    </td>
                    <td>
                        <!-- Anzahl -->
                        <?php echo (int)$item->quantity; ?>
                    </td>
                    <td>
                        <!-- Button zum Entfernen -->
                        <form action="<?php echo Config::get('URL'); ?>cart/removeFromCart/<?php echo (int)$item->id; ?>"
                              method="post" class="d-inline">
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
            <form action="<?php echo Config::get('URL'); ?>cart/checkout" method="post">
                <button type="submit" class="btn btn-primary">
                    Jetzt bestellen
                </button>
            </form>
        </div>
    <?php endif; ?>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
