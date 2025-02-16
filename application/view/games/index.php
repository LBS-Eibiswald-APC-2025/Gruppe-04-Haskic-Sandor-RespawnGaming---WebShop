<?php require APP . 'view/_templates/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Alle Spiele</h2>
    <div class="row">
        <?php if (!empty($games)) : ?>
            <?php foreach ($games as $game) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card game-card">
                        <img src="<?php echo Config::get('URL') . 'assets/images/' . $game->image; ?>"
                             class="card-img-top" alt="<?php echo htmlspecialchars($game->title); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($game->title); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($game->description); ?></p>
                            <a href="<?php echo Config::get('URL') . 'games/details/' . $game->id; ?>"
                               class="btn btn-primary">Mehr erfahren</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-center text-danger">Keine Spiele gefunden.</p>
        <?php endif; ?>
    </div>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>
