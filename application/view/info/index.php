<?php require APP . 'view/_templates/header.php'; ?>

<main class="info-page">
    <div class="info-section">
        <div class="info-content">
            <!-- Logo und Titel -->
            <div class="info-logo">
                <img src="../../../public/image/main/RG_MainLogo.png" alt="Respawn Gaming Logo" class="logo">
                <h1 class="info-title">Respawn Gaming</h1>
            </div>

            <!-- Zusätzliche Informationen -->
            <div class="info-details">
                <h2>Über das Projekt</h2>
                <p>
                    Dieses Projekt ist ein Schulprojekt der LBS Eibiswald und dient ausschließlich zu Übungszwecken. Es werden keine echten Inhalte angeboten, verkauft oder vermarktet.
                </p>
                <p>
                    Alle dargestellten Informationen sind fiktiv und demonstrieren lediglich Webtechnologien.
                </p>
            </div>

            <!-- Online-Status -->
            <div class="online-status">
                <p>Aktuelle Nutzer</p> 🟢 Online: <span class="stat-value"><?php echo OnlineModel::getCurrentUsers(); ?></span>
            </div>

            <!-- Call-to-Action-Button -->
            <a href="<?php echo Config::get('URL'); ?>register" class="info-btn">Registrieren</a>
        </div>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
