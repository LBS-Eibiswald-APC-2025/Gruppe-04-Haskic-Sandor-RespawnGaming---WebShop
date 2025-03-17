<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

    <main class="success-page">
        <div class="success-container">
            <h2 class="success-title">Bestellung erfolgreich</h2>

            <div class="success-message">
                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <p>Vielen Dank für Ihre Bestellung! Eine Bestätigung mit Ihrer Rechnung wurde an Ihre E-Mail-Adresse gesendet.</p>
            </div>

            <div class="order-details">
                <h3>Bestelldetails</h3>
                <p><strong>Bestellnummer:</strong> <?= htmlspecialchars($this->data['orderId']); ?></p>

                <h4>Gekaufte Artikel</h4>
                <ul class="order-items">
                    <?php foreach ($this->data['orderDetails']['items'] as $item): ?>
                        <li>
                            <span class="item-title"><?= htmlspecialchars($item['title']); ?></span>
                            <span class="item-quantity">x <?= (int)$item['quantity']; ?></span>
                            <span class="item-price">€<?= number_format($item['price'], 2, ',', '.'); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="order-total">
                    <strong>Gesamtbetrag:</strong> €<?= number_format($this->data['orderDetails']['total'], 2, ',', '.'); ?>
                </div>
            </div>

            <div class="next-steps">
                <h3>Nächste Schritte</h3>
                <p>Deine digitalen Artikel wurden an deine E-Mail-Adresse gesendet. Falls du Fragen zu deiner Bestellung hast, kontaktiere bitte unseren Kundenservice.</p>

                <div class="action-buttons">
                    <a href="<?= Config::get('URL'); ?>games/index" class="btn btn-primary">Weitere Spiele entdecken</a>
                    <a href="<?= Config::get('URL'); ?>account/orders" class="btn btn-secondary">Zu meinen Bestellungen</a>
                </div>
            </div>
        </div>
    </main>

<?php require APP . 'view/_templates/footer.php'; ?>