<?php require APP . 'view/_templates/header.php'; ?>

<main class="info-page">
    <div class="info-section">
        <div class="info-content">
            <!-- Logo und Titel -->
            <div class="info-logo">
                <img src="/public/image/RG_MainLogo.png" alt="Respawn Gaming Logo" class="logo">
                <h1 class="info-title">Respawn Gaming</h1>
            </div>

            <!-- Beschreibung -->
            <p class="info-text">
                Respawn Gaming ist deine Anlaufstelle für alle Spielefans! Hier kannst du spannende neue Spiele entdecken, dich mit Gleichgesinnten austauschen und gemeinsam in virtuelle Welten eintauchen.
            </p>

            <!-- Online-Status -->
            <div class="online-status">
                🟢 Online: <span class="stat-value"><?php echo OnlineModel::getCurrentUsers(); ?></span>
            </div>

            <!-- CTA-Button -->
            <a href="<?php echo Config::get('URL'); ?>games" class="info-btn">Jetzt Spiele entdecken</a>
        </div>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
