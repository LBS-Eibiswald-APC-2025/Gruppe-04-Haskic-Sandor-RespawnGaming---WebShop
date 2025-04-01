<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<main class="checkout-page">
    <div class="checkout-container">
        <h2 class="checkout-title">Kasse</h2>

        <div class="checkout-content">
            <div class="checkout-block">
                <div class="checkout-summary">
                    <h3 class="checkout-heading">Zahlungsmethode</h3>
                    <p>Derzeit ist nur PayPal als Zahlungsmethode verfügbar. Eine Rechnung wird an deine E-Mail-Adresse gesendet.</p>
                    <h3 class="summary-heading">Bestellübersicht</h3>
                    <?php if (!empty($this->data['cartItems'])): ?>
                        <ul class="summary-list">
                            <?php foreach ($this->data['cartItems'] as $item): ?>
                                <li class="summary-item">
                                    <span class="item-title"><?= htmlspecialchars($item['title']); ?></span>
                                    <span class="item-quantity">x <?= (int)$item['quantity']; ?></span>
                                    <span class="item-price">
                    €<?= number_format($item['price'], 2, ',', '.'); ?>
                  </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="summary-total">
                            <span>Gesamt:</span>
                            <span class="summary-total-value">
                €<?= number_format($this->data['totalPrice'] ?? 0, 2, ',', '.'); ?>
              </span>
                        </div>
                    <?php else: ?>
                        <p>Keine Artikel im Warenkorb.</p>
                    <?php endif; ?>
                </div>

                <div class="paypal-only">

                    <p class="payment-note">Bitte logge dich mit diesen Sandbox-Zugangsdaten ein, um deinen Testkauf abzuschließen.</p>
                    <p class="sandbox-info">
                        <strong>PayPal Sandbox:</strong>
                    <p>E-Mail: berndvonbrot@respawngaming.at</p>
                    <p>Passwort: 12345678</p>
                </div>

                <!-- Button, um den Kauf abzuschließen -->
                <form action="<?= Config::get('URL'); ?>cart/process" method="post" class="checkout-final">
                    <button type="submit" class="checkout-submit-btn">Kauf abschließen</button>
                </form>
            </div>

        </div>
    </div>
</main>

<!-- Fake PayPal Modal -->
<div id="fakePaypalModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Fake PayPal Login</h2>
        <form id="fakePaypalForm">
            <div class="modal-form-group">
                <label for="paypalEmail">E-Mail-Adresse</label>
                <input type="email" id="paypalEmail" name="paypalEmail" placeholder="E-Mail-Adresse" required>
            </div>
            <div class="modal-form-group">
                <label for="paypalPassword">Passwort</label>
                <input type="password" id="paypalPassword" name="paypalPassword" placeholder="Passwort" required>
            </div>
            <div class="modal-form-group">
                <label for="paymentAmount">Betrag</label>
                <input type="text" id="paymentAmount" name="paymentAmount" value="€<?= number_format($this->data['totalPrice'] ?? 0, 2, ',', '.'); ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Zahlung bestätigen</button>
        </form>
    </div>
</div>

<!-- Inline JavaScript für Modal-Funktionalität -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('fakePaypalModal');
        const trigger = document.getElementById('fakePaypalTrigger');
        const closeBtn = modal.querySelector('.close');

        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            modal.style.display = 'block';
        });
        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
        window.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        document.getElementById('fakePaypalForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // Simuliere eine kurze Ladezeit und fake Zahlung
            setTimeout(function () {
                alert("Fake Zahlung erfolgreich. Danke für deine Bestellung!");
                modal.style.display = 'none';
                // Optional: Weiterleitung zur Erfolgsseite, z.B.
                // window.location.href = "<?= Config::get('URL'); ?>checkout/success";
            }, 1000);
        });
    });
</script>

<?php require APP . 'view/_templates/footer.php'; ?>
