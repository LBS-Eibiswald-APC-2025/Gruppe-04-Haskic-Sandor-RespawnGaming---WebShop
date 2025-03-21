<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<?php
// Hier erwarten wir, dass der Controller uns ein Array $games liefert
$games = $this->data['games'] ?? [];
$isSearch = $this->data['method'] ?? '';
?>
<main class="games-page-wrapper">
    <div class="container-tab my-4">
        <div class="row">
            <!-- Linke Spalte: Spieleliste -->
            <div class="col-lg-8 col-md-7 px-0" id="gamesList">
                <nav class="games-nav d-flex">
                    <a href="#" data-category="1">Topseller</a>
                    <a href="#" data-category="2">Beliebt &amp; bald verfügbar</a>
                    <a href="#" data-category="3">Angebote</a>
                </nav>

                <ul class="games-list" id="gamesListItems">
                    <?php foreach ($games

                                   as $game): ?>
                        <li class="game-item justify-content-between align-items-center"
                            data-id="<?php echo htmlspecialchars($game['id']); ?>"
                            data-category="<?php echo htmlspecialchars($game['category'] ?? 'neu'); ?>">

                            <!-- Linke Seite: Cover + Infos -->
                            <div class="d-flex">
                                <div class="game-cover me-3">
                                    <img src="<?php echo htmlspecialchars($game['image']); ?>" alt="Game Cover"/>
                                </div>
                                <div class="game-info">
                                    <h3 class="game-title"><?php echo htmlspecialchars($game['title']); ?></h3>
                                    <p class="game-desc"><?php echo htmlspecialchars($game['description']); ?></p>

                                    <?php if (!empty($game['snippet'])): ?>
                                        <p class="game-snippet">
                                            <?php echo nl2br(htmlspecialchars($game['snippet'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Rechte Seite: Preis in € und wenn 0 € dann soll "Kostenlos" stehen-->
                            <div class="game-price">
                                <?php if ($game['price'] == '0.00'): ?>
                                    <div class="game-price">Kostenlos</div>
                                <?php else: ?>
                                    <div class="game-price"><?php echo htmlspecialchars($game['price']); ?> €</div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Rechte Spalte: Detail-Panel (Hover-Infos) -->
            <div class="col-lg-4 col-md-5 detail-panel" id="detailPanel">
                <div id="detailScreenshots"></div>
                <div id="detailTags"></div>
                <div id="detailRating"></div>
                <div id="detailMisc"></div>
            </div>
        </div>
    </div>
</main>

<!-- Übergabe der Spiele-Daten ans JS (für Hover-Effekte & Filter) -->
<script>
    window.gamesData = <?php echo json_encode($games, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
</script>

<!-- Bootstrap + JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/public/js/games/gameList.js"></script>

<?php require APP . 'view/_templates/footer.php'; ?>
