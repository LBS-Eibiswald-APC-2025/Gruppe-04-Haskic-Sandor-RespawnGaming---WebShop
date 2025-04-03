<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

    <div class="chat-room">
        <h1><?= htmlspecialchars($this->data['roomDetails']['name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($this->data['roomDetails']['description'], ENT_QUOTES, 'UTF-8') ?></p>

        <div class="chat-messages" id="chatMessages">
            <?php foreach (array_reverse($this->data['messages']) as $message): ?>
                <div class="message <?= $message['is_own_message'] ? 'own-message' : '' ?>" data-id="<?= (int)$message['id'] ?>">
                    <img src="<?= htmlspecialchars($message['avatar'] ?? '/avatars/default.jpg', ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="user-avatar">
                    <div class="message-content">
                        <strong><?= htmlspecialchars($message['user_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="timestamp"><?= htmlspecialchars(date('d.m.y - H:i:s', strtotime($message['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                        <p class="message-text"><?= htmlspecialchars($message['message_text'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="typingStatus" class="typing-status"></div>

        <form action="<?= htmlspecialchars(Config::get('URL'), ENT_QUOTES, 'UTF-8') ?>community/sendMessage" method="POST" class="message-form">
            <input type="hidden" name="room_id" value="<?= (int)$this->data['roomDetails']['id'] ?>">
            <textarea name="message" placeholder="Deine Nachricht..." required></textarea>
            <button type="submit" class="btn btn-send">Senden</button>
        </form>
    </div>

<?php require APP . 'view/_templates/footer.php'; ?>