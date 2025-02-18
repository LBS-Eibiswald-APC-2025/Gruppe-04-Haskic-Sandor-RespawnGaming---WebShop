<?php require APP . 'view/_templates/header.php'; ?>

<div class="auth-page-box">
    <h2 class="text-center">Anmelden</h2>
    <form action="<?php echo Config::get('URL'); ?>login/login" method="post">
        <div class="input-group">
            <input type="text" class="form-control" name="user_name" placeholder="Benutzername" required>
        </div>
        <div class="input-group">
            <input type="password" class="form-control" name="user_password" placeholder="Passwort" required>
            <button type="submit" class="btn btn-primary w-100">Anmelden</button>
        </div>
    </form>
    <div class="text-center mt-3 link">
        <a href="<?php echo Config::get('URL'); ?>register/index">Noch keinen Account? Jetzt registrieren!</a>
    </div>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>
