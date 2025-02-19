<?php
// Einbinden von Header und Feedback
require APP . 'view/_templates/header.php';
require APP . 'view/_templates/feedback.php';

// Nutzerdaten aus der Session laden
$user = Session::get('user_data');
?>

<!-- Profilcontainer -->
<div class="profile-container">
    <!-- Header-Bereich mit Cover-Bild und Avatar -->
    <div class="profile-header">
        <!-- Cover-Bild (falls kein eigenes Bild, wird ein Standardbild genutzt) -->
        <img class="cover" src="<?= $user['cover_image'] ?? 'path/to/default_cover.jpg'; ?>" alt="Cover Image">
        <!-- Avatar, der über den unteren Bereich des Covers ragt -->
        <div class="avatar">
            <img src="<?= $user['avatar'] ?? 'path/to/default_avatar.jpg'; ?>" alt="User Avatar">
        </div>
    </div>

    <!-- Informationsbereich, der unterhalb des Headers positioniert ist -->
    <div class="profile-info">
        <h2 class="username"><?= $user['user_name']; ?></h2>
        <p class="status">Status: <?= $user['status'] ?? 'Offline'; ?></p>
        <p class="location"><?= !empty($user['location']) ? 'Location: ' . $user['location'] : ''; ?></p>
        <p class="member-since">Member since: <?= $user['member_since'] ?? 'N/A'; ?></p>

        <?php if (!empty($user['about'])): ?>
            <div class="about">
                <p><?= $user['about']; ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>
