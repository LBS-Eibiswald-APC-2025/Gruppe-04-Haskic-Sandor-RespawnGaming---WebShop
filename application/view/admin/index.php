<?php
// Einbinden von Header und Feedback
require APP . 'view/_templates/header.php';
require APP . 'view/_templates/feedback.php';

// Nutzerdaten aus der Session laden
$user = Session::get('user_data');
?>

    <div class="admin-page">
        <h1>Admin-Bereich</h1>
        <div class="inner-content">
            <h3>Alle Benutzer in der Datenbank:</h3>

            <table class="table table-dark table-striped">
                <thead>
                <tr>
                    <th>UserID</th>
                    <th>Benutzername</th>
                    <th>Email</th>
                    <th>Aktiv?</th>
                    <th>Rolle</th>
                    <th>Aktionen</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($this->data['users'])) : ?>
                    <?php foreach ($this->data['users'] as $user) : ?>
                        <tr>
                            <td><?= htmlentities($user->user_id); ?></td>
                            <td><?= htmlentities($user->user_name); ?></td>
                            <td><?= htmlentities($user->email); ?></td>
                            <td><?= $user->user_active == 1 ? 'Ja' : 'Nein'; ?></td>
                            <td>
                                <form action="<?= Config::get('URL'); ?>admin/changeUserRole" method="post" style="display:inline-block;">
                                    <input type="hidden" name="user_id" value="<?= $user->user_id; ?>"/>
                                    <label>
                                        <select name="user_account_type">
                                            <option value="Kunde" <?= $user->role == 'Kunde' ? 'selected' : ''; ?>>Kunde</option>
                                            <option value="Entwickler" <?= $user->role == 'Entwickler' ? 'selected' : ''; ?>>Entwickler</option>
                                            <option value="Admin" <?= $user->role == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </label>
                                    <button type="submit" class="btn btn-primary">Rolle ändern</button>
                                </form>
                            </td>
                            <td>
                                <!-- Formular zum Sperren oder Entsperren -->
                                <form action="<?= Config::get('URL'); ?>admin/actionAccountSettings" method="post" style="display:inline-block;">
                                    <input type="hidden" name="user_id" value="<?= $user->user_id; ?>"/>

                                    <!-- 1 = gesperrt, 0 = entsperrt -->
                                    <input type="hidden" name="deactivate" value="<?= $user->user_active ? '0' : '1'; ?>"/>
                                    <button type="submit" class="btn <?= $user->user_active ? ' btn-warning' : ' btn-success'; ?>">
                                        <?= $user->user_active ? 'Sperren' : 'Entsperren'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">Keine Nutzer gefunden.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require APP . 'view/_templates/footer.php'; ?>