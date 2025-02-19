<?php require APP . 'view/_templates/header.php'; ?>

<?php
// Beispiel-Spiele (kannst du aus DB etc. laden)
$games = [
    [
        'title'        => 'Avowed',
        'description'  => 'Rollenspiel, Fantasy, Einzelspieler, Egospektive',
        'price'        => '69,99€',
        'discount'     => null,
        'release_date' => '16. Feb. 2025',
        'tags'         => ['Rollenspiel', 'Fantasy', 'Einzelspieler', 'Egospektive'],
        'cover_image'  => '/public/image/gamecover1.jpg',
        'screenshots'  => [
            '/public/image/screenshot1_1.jpg',
            '/public/image/screenshot1_2.jpg',
            '/public/image/screenshot1_3.jpg'
        ]
    ],
    [
        'title'        => 'Microtopia',
        'description'  => 'Basenbau, Automation, Rohstoff-Management, Sandbox',
        'price'        => '17,99€',
        'discount'     => '-10%',
        'release_date' => '17. Feb. 2025',
        'tags'         => ['Basenbau', 'Automation', 'Rohstoff-Management', 'Sandbox'],
        'cover_image'  => '/public/image/gamecover2.jpg',
        'screenshots'  => [
            '/public/image/screenshot2_1.jpg',
            '/public/image/screenshot2_2.jpg',
            '/public/image/screenshot2_3.jpg'
        ]
    ],
    [
        'title'        => 'Lost Records: Bloom & Rage',
        'description'  => 'LGBTQ+, 90er, Bedeutsame Entscheidungen, Weibliche Hauptfigur',
        'price'        => '35,99€',
        'discount'     => '-10%',
        'release_date' => '18. Feb. 2025',
        'tags'         => ['LGBTQ+', '90er', 'Bedeutsame Entscheidungen', 'Weibl. Hauptfigur'],
        'cover_image'  => '/public/image/gamecover3.jpg',
        'screenshots'  => [
            '/public/image/screenshot3_1.jpg',
            '/public/image/screenshot3_2.jpg',
            '/public/image/screenshot3_3.jpg'
        ]
    ],
    // ... weitere Spiele ...
];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Respawn Gaming – Games</title>
    <link rel="stylesheet" href="/public/css/games/style.css">
</head>
<body>

<main class="games-page-wrapper">
    <div class="container steam-like-container my-4">
        <div class="row">
            <!-- Linke Spalte: Liste -->
            <div class="col-lg-8 col-md-7 px-0" id="gamesList">
                <nav class="games-nav d-flex">
                    <a href="#" class="active-link">Neu & angesagt</a>
                    <a href="#">Topseller</a>
                    <a href="#">Beliebt & bald verfügbar</a>
                    <a href="#">Angebote</a>
                    <a href="#">Angesagt und kostenlos</a>
                </nav>

                <ul class="games-list">
                    <?php foreach ($games as $index => $game): ?>
                        <li class="game-item" data-index="<?php echo $index; ?>">
                            <div class="game-cover">
                                <img src="<?php echo htmlspecialchars($game['cover_image']); ?>" alt="Game Cover">
                            </div>
                            <div class="game-info">
                                <h3 class="game-title"><?php echo htmlspecialchars($game['title']); ?></h3>
                                <p class="game-desc"><?php echo htmlspecialchars($game['description']); ?></p>
                            </div>
                            <div class="game-right-panel">
                                <?php if (!empty($game['discount'])): ?>
                                    <span class="discount"><?php echo htmlspecialchars($game['discount']); ?></span>
                                <?php endif; ?>
                                <span class="price"><?php echo htmlspecialchars($game['price']); ?></span>
                                <p class="release-date"><?php echo htmlspecialchars($game['release_date']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Mehr ansehen -->
                <div class="games-list-footer">
                    <a href="#" class="more-link">Mehr ansehen: <span>Neuerscheinungen</span></a>
                </div>
            </div>

            <!-- Rechte Spalte: Detail-Bereich -->
            <div class="col-lg-4 col-md-5 px-0 detail-panel" id="detailPanel">
                <div class="detail-header" id="detailHeader">Bitte mit der Maus über ein Spiel fahren</div>
                <div class="detail-screenshots" id="detailScreenshots"></div>
                <div class="detail-tags" id="detailTags"></div>
            </div>
        </div>
    </div>
</main>

<!-- Bootstrap JS (falls benötigt) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Das ausgelagerte JS für die Hover-Logik -->
<script src="/public/js/games/gameList.js"></script>

<?php require APP . 'view/_templates/footer.php'; ?>
</body>
</html>
