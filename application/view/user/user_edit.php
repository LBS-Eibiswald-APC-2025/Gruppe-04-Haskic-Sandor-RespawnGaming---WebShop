<?php
require APP . 'view/_templates/header.php';
require APP . 'view/_templates/feedback.php';

$user = Session::get('user_data');
?>

<main>
    <div class="profile-edit-box">
        <h2 class="profile-edit-title">Profil bearbeiten</h2>
        <form action="<?= Config::get('URL'); ?>saveChanges" method="post">
            <div class="profile-edit-group">
                <label for="name">Name:</label>
                <input id="name" type="text" name="name" value="<?= htmlspecialchars($user['user_name']) ?>" required>
            </div>
            <div class="profile-edit-group">
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="<?= htmlspecialchars($user['user_email']) ?>" required>
            </div>
            <div class="profile-edit-group">
                <label for="password">Passwort (leer lassen, um nicht zu ändern):</label>
                <input id="password" type="password" name="password" placeholder="Neues Passwort">
            </div>
            <div class="profile-edit-group">
                <label for="location">Standort:</label>
                <input id="location" type="text" name="location" value="<?= htmlspecialchars($user['user_location']) ?>">
            </div>
            <div class="profile-edit-group">
                <label for="about">Über mich:</label>
                <textarea id="about" name="about"><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="profile-edit-btn">Änderungen speichern</button>
        </form>
        <div class="profile-edit-link">
            <a href="/user">Zurück zum Profil</a>
        </div>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
