<?php
require_once APP . 'model/OnlineModel.php';
OnlineModel::trackUser();

$uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$uri = trim($uri, '/');
$segments = explode('/', $uri);
$view = $segments[0] ?: 'index';

$cartCount = 0;
$userId = Session::get('user_id');
$cartItems = CartModel::getCartItemsWithDetails();
foreach ($cartItems as $item) {
    $cartCount += (int)$item->quantity;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Respawn Gaming</title>

    <link rel="stylesheet" href="/public/scss/main/style.css">
    <?php
        $scssFiles = glob(__DIR__ . '/../../../public/scss/' . $view . '/*.scss');
        foreach ($scssFiles as $scssFile) {
            echo '<link rel="stylesheet" href="/public/scss/' . $view . '/' . basename($scssFile, '.scss') . '.css">';
        }
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/public/scss/google/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://kit.fontawesome.com/9a7be7a56e.js" crossorigin="anonymous"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
<div id="page-loader">
    <div class="loader"></div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo Config::get('URL'); ?>">
            <img src="/public/image/RG_MainLogo.png" alt="Respawn Gaming Logo" class="logo me-2">Respawn Gaming</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <?php $current_page = $_SERVER['REQUEST_URI']; ?>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Warenkorb-Link mit DB-basiertem Zähler -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (str_contains($current_page, '/cart')) ? 'active-link' : ''; ?>"
                       href="<?php echo Config::get('URL'); ?>cart">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="cart-count">
                            <?php echo $cartCount; ?>
                        </span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo (str_contains($current_page, '/games')) ? 'active-link' : ''; ?>"
                       href="<?php echo Config::get('URL'); ?>games">Spiele</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (str_contains($current_page, '/community')) ? 'active-link' : ''; ?>"
                       href="<?php echo Config::get('URL'); ?>community">Community</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (str_contains($current_page, '/info')) ? 'active-link' : ''; ?>"
                       href="<?php echo Config::get('URL'); ?>info">Info</a>
                </li>

                <?php if (Session::userIsLoggedIn()) : ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (str_contains($current_page, '/user/index')) ? 'active-link' : ''; ?>"
                           href="<?php echo Config::get('URL'); ?>user/index">Mein Konto</a>
                    </li>
                    <?php
                    $userData = Session::get('user_data');
                    if (isset($userData['user_account_type']) && $userData['user_account_type'] === 'Admin'):
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (str_contains($current_page, '/admin/index')) ? 'active-link' : ''; ?>"
                               href="<?php echo Config::get('URL'); ?>admin/index">Admin</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo Config::get('URL'); ?>login/logout">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (str_contains($current_page, '/login/index')) ? 'active-link' : ''; ?>"
                           href="<?php echo Config::get('URL'); ?>login/index">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Ladebildschirm-Skript -->
<script>
    window.addEventListener('beforeunload', () => {
        document.getElementById('page-loader').style.display = 'flex';
    });

    window.addEventListener('load', () => {
        hideLoader();
    });

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            hideLoader();
        }
    });

    function hideLoader() {
        const loader = document.getElementById('page-loader');
        if (!loader) return;
        loader.style.opacity = '0';
        setTimeout(() => loader.style.display = 'none', 500);
    }

    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function (e) {
            if (this.href && this.target !== '_blank' && !this.href.startsWith('#')) {
                e.preventDefault();
                document.getElementById('page-loader').classList.remove('hidden');
                document.getElementById('page-loader').style.display = 'flex';
                setTimeout(() => {
                    window.location.href = this.href;
                }, 300);
            }
        });
    });
</script>
