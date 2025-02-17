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
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/440/header.jpg',
        'title' => 'Team Fortress 2',
        'description' => 'Team-basierter Shooter mit Humor.'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/570/header.jpg',
        'title' => 'Dota 2',
        'description' => 'Strategie und Teamwork in einem MOBA.'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/730/header.jpg',
        'title' => 'CS:GO',
        'description' => 'Intensiver Taktik-Shooter im Wettkampf.'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/292030/header.jpg',
        'title' => 'The Witcher 3',
        'description' => 'Episches Rollenspiel in einer offenen Welt.'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/413150/header.jpg',
        'title' => 'Stardew Valley',
        'description' => 'Entspanne auf dem Land und baue deine Farm aus.'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/252950/header.jpg',
        'title' => 'Rocket League',
        'description' => 'Fussball trifft auf schnelle Autos.'
    ]
];

$featured_games = [
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1551360/header.jpg',
        'title' => 'Forza Horizon 5',
        'description' => 'Erkunde eine offene Welt voller Rennen und Abenteuer.',
        'price' => '59,99€'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1190460/header.jpg',
        'title' => 'Death Stranding',
        'description' => 'Erlebe epische Schlachten im Halo-Universum.',
        'price' => '39,99€'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/271590/header.jpg',
        'title' => 'GTA V',
        'description' => 'Der Klassiker unter den Open-World-Spielen.',
        'price' => '29,99€'
    ],
    (object)[
        'image' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/252950/header.jpg',
        'title' => 'Rocket League',
        'description' => 'Autos und Fußball kombiniert zu einer actiongeladenen Erfahrung.',
        'price' => 'Gratis'
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
                            <label>
                                <input type="text" name="search" class="form-control" placeholder="Suche nach Spielen">
                            </label>
                            <button type="submit" class="btn btn-primary">Suchen</button>
                        </div>
                    </form>
                </div>
            </div>
        </header>

        <!-- Game Carousel -->
        <h2 class="text-center mb-4 custom-carousel-title">Angesagt und Beliebt</h2>
        <section class="custom-carousel-container container my-5">
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

        <script>
            const featuredGames = <?php echo json_encode($featured_games); ?>;
            let currentIndex = 0;
            const slideEl = document.getElementById('carouselSlide');
            const carouselImg = document.getElementById('carouselImg');
            const carouselTitle = document.getElementById('carouselTitle');
            const carouselDescription = document.getElementById('carouselDescription');
            const carouselPrice = document.getElementById('carouselPrice');
            const carouselPrev = document.getElementById('carouselPrev');
            const carouselNext = document.getElementById('carouselNext');

            let autoSwitchInterval;

            function updateCarousel() {
                const game = featuredGames[currentIndex];
                carouselImg.src = game.image;
                carouselImg.alt = game.title;
                carouselTitle.textContent = game.title;
                carouselDescription.textContent = game.description;
                carouselPrice.textContent = game.price;
            }

            function goToSlide(newIndex, direction = 'right') {
                slideEl.classList.remove('slide-in-right', 'slide-out-left', 'slide-in-left', 'slide-out-right');

                if (direction === 'right') {
                    slideEl.classList.add('slide-out-left');
                    setTimeout(() => {
                        currentIndex = newIndex;
                        updateCarousel();
                        slideEl.classList.remove('slide-out-left');
                        slideEl.classList.add('slide-in-right');
                    }, 500);
                } else {
                    slideEl.classList.add('slide-out-right');
                    setTimeout(() => {
                        currentIndex = newIndex;
                        updateCarousel();
                        slideEl.classList.remove('slide-out-right');
                        slideEl.classList.add('slide-in-left');
                    }, 500);
                }
            }

            function startAutoSwitch() {
                if (autoSwitchInterval) clearInterval(autoSwitchInterval);
                autoSwitchInterval = setInterval(() => {
                    goToSlide((currentIndex + 1) % featuredGames.length, 'right');
                }, 7000);
            }

            carouselPrev.addEventListener('click', () => {
                const newIndex = (currentIndex - 1 + featuredGames.length) % featuredGames.length;
                goToSlide(newIndex, 'left');
                startAutoSwitch();
            });

            carouselNext.addEventListener('click', () => {
                const newIndex = (currentIndex + 1) % featuredGames.length;
                goToSlide(newIndex, 'right');
                startAutoSwitch();
            });

            updateCarousel();
            startAutoSwitch();
        </script>

        <style>
            .slide-out-left {
                animation: slideOutLeft 0.5s forwards;
            }

            .slide-in-right {
                animation: slideInRight 0.5s forwards;
            }

            .slide-out-right {
                animation: slideOutRight 0.5s forwards;
            }

            .slide-in-left {
                animation: slideInLeft 0.5s forwards;
            }

            @keyframes slideOutLeft {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(-100%); opacity: 0; }
            }

            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }

            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }

            @keyframes slideInLeft {
                from { transform: translateX(-100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        </style>

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
                                    <a href="#" class="btn btn-outline-primary">Mehr erfahren</a>
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

<?php require APP . 'view/_templates/footer.php'; ?>
