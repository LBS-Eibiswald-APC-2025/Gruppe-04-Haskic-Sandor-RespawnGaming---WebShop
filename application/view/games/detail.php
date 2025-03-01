<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<div class="game-detail-wrapper">

    <!-- BREADCRUMB / NAV -->
    <div class="breadcrumb">
        <a href="<?= Config::get('URL'); ?>games">Alle Spiele</a>
        >
        <?= htmlspecialchars($this->data['game']->title); ?>
    </div>

    <?php
    $game = $this->data['game'];
    ?>

    <!-- OBERER BEREICH: TRAILER LINKS, INFOS RECHTS -->
    <section class="top-section">
        <div class="left-column">
            <!-- Cover-Bild -->
            <img src="<?= htmlspecialchars($game->image); ?>" alt="Cover" class="cover-image">
        </div>

        <div class="right-column">
            <!-- Titel & Kurzbeschreibung -->
            <h1><?= htmlspecialchars($game->title); ?></h1>
            <p class="short-desc">
                <?= nl2br(htmlspecialchars($game->description)); ?>
            </p>

            <!-- Entwickler, Genre, Release -->
            <ul class="game-meta">
                <li><strong>Genre:</strong> <?= htmlspecialchars($game->genre); ?></li>
                <li><strong>Veröffentlichung:</strong> <?= htmlspecialchars($game->release_date); ?></li>
                <li><strong>Entwickler-ID:</strong> <?= htmlspecialchars($game->developer_id); ?></li>
            </ul>

            <!-- Lizenz-Info -->
            <?php if ($game->license_required == 1): ?>
                <p class="license-warning">⚠ Dieses Spiel erfordert eine spezielle Lizenz.</p>
            <?php endif; ?>

            <!-- Kauf/Download-Button -->
            <?php if ($game->price == 0): ?>
                <!-- Spiel ist kostenlos -->
                <div class="play-section">
                    <span class="price-label">Kostenlos spielbar</span>
                    <form action="<?= Config::get('URL'); ?>cart/addToCart/<?= (int)$game->id; ?>" method="post">
                        <button type="submit" class="cta-button cta-free">Spiel starten</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Spiel kostet etwas -->
                <div class="buy-section">
                    <span class="price-label">
                        Preis: €<?= number_format($game->price, 2, ',', '.'); ?>
                    </span>
                    <form action="<?= Config::get('URL'); ?>cart/addToCart/<?= (int)$game->id; ?>" method="post">
                        <button type="submit" class="cta-button cta-buy">Kaufen</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- OPTIONAL: WEITERE INFOS -->
    <section class="bottom-section">
        <h2>Über dieses Spiel</h2>
        <p>Hier könnte nicht deine Werbung stehen.</p>
        <iframe src="https://media.tenor.com/X-cdTIjltDEAAAAM/meme-ads.gif" style="height: 30vh"></iframe>
    </section>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>