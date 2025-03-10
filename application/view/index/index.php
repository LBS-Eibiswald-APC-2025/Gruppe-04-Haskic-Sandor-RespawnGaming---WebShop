<?php require APP . 'view/_templates/header.php'; ?>

<?php
// Beispiel: Datenarrays für Spiele und "featured_games"
$games = [
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/550/header.jpg',
        'title' => 'Left 4 Dead 2',
        'description' => 'Tauche ein in eine fantastische Welt.',
        'url' => Config::get('URL') . 'games/detail/8'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/440/header.jpg',
        'title' => 'Team Fortress 2',
        'description' => 'Team-basierter Shooter mit Humor.',
        'url' => Config::get('URL') . 'games/detail/9'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/570/header.jpg',
        'title' => 'Dota 2',
        'description' => 'Strategie und Teamwork in einem MOBA.',
        'url' => Config::get('URL') . 'games/detail/10'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/730/header.jpg',
        'title' => 'Counter Strike 2',
        'description' => 'Intensiver Taktik-Shooter im Wettkampf.',
        'url' => Config::get('URL') . 'games/detail/11'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/292030/header.jpg',
        'title' => 'The Witcher 3',
        'description' => 'Episches Rollenspiel in einer offenen Welt.',
        'url' => Config::get('URL') . 'games/detail/1'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/413150/header.jpg',
        'title' => 'Stardew Valley',
        'description' => 'Entspanne auf dem Land und baue deine Farm aus.',
        'url' => Config::get('URL') . 'games/detail/12'
    ]
];

$featured_games = [
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1551360/header.jpg',
        'title' => 'Forza Horizon 5',
        'description' => 'Bald verfügbar',
        'price' => '59,99€',
        'url' => Config::get('URL') . 'games/detail/3'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1190460/header.jpg',
        'title' => 'Death Stranding',
        'description' => 'Bald verfügbar',
        'price' => '39,99€',
        'url' => Config::get('URL') . 'games/detail/4'
    ],
    (object)[
        'image' => 'https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/3240220/header.jpg?t=1741381848',
        'title' => 'GTA V Enhanced',
        'description' => 'Bald verfügbar',
        'price' => '29,99€',
        'url' => Config::get('URL') . 'games/detail/5'
    ],
    (object)[
        'image' => 'https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/3159330/header.jpg?t=1741374708',
        'title' => 'AC Shadows',
        'description' => 'Bald verfügbar',
        'price' => '69,99€',
        'url' => Config::get('URL') . 'games/detail/6'
    ],
    (object)[
        'image' => 'https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/1599340/header.jpg?t=1736361963',
        'title' => 'Lost Ark',
        'description' => 'Nicht mehr verfügbar',
        'price' => 'Gratis',
        'url' => Config::get('URL') . 'games/detail/7'
    ]
];

$AllGames = GamesModel::getAllGames(5);
?>

<main>
    <div class="inner">
        <!-- Hero-Section -->
        <header class="hero-section text-center">
            <div class="container">
                <h1>Willkommen bei Respawn Gaming</h1>
                <p>Entdecke Spiele und tritt unserer Gamer-Community bei!</p>
                <a href="<?php echo Config::get('URL'); ?>games" class="btn-primary">Jetzt Loslegen!</a>
                <div class="search-bar mt-4">
                    <form action="<?php echo Config::get('URL'); ?>games/search" method="post">
                        <div class="input-group">
                            <label>
                                <input type="text" name="search" class="form-control" placeholder="Suche nach Spielen">
                            </label>
                            <button type="submit" class="btn-secondary">Suchen</button>
                        </div>
                    </form>
                </div>
            </div>
        </header>

        <!-- Game Carousel -->
        <h2 class="text-center mb-4 custom-carousel-title">Angesagt und Beliebt</h2>
        <section class="custom-carousel-container container">
            <!-- Vor-/Zurück-Buttons -->
            <button class="carousel-btn" id="carouselPrev">&lt;</button>

            <div class="carousel-slide" id="carouselSlide">
                <div class="carousel-image">
                    <img id="carouselImg" src="" alt="">
                </div>
                <div class="carousel-info">
                    <h3 id="carouselTitle"></h3>

                    <!-- NEU: 2x2 Thumbnails -->
                    <div class="carousel-thumbnails" id="carouselThumbnails"></div>

                    <p id="carouselDescription"></p>
                    <span id="carouselPrice"></span>
                </div>
            </div>

            <button class="carousel-btn" id="carouselNext">&gt;</button>
        </section>

        <!-- Indikatoren (Punkte) -->
        <div class="carousel-indicators-custom" id="carouselIndicators"></div>

        <!-- NEU: Zusätzlicher Status (z. B. "1 / 4") -->
        <div class="carousel-status" id="carouselStatus"></div>

        <!-- Übergabe der PHP-Array-Daten an dein JS-Karussell -->
        <script>
            window.allgames = <?php echo json_encode($AllGames); ?>;
        </script>

        <!-- Game Cards -->
        <section class="container my-5">
            <h2 class="text-center mb-4">Vorgeschlagene Spiele</h2>
            <div class="row">
                <?php if (!empty($games)): ?>
                    <?php foreach ($games as $game): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card game-card">
                                <img src="<?php echo htmlspecialchars($game->image); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($game->title); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($game->title); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($game->description); ?></p>

                                    <!-- Wenn url nicht gesetzt ist, verlinken wir zum Haupt-Games-Bereich -->
                                    <a href="<?php echo isset($game->url) ? htmlspecialchars($game->url) : Config::get('URL') . 'games'; ?>" class="btn btn-outline-primary">Mehr erfahren</a>
                                </div>
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

<script src="/public/js/main/carousel.js"></script>

<?php require APP . 'view/_templates/footer.php'; ?>
