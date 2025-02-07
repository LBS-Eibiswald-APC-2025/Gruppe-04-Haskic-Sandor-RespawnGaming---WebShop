<?php
/** @var $this View */
require_once APP . 'model/Game.php';
$games = Game::getAllGames();
?>

<!-- Header nur einmal einbinden -->
<?php require APP . 'view/_templates/header.php'; ?>

<header class="hero-section text-dark text-center">
    <div class="container">
        <h1>Willkommen bei Respawn Gaming</h1>
        <p>Dein Gaming-Portal für neue Spiele, Bewertungen und Community-Features.</p>
        <a href="<?php echo Config::get('URL'); ?>games" class="btn btn-primary">Jetzt entdecken</a>
    </div>
</header>

<section class="container my-5">
    <h2 class="text-center mb-4">Beliebte Spiele</h2>
    <div class="row">
        <?php foreach ($games as $game) : ?>
            <div class="col-md-4">
                <div class="card game-card">
                    <img src="<?php echo Config::get('URL') . 'assets/images/' . $game->image; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($game->title); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($game->title); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($game->description); ?></p>
                        <a href="<?php echo Config::get('URL') . 'games/details/' . $game->id; ?>" class="btn btn-outline-primary">Mehr erfahren</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Footer einbinden -->
<?php require APP . 'view/_templates/footer.php'; ?>
