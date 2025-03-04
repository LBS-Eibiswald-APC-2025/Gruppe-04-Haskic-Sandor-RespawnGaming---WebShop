<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<div class="password-reset-page">
    <div class="password-reset-box">
        <h2>Passwort zurücksetzen</h2>

        <form method="post" action="<?php echo Config::get('URL'); ?>login/submitPasswordReset">
            <!-- Eingabefeld: Benutzername oder E-Mail -->
            <div class="mb-3">
                <label for="user_name_or_email" class="pw-label">
                    Bitte gib deinen Benutzernamen oder deine E-Mail-Adresse ein
                </label>
                <input type="text" name="user_name_or_email" class="pw-input"
                       placeholder="Benutzername oder E-Mail" required>
            </div>

            <!-- Captcha-Bereich -->
            <div class="mb-3">
                <img id="captcha" src="<?php echo Config::get('URL'); ?>register/showCaptcha"
                     alt="Captcha" class="pw-captcha-image">

                <div class="captcha-reload-container">
                    <button type="button" class="pw-captcha-btn"
                            onclick="document.getElementById('captcha').src = '<?php echo Config::get('URL'); ?>register/showCaptcha?' + Math.random(); return false;">Neues Captcha laden
                    </button>
                </div>

                <input type="text" name="captcha" class="pw-input mt-2" placeholder="Captcha eingeben" required>
            </div>

            <!-- Absenden -->
            <button type="submit" class="pw-submit-btn">
                Passwort zurücksetzen
            </button>
        </form>

        <!-- Link zurück zum Login -->
        <div class="back-to-login">
            <a href="<?php echo Config::get('URL'); ?>login/index">Zurück zum Login</a>
        </div>
    </div>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>
