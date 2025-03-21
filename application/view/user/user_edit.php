<?php
// Einbinden des Headers mit der CSS-Verknüpfung
require APP . 'view/_templates/header.php';
require APP . 'view/_templates/feedback.php';

// Benutzer-Daten aus der Session abrufen
$user = Session::get('user_data');
?>

<main>
    <div class="profile-edit-box">
        <!-- Überschrift mit Styling aus SCSS -->
        <h2 class="profile-edit-title">Profil bearbeiten</h2>

        <form action="<?php Config::get('URL') ?>saveChanges" method="post">
            <div class="profile-edit-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= $user['user_name'] ?>" required>
            </div>
            <div class="profile-edit-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= $user['user_email'] ?>" required>
            </div>
            <div class="profile-edit-group">
                <label for="password">Passwort (leer lassen, um nicht zu ändern):</label>
                <input type="password" id="password" name="password" placeholder="Neues Passwort">
            </div>
            <div class="profile-edit-group">
                <label for="location">Standort:</label>
                <input type="text" id="location" name="location" value="<?= $user['user_location'] ?>">
            </div>
            <div class="profile-edit-group">
                <label for="about">Über mich:</label>
                <textarea id="about" name="about"><?= $user['about'] ?? '' ?></textarea>
            </div>
            <!-- Button zum Speichern der Änderungen -->
            <button type="submit" class="profile-edit-btn">Änderungen speichern</button>
        </form>
        <div class="profile-edit-link">
            <!-- Link zurück zum Profil -->
            <a href="/user">Zurück zum Profil</a>
        </div>
    </div>
</main>

<?php
// Footer einbinden
require APP . 'view/_templates/footer.php';
?>
