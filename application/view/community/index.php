<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

    <div class="community-page">
        <h1>Gaming Community Chat</h1>

        <div class="chat-rooms-overview">
            <?php foreach ($this->data['chatRooms'] as $room): ?>
                <div class="chat-room-card">
                    <h3><?= htmlspecialchars($room['name']) ?></h3>
                    <p><?= htmlspecialchars($room['description']) ?></p>
                    <p>Spieltyp: <?= htmlspecialchars($room['game_type']) ?></p>
                    <a href="<?= Config::get('URL') ?>community/chatRoom/<?= $room['id'] ?>" class="btn btn-primary">Raum betreten</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php require APP . 'view/_templates/footer.php'; ?>