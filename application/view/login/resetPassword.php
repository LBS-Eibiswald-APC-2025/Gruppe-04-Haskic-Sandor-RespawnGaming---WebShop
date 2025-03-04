<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<div class="password-change-page">
    <div class="password-change-box">
        <h2>Neues Passwort festlegen</h2>

        <form method="post" action="<?php echo Config::get('URL'); ?>login/setNewPassword">

            <input type="hidden" name="hash" value="<?php echo $this->data['user_password_reset_hash']; ?>">

            <div class="mb-3">
                <label for="password" class="pw-change-label">Neues Passwort</label>
                <input type="password" name="password" class="pw-change-input"
                       placeholder="Neues Passwort" required>
            </div>

            <!-- Eingabefeld: Passwort wiederholen -->
            <div class="mb-3">
                <label for="password_repeat" class="pw-change-label">Passwort wiederholen</label>
                <input type="password" name="password_repeat" class="pw-change-input"
                       placeholder="Passwort bestätigen" required>
            </div>

            <!-- Absenden -->
            <button type="submit" class="pw-change-submit-btn">
                Passwort ändern
            </button>
        </form>

        <!-- Link zurück zum Login -->
        <div class="back-to-login">
            <a href="<?php echo Config::get('URL'); ?>login/index">Zurück zum Login</a>
        </div>
    </div>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>
