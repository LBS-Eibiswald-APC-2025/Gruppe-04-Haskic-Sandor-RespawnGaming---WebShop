<?php require APP . 'view/_templates/header.php'; ?>

<?php
    if (!isset($this->data['games'])) {
        $this->data['games'] = [];
    }
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
                    <?php foreach ($this->data['games'] as $game): ?>
                        <li class="game-item" data-index="<?php echo htmlspecialchars($game->id); ?>">
                            <div class="game-cover">
                                <img src="<?php echo htmlspecialchars($game->image); ?>" alt="Game Cover">
                            </div>
                            <div class="game-info">
                                <h3 class="game-title"><?php echo htmlspecialchars($game->title); ?></h3>
                                <p class="game-desc"><?php echo htmlspecialchars($game->description); ?></p>
                            </div>
                            <div class="game-right-panel">
                                <?php if (!empty($game->discount)): ?>
                                    <span class="discount"><?php echo htmlspecialchars($game->discount); ?></span>
                                <?php endif; ?>
                                <span class="price"><?php echo htmlspecialchars($game->price); ?></span>
                                <p class="release-date"><?php echo htmlspecialchars($game->release_date); ?></p>
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
