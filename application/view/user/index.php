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
        <?php $random = rand(1, 3); ?>
        <img class="cover" src="<?= $user['cover_image'] ?? '/image/main/cover_'.$random.'.webp'; ?>" alt="Cover Image">
        <!-- Avatar, der über den unteren Bereich des Covers ragt -->
        <div class="avatar">
            <img src="<?= $user['avatar'] ?? 'https://avatar.iran.liara.run/public'; ?>" alt="User Avatar">
        </div>
    </div>

    <span class="icon-edit"><i class="fa-solid fa-pencil"></i></span>

    <span class="icon-library"><i class="fa-solid fa-bookmark"></i></span>

    <div class="profile-info">
        <h2 class="username"><?= $user['user_name']; ?></h2>
        <p class="location"><?= !empty($user['user_location']) ? 'Standort: ' . $user['user_location'] : 'Location: N/A'; ?></p>
        <p class="member-since">Mitglied seit: <?= date('d.m.Y', strtotime($user['user_member_since'])) ?? ''; ?></p>

        <hr>

        <div class="about">
            <p><?= $user['about'] ?? ''; ?></p>
        </div>

        <hr>

        <div class="library" id="library">
            <h3>Meine Bibliothek</h3>
            <div class="games">
                <p>
                    <?php
                    $games = UserModel::getGamesByUserId($user['user_id']);

                    if ($games) {
                        $gameCount = count($games);
                        $gameCounts = array_count_values(array_map(function($game) {
                            return $game->title;
                        }, $games));

                        echo "<div class='game-list'>";
                        echo "<p class='game-count'>{$gameCount} " . ($gameCount === 1 ? "Spiel" : "Spiele") . " in der Bibliothek</p>";
                        echo "<ul>";
                        foreach ($gameCounts as $title => $count) {
                            $displayCount = $count > 1 ? " ({$count}x)" : "";
                            echo "<li>{$title}{$displayCount}</li>";
                        }
                        echo "</ul>";
                        echo "</div>";
                    } else {
                        echo "<p class='no-games'>Keine Spiele in der Bibliothek</p>";
                    }
                    ?>
                </p>
            </div>
        </div>

        <hr>

    </div>
</div>

<script src="/public/js/user/index.js"></script>

<?php require APP . 'view/_templates/footer.php'; ?>
