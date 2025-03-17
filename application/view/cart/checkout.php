<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<main class="checkout-page">
    <div class="checkout-container">
        <h2 class="checkout-title">Kasse</h2>

        <!-- Flex-Container: linke Spalte für persönliche Daten, rechte für Bestellübersicht & Fake-PayPal -->
        <div class="checkout-content">

            <!-- Linke Spalte: Persönliche Daten -->
            <div class="checkout-left">
                <h3 class="section-title">Persönliche Daten</h3>
                <form action="<?= Config::get('URL'); ?>checkout/complete" method="post" class="checkout-form">
                    <div class="form-group">
                        <label for="firstname">Vorname</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Nachname</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-Mail-Adresse</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-info">
                        <p>Deine digitalen Inhalte werden nach Zahlung an diese E-Mail-Adresse gesendet.</p>
                    </div>
                </form>
            </div>

            <!-- Rechte Spalte: Bestellübersicht und Fake-PayPal -->
            <div class="checkout-right">
                <div class="checkout-summary">
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

                <div class="checkout-payment">
                    <h3 class="payment-heading">Zahlungsmethode</h3>
                    <div class="paypal-only">
                        <p>Einfache und sichere Bezahlung mit PayPal.</p>
                        <p>sb-1f5by38768555@personal.example.com</p>
                        <p>pTo^qil5</p>
                        <!-- Klick auf das Logo öffnet den Fake-PayPal-Modal -->
                        <a href="#" id="fakePaypalTrigger" title="Fake PayPal">
                            <img src="https://www.paypalobjects.com/webstatic/de_DE/i/de-pp-logo-150px.png" alt="Fake PayPal Logo" class="paypal-logo">
                        </a>
                        <p class="payment-note">Nach Abschluss der Zahlung erhältst du sofortigen Zugriff auf deine digitalen Inhalte.</p>
                    </div>
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
    document.addEventListener('DOMContentLoaded', function(){
        const modal = document.getElementById('fakePaypalModal');
        const trigger = document.getElementById('fakePaypalTrigger');
        const closeBtn = modal.querySelector('.close');

        trigger.addEventListener('click', function(e){
            e.preventDefault();
            modal.style.display = 'block';
        });
        closeBtn.addEventListener('click', function(){
            modal.style.display = 'none';
        });
        window.addEventListener('click', function(e){
            if(e.target === modal) {
                modal.style.display = 'none';
            }
        });

        document.getElementById('fakePaypalForm').addEventListener('submit', function(e){
            e.preventDefault();
            // Simuliere eine kurze Ladezeit und fake Zahlung
            setTimeout(function(){
                alert("Fake Zahlung erfolgreich. Danke für deine Bestellung!");
                modal.style.display = 'none';
                // Optional: Weiterleitung zur Erfolgsseite, z.B.
                // window.location.href = "<?= Config::get('URL'); ?>checkout/success";
            }, 1000);
        });
    });
</script>

<?php require APP . 'view/_templates/footer.php'; ?>
