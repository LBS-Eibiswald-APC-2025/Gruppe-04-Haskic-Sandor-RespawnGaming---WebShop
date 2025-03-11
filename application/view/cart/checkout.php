<h1>Checkout</h1>

<!-- Zeige den Warenkorb und Gesamtpreis -->
<?php if (!empty($this->cartItems)): ?>
    <h2>Deine Artikel:</h2>
    <ul>
        <?php foreach ($this->cartItems as $item): ?>
            <li>
                <?= htmlspecialchars($item['title']) ?> – <?= number_format($item['price'], 2) ?> €
                (Menge: <?= (int)$item['quantity'] ?>)
            </li>
        <?php endforeach; ?>
    </ul>
    <p><strong>Gesamtsumme: <?= number_format($this->totalPrice, 2) ?> €</strong></p>
<?php endif; ?>

<!-- Formular für Zahlungsdaten (fiktiv, z.B. nur eine E-Mail zur Bestätigung) -->
<form action="<?= Config::get('URL') ?>cart/processPayment" method="post">
    <label for="payment_email">Deine E-Mail (für die Rechnung):</label>
    <input type="email" name="payment_email" id="payment_email" required>
    <button type="submit">Zahlen</button>
</form>

<!-- SweetAlert einbinden (falls noch nicht global eingebunden) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (Session::get('feedback_sweetalert_success')): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Erfolg',
            text: '<?= Session::get('feedback_sweetalert_success') ?>'
        });
    </script>
    <?php Session::set('feedback_sweetalert_success', null); ?>
<?php endif; ?>
<?php if (Session::get('feedback_sweetalert_error')): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Fehler',
            text: '<?= Session::get('feedback_sweetalert_error') ?>'
        });
    </script>
    <?php Session::set('feedback_sweetalert_error', null); ?>
<?php endif; ?>
