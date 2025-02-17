<?php require APP . 'view/_templates/header.php'; ?>

<main class="info-page">
    <div class="info-container">
        <!-- Linker Textbereich -->
        <div class="info-text">
            <div class="info-logo">
                <img src="/image/Logo/respawn_mockup.png" alt="Respawn Gaming Logo">
                <h1>Respawn Gaming</h1>
            </div>
            <p class="info-description">
                Respawn Gaming ist deine Anlaufstelle für alle Spielefans! Hier kannst du spannende neue Spiele entdecken, dich mit Gleichgesinnten austauschen und gemeinsam in virtuelle Welten eintauchen.
            </p>
            <div class="info-stats">
                <div class="stat">
                    <span class="stat-label">🟢 Online:</span>
                    <span class="stat-value"><?php echo OnlineModel::getCurrentUsers(); ?></span>
                </div>
            </div>
            <a href="<?php echo Config::get('URL'); ?>games" class="btn btn-primary info-btn">Jetzt Spiele entdecken</a>
        </div>

        <!-- Rechter Bildbereich -->
        <div class="info-image">
            <img src="/image/InfoMockup/Error_404.png" alt="Respawn Gaming Mockup">
        </div>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
