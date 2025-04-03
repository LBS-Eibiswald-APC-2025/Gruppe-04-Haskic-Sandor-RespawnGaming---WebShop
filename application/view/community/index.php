<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

    <div class="community-page">
        <h1>Gaming Community Chat</h1>

        <div class="chat-rooms-overview">
            <?php foreach ($this->data['chatRooms'] as $room): ?>
                <div class="chat-room-card">
                    <h3><?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($room['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p>Spieltyp: <?= htmlspecialchars($room['game_type'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a href="<?= htmlspecialchars(Config::get('URL'), ENT_QUOTES, 'UTF-8') ?>community/chatRoom/<?= (int)$room['id'] ?>" class="btn btn-primary">Raum betreten</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php require APP . 'view/_templates/footer.php'; ?>