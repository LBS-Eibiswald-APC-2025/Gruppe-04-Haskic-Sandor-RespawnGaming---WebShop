<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<?php
$game = $this->data['game'] ?? null;
if (!$game) {
    echo "<p>Spiel nicht gefunden.</p>";
    return;
}
?>

<div class="game-detail-wrapper">
    <!-- Breadcrumb / Navigation -->
    <div class="breadcrumb">
        <a href="<?php echo Config::get('URL'); ?>games">Alle Spiele</a> >
        <?php echo htmlspecialchars($game['title']); ?>
    </div>

    <!-- Oberer Bereich: Cover, optionales Video -->
    <section class="top-section">
        <div class="left-column">
            <!-- Cover-Bild -->
            <img src="<?php echo htmlspecialchars($game['image']); ?>" alt="Cover" class="cover-image">

            <!-- Video (optional, startet automatisch) -->
            <?php if (!empty($game['video_url'])): ?>
                <div class="video-section mt-3">
                    <!-- Video mit autoplay, muted und playsinline -->
                    <video width="100%" controls autoplay muted playsinline>
                        <source src="<?php echo htmlspecialchars($game['video_url']); ?>" type="video/mp4">
                        Dein Browser unterstützt kein HTML5-Video.
                    </video>
                </div>
            <?php endif; ?>
        </div>

        <div class="right-column">
            <!-- Titel & Kurzbeschreibung -->
            <h1><?php echo htmlspecialchars($game['title']); ?></h1>
            <p class="short-desc">
                <?php echo nl2br(htmlspecialchars($game['description'])); ?>
            </p>

            <!-- Meta-Daten -->
            <ul class="game-meta">
                <li><strong>Genre:</strong> <?php echo htmlspecialchars($game['genre']); ?></li>
                <li><strong>Veröffentlichung:</strong> <?php echo htmlspecialchars($game['release_date']); ?></li>

                <?php if (!empty($game['discount'])): ?>
                    <li><strong>Rabatt:</strong> <?php echo htmlspecialchars($game['discount']); ?></li>
                <?php endif; ?>
            </ul>

            <!-- Kauf/Download-Button -->
            <?php if ($game['price'] == '0.00'): ?>
                <div class="play-section">
                    <span class="price-label">Kostenlos spielbar</span>
                    <form action="<?php echo Config::get('URL'); ?>cart/addToCart/<?php echo (int)$game['id']; ?>" method="post">
                        <button type="submit" class="cta-button cta-free">Spiel starten</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="buy-section">
                    <span class="price-label">
                        Preis: <?php echo htmlspecialchars($game['price']); ?>
                    </span>
                    <form action="<?php echo Config::get('URL'); ?>cart/addToCart" method="post">
                        <input type="hidden" name="game_id" value="<?php echo (int)$game['id']; ?>">
                        <button type="submit" class="cta-button cta-buy">Kaufen</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Unterer Bereich: Snippet + Beschreibung -->
    <section class="bottom-section">
        <h2>Über dieses Spiel</h2>
        <?php if (!empty($game['snippet'])): ?>
            <p><em><?php echo nl2br(htmlspecialchars($game['snippet'])); ?></em></p>
        <?php endif; ?>

        <p><?php echo nl2br(htmlspecialchars($game['description'])); ?></p>
    </section>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>
