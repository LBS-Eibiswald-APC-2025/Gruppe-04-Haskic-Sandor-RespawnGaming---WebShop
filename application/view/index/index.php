<!-- Header einbinden -->
<?php require APP . 'view/_templates/header.php'; ?>

<?php
$games = [
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/620/header.jpg',
        'title' => 'Portal 2',
        'description' => 'Ein cooles Spiel, das dich fesselt.'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/220/header.jpg',
        'title' => 'Half-Life 2',
        'description' => 'Ein spannendes Abenteuer wartet auf dich.'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/550/header.jpg',
        'title' => 'Left 4 Dead 2',
        'description' => 'Tauche ein in eine fantastische Welt.'
    ]
];
?>

<main>
    <div class="inner">
        <header class="hero-section text-center">
            <div class="container">
                <h1>Willkommen bei Respawn Gaming</h1>
                <p>Entdecke Spiele und tritt unserer Gamer-Community bei!</p>
                <a href="<?php echo Config::get('URL'); ?>games" class="btn btn-primary">Jetzt loslegen</a>
                <div class="search-bar mt-4">
                    <form action="<?php echo Config::get('URL'); ?>games/search" method="post">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Suche nach Spielen">
                            <button type="submit" class="btn btn-primary">Suchen</button>
                        </div>
                    </form>
                </div>
            </div>
        </header>

        <section class="container my-5">
            <h2 class="text-center mb-4">Beliebte Spiele</h2>
            <div class="d-flex flex-nowrap justify-content-between">
                <?php if (!empty($games)): ?>
                    <?php foreach ($games as $game): ?>
                        <div class="card game-card">
                            <img src="<?php echo htmlspecialchars($game->image); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($game->title); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($game->title); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($game->description); ?></p>
                                <a href="#" class="btn btn-outline-primary">Mehr erfahren</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-danger">Keine Spiele verfügbar.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<!-- Footer einbinden -->
<?php require APP . 'view/_templates/footer.php'; ?>
