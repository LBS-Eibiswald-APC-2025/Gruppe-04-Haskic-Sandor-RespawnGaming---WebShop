<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung - Respawn Gaming</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="auth-page-box">
    <h2 class="text-center">Registrieren</h2>
    <form method="post" action="<?php echo Config::get('URL'); ?>register/register_action">
        <input type="text" class="form-control mb-3" name="user_name" placeholder="Benutzername" required>
        <input type="email" class="form-control mb-3" name="user_email" placeholder="E-Mail-Adresse" required>
        <input type="password" class="form-control mb-3" name="user_password_new" placeholder="Passwort" required>
        <input type="password" class="form-control mb-3" name="user_password_repeat" placeholder="Passwort wiederholen" required>
        <button type="submit" class="btn btn-primary w-100">Registrieren</button>
    </form>
    <div class="text-center mt-3">
        <a href="<?php echo Config::get('URL'); ?>login/index">Schon einen Account? Hier einloggen.</a>
    </div>
</div>
