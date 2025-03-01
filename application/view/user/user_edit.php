<?php

require APP . 'view/_templates/header.php';
require APP . 'view/_templates/feedback.php';

$user = Session::get('user_data');
?>

<main>
    <div class="container-ue">
        <h2>Profil bearbeiten</h2>

        <form action="<?php Config::get('URL') ?>saveChanges" method="post">
            <label>Name:</label>
            <input type="text" name="name" value="<?= $user['user_name'] ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= $user['user_email'] ?>" required>

            <label>Passwort (leer lassen, um nicht zu ändern):</label>
            <input type="password" name="password" placeholder="Neues Passwort">

            <label>Standort:</label>
            <input type="text" name="location" value="<?= $user['user_location'] ?>">

            <label>Über mich:</label>
            <textarea name="about"><?= $user['about'] ?? '' ?></textarea>

            <button type="submit">Änderungen speichern</button>
        </form>
        <a href="/user">Zurück zum Profil</a>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
