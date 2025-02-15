<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respawn Gaming</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo Config::get('URL'); ?>">Respawn Gaming</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo Config::get('URL'); ?>games">Spiele</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo Config::get('URL'); ?>community">Community</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo Config::get('URL'); ?>contact">Info</a></li>
                <?php if (Session::userIsLoggedIn()) : ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo Config::get('URL'); ?>user/index">Mein Konto</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo Config::get('URL'); ?>login/logout">Logout</a></li>
                <?php else : ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo Config::get('URL'); ?>login/index">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo Config::get('URL'); ?>register/index">Registrieren</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
