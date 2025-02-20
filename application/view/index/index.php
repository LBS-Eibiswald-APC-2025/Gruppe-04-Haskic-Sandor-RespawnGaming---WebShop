<?php require APP . 'view/_templates/header.php'; ?>

<?php
// Beispiel: Datenarrays für Spiele und "featured_games"
$games = [
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/620/header.jpg',
        'title' => 'Portal 2',
        'description' => 'Ein cooles Spiel, das dich fesselt.',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/220/header.jpg',
        'title' => 'Half-Life 2',
        'description' => 'Ein spannendes Abenteuer wartet auf dich.',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/550/header.jpg',
        'title' => 'Left 4 Dead 2',
        'description' => 'Tauche ein in eine fantastische Welt.',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/440/header.jpg',
        'title' => 'Team Fortress 2',
        'description' => 'Team-basierter Shooter mit Humor.',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/570/header.jpg',
        'title' => 'Dota 2',
        'description' => 'Strategie und Teamwork in einem MOBA.',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/730/header.jpg',
        'title' => 'CS:GO',
        'description' => 'Intensiver Taktik-Shooter im Wettkampf.',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/292030/header.jpg',
        'title' => 'The Witcher 3',
        'description' => 'Episches Rollenspiel in einer offenen Welt.',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/413150/header.jpg',
        'title' => 'Stardew Valley',
        'description' => 'Entspanne auf dem Land und baue deine Farm aus.',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/252950/header.jpg',
        'title' => 'Rocket League',
        'description' => 'Fussball trifft auf schnelle Autos.',
        'url' => Config::get('URL') . 'games'
    ]
];

$featured_games = [
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1551360/header.jpg',
        'title' => 'Forza Horizon 5',
        'description' => 'Erkunde eine offene Welt voller Rennen und Abenteuer.',
        'price' => '59,99€',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1190460/header.jpg',
        'title' => 'Death Stranding',
        'description' => 'Erlebe epische Schlachten im Halo-Universum.',
        'price' => '39,99€',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/271590/header.jpg',
        'title' => 'GTA V',
        'description' => 'Der Klassiker unter den Open-World-Spielen.',
        'price' => '29,99€',
        'url' => Config::get('URL') . 'games'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/252950/header.jpg',
        'title' => 'Rocket League',
        'description' => 'Autos und Fußball kombiniert zu einer actiongeladenen Erfahrung.',
        'price' => 'Gratis',
        'url' => Config::get('URL') . 'games'
    ]
];
?>

<main>
    <div class="inner">
        <!-- Hero-Section -->
        <header class="hero-section text-center">
            <div class="container">
                <h1>Willkommen bei Respawn Gaming</h1>
                <p>Entdecke Spiele und tritt unserer Gamer-Community bei!</p>
                <a href="<?php echo Config::get('URL'); ?>games" class="btn-primary">Jetzt loslegen</a>
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
        <section class="custom-carousel-container container my-5">
            <!-- Vor-/Zurück-Buttons -->
            <button class="carousel-btn" id="carouselPrev">&lt;</button>

            <div class="carousel-slide" id="carouselSlide">
                <div class="carousel-image">
                    <img id="carouselImg" src="" alt="">
                </div>
                <div class="carousel-info">
                    <h3 id="carouselTitle"></h3>
                    <p id="carouselDescription"></p>
                    <span id="carouselPrice"></span>
                </div>
            </div>

            <button class="carousel-btn" id="carouselNext">&gt;</button>
        </section>
        <div class="carousel-indicators-custom" id="carouselIndicators"></div>

        <!-- Übergabe der PHP-Array-Daten an dein JS-Karussell -->
        <script>
            window.featuredGames = <?php echo json_encode($featured_games); ?>;
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
                                    <a href="<?php echo isset($game->url) ? htmlspecialchars($game->url) : Config::get('URL') . 'games'; ?>" class="btn btn-outline-primary">
                                        Mehr erfahren
                                    </a>
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

<script src="../../../public/js/main/carousel.js"></script>

<?php require APP . 'view/_templates/footer.php'; ?>
