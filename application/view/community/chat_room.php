<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

    <div class="chat-room">
        <h1><?= htmlspecialchars($this->data['roomDetails']['name']) ?></h1>
        <p><?= htmlspecialchars($this->data['roomDetails']['description']) ?></p>

        <div class="chat-messages" id="chatMessages">
            <?php foreach (array_reverse($this->data['messages']) as $message): ?>
                <div class="message <?= $message['is_own_message'] ? 'own-message' : '' ?>">
                    <img src="<?= htmlspecialchars($message['avatar'] ?? '/avatars/default.jpg') ?>" alt="Avatar" class="user-avatar">
                    <div class="message-content">
                        <strong><?= htmlspecialchars($message['user_name']) ?></strong>
                        <span class="timestamp"><?= date('d.m.y - H:i:s', strtotime($message['created_at'])) ?></span>
                        <p><?= htmlspecialchars($message['message_text']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="typingStatus" class="typing-status"></div>

        <form action="<?= Config::get('URL') ?>community/sendMessage" method="POST" class="message-form">
            <input type="hidden" name="room_id" value="<?= $this->data['roomDetails']['id'] ?>">
            <textarea name="message" placeholder="Deine Nachricht..." required></textarea>
            <button type="submit" class="btn btn-send">Senden</button>
        </form>
    </div>

<?php require APP . 'view/_templates/footer.php'; ?>