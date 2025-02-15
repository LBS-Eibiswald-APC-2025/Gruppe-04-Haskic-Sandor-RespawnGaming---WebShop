<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Respawn Gaming</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="auth-page-box">
    <h2 class="text-center">Login</h2>
    <form action="<?php echo Config::get('URL'); ?>login/login" method="post">
        <input type="text" class="form-control mb-3" name="user_name" placeholder="Benutzername" required>
        <input type="password" class="form-control mb-3" name="user_password" placeholder="Passwort" required>
        <button type="submit" class="btn btn-primary w-100">Einloggen</button>
    </form>
    <div class="text-center mt-3">
        <a href="<?php echo Config::get('URL'); ?>register/index">Noch keinen Account? Jetzt registrieren!</a>
    </div>
</div>
