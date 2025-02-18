<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<div class="auth-page-box">
    <h2 class="text-center">Registrieren</h2>
    <form method="post" action="<?php echo Config::get('URL'); ?>register/register_action">
        <input type="text" class="form-control mb-3" name="user_name" placeholder="Benutzername" required>
        <input type="email" class="form-control mb-3" name="user_email" placeholder="E-Mail-Adresse" required>
        <input type="password" class="form-control mb-3" name="user_password_new" placeholder="Passwort" required>
        <input type="password" class="form-control mb-3" name="user_password_repeat" placeholder="Passwort wiederholen"
               required>
        <button type="submit" class="btn btn-primary w-100">Registrieren</button>
    </form>
    <div class="text-center mt-3 link">
        <a href="<?php echo Config::get('URL'); ?>login/index">Schon einen Account? Hier einloggen.</a>
    </div>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>
