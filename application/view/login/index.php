<?php
require APP . 'view/_templates/header.php';
require APP . 'view/_templates/feedback.php';
$token = Csrf::makeToken();
?>

    <div class="auth-page-box">
        <h2 class="text-center">Anmelden</h2>
        <form action="<?php echo Config::get('URL'); ?>login/login" method="post">
            <div class="input-group">
                <input type="text" class="form-control" name="user_name" placeholder="Benutzername" required>
            </div>
            <div class="input-group">
                <input type="password" class="form-control" name="user_password" placeholder="Passwort" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Anmelden</button>
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        </form>
        <div class="text-center mt-3 link">
            <a href="<?php echo Config::get('URL'); ?>login/requestPasswordReset">Passwort vergessen?</a>
        </div>
        <div class="text-center mt-3 link">
            <a href="<?php echo Config::get('URL'); ?>register/index">Noch keinen Account? Jetzt registrieren!</a>
        </div>
    </div>

<?php require APP . 'view/_templates/footer.php'; ?>