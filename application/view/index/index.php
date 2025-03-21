<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<?php
$AllGames = GamesModel::getAllGames(5);
?>

<main>
    <div class="inner">
        <!-- Hero-Section -->
        <header class="hero-section text-center">
            <div class="container">
                <h1>Respawn Gaming</h1>
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
                <?php if (!empty($AllGames)): ?>
                    <?php foreach ($AllGames as $game): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card game-card">
                                <img src="<?php echo htmlspecialchars($game['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($game['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($game['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($game['description']); ?></p>

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
