<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<?php
// Hier erwarten wir, dass der Controller uns ein Array $games liefert
$games = $this->data['games'] ?? [];
?>
<main class="games-page-wrapper">
    <div class="container steam-like-container my-4">
        <div class="row">
            <!-- Linke Spalte: Spieleliste -->
            <div class="col-lg-8 col-md-7 px-0" id="gamesList">
                <nav class="games-nav d-flex">
                    <a href="#" data-filter="neu" class="active-link">Neu & angesagt</a>
                    <a href="#" data-filter="topseller">Topseller</a>
                    <a href="#" data-filter="upcoming">Beliebt & bald verfügbar</a>
                    <a href="#" data-filter="offers">Angebote</a>
                </nav>

                <ul class="games-list" id="gamesListItems">
                    <?php foreach ($games as $game): ?>
                        <li class="game-item d-flex justify-content-between align-items-center"
                            data-id="<?php echo htmlspecialchars($game['id']); ?>"
                            data-category="<?php echo htmlspecialchars($game['category'] ?? 'neu'); ?>">

                            <!-- Linke Seite: Cover + Infos -->
                            <div class="d-flex">
                                <div class="game-cover me-3">
                                    <img src="<?php echo htmlspecialchars($game['image']); ?>" alt="Game Cover" />
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

                            <!-- Rechte Seite: Preis, Rabatt & Warenkorb -->
                            <div class="game-right-panel text-end">
                                <?php if (!empty($game['discount'])): ?>
                                    <span class="discount me-2"><?php echo htmlspecialchars($game['discount']); ?></span>
                                <?php endif; ?>

                                <span class="price me-3">
                                    <?php echo htmlspecialchars($game['price']); ?>
                                </span>

                                <form action="<?php echo Config::get('URL'); ?>cart/addToCart" method="post" class="d-inline">
                                    <input type="hidden" name="game_id" value="<?php echo (int)$game['id']; ?>">
                                    <button type="submit" class="btn btn-success">In den Warenkorb</button>
                                </form>

                                <p class="release-date mt-2">
                                    <?php echo htmlspecialchars($game['release_date']); ?>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="games-list-footer">
                    <a href="#" class="more-link">Mehr ansehen: <span>Neuerscheinungen</span></a>
                </div>
            </div>

            <!-- Rechte Spalte: Detail-Panel (Hover-Infos) -->
            <div class="col-lg-4 col-md-5 px-0 detail-panel" id="detailPanel">
                <div class="detail-header" id="detailHeader">Bitte mit der Maus über ein Spiel fahren</div>
                <div class="detail-screenshots" id="detailScreenshots"></div>
                <div class="detail-tags" id="detailTags"></div>
                <div class="detail-rating" id="detailRating"></div>
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
