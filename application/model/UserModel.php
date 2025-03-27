<?php

class UserModel
{
    /**
     * Gets an array of all users (for admin or public listings).
     */
    public static function getPublicProfilesOfAllUsers($filter, $filter2): array
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name, email, user_active, role FROM users";
        $params = [];

        if (!empty($filter)) {
            $sql .= " WHERE role = :role";
            $params[':role'] = $filter;
        } elseif (!empty($filter2)) {
            $sql .= " WHERE (user_name LIKE :filter2 
                 OR email LIKE :filter2 OR user_id LIKE :filter2)";
            $params[':filter2'] = '%' . $filter2 . '%';
        }

        $query = $database->prepare($sql);
        $query->execute($params);

        $all_users_profiles = array();

        foreach ($query->fetchAll() as $user) {
            array_walk_recursive($user, 'Filter::XSSFilter');

            $all_users_profiles[$user->user_id] = new stdClass();
            $all_users_profiles[$user->user_id]->user_id = $user->user_id;
            $all_users_profiles[$user->user_id]->user_name = $user->user_name;
            $all_users_profiles[$user->user_id]->email = $user->email;
            $all_users_profiles[$user->user_id]->user_active = $user->user_active;
            $all_users_profiles[$user->user_id]->role = $user->role;
        }

        return $all_users_profiles;
    }

    /**
     * Gets a user's profile data, by user_id
     */
    public static function getPublicProfileOfUser($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name, email, user_active
                  FROM users
                 WHERE user_id = :user_id
                 LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id]);

        $user = $query->fetch();

        if ($query->rowCount() == 1) {
            if (Config::get('USE_GRAVATAR')) {
                $user->user_avatar_link = AvatarModel::getGravatarLinkByEmail($user->email);
            }
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_USER_DOES_NOT_EXIST'));
        }

        array_walk_recursive($user, 'Filter::XSSFilter');

        return $user;
    }

    public static function getUserDataByUserNameOrEmail($user_name_or_email)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name, email
                  FROM users
                 WHERE (user_name = :user_name_or_email
                    OR email = :user_name_or_email)
                 LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':user_name_or_email' => $user_name_or_email]);

        return $query->fetch();
    }

    public static function doesUsernameAlreadyExist($user_name): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT user_id FROM users
                                       WHERE user_name = :user_name
                                       LIMIT 1");
        $query->execute([':user_name' => $user_name]);
        return ($query->rowCount() !== 0);
    }

    public static function doesEmailAlreadyExist($user_email): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT user_id FROM users
                                       WHERE email = :user_email
                                       LIMIT 1");
        $query->execute([':user_email' => $user_email]);
        return ($query->rowCount() !== 0);
    }

    public static function saveNewUserName($user_id, $new_user_name): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users
                                        SET user_name = :user_name
                                      WHERE user_id = :user_id
                                      LIMIT 1");
        $query->execute([
            ':user_name' => $new_user_name,
            ':user_id' => $user_id
        ]);
        return ($query->rowCount() === 1);
    }

    public static function saveNewEmailAddress($user_id, $new_user_email): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users
                                        SET email = :user_email
                                      WHERE user_id = :user_id
                                      LIMIT 1");
        $query->execute([
            ':user_email' => $new_user_email,
            ':user_id' => $user_id
        ]);
        return ($query->rowCount() === 1);
    }

    public static function editUserName($new_user_name): bool
    {
        if ($new_user_name == Session::get('user_name')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_SAME_AS_OLD_ONE'));
            return false;
        }

        if (!preg_match("/^[a-zA-Z0-9]{2,64}$/", $new_user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        $new_user_name = substr(strip_tags($new_user_name), 0, 64);

        if (self::doesUsernameAlreadyExist($new_user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_ALREADY_TAKEN'));
            return false;
        }

        $status_of_action = self::saveNewUserName(Session::get('user_id'), $new_user_name);
        if ($status_of_action) {
            Session::set('user_name', $new_user_name);
            Session::add('feedback_positive', Text::get('FEEDBACK_USERNAME_CHANGE_SUCCESSFUL'));
            return true;
        }
        Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
        return false;
    }

    public static function editUserEmail($new_user_email): bool
    {
        if (empty($new_user_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_FIELD_EMPTY'));
            return false;
        }

        if ($new_user_email == Session::get('user_email')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_SAME_AS_OLD_ONE'));
            return false;
        }

        if (!filter_var($new_user_email, FILTER_VALIDATE_EMAIL)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        $new_user_email = substr(strip_tags($new_user_email), 0, 254);

        if (self::doesEmailAlreadyExist($new_user_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USER_EMAIL_ALREADY_TAKEN'));
            return false;
        }

        if (self::saveNewEmailAddress(Session::get('user_id'), $new_user_email)) {
            Session::set('user_email', $new_user_email);
            Session::set('user_gravatar_image_url', AvatarModel::getGravatarLinkByEmail($new_user_email));
            Session::add('feedback_positive', Text::get('FEEDBACK_EMAIL_CHANGE_SUCCESSFUL'));
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
        return false;
    }

    public static function getUserIdByUsername($user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id
                  FROM users
                 WHERE user_name = :user_name
                 LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':user_name' => $user_name]);
        $result = $query->fetch();
        return $result ? $result->user_id : null;
    }

    public static function getUserDataByUsername($user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name, email, password_hash, user_active, role,
                       user_failed_logins, user_last_failed_login
                  FROM users
                 WHERE (user_name = :user_name OR email = :user_name)
                 LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':user_name' => $user_name]);

        return $query->fetch();
    }

    public static function getUserDataByUserID(int $id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT user_id, user_name, email, password_hash, user_active, role,
                       user_failed_logins, user_last_failed_login
                  FROM users
                 WHERE user_id = :user_id
                 LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $id]);
        return $query->fetch();
    }

    public static function getUserDataByUserIdAndToken($user_id, $token)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name, email, password_hash, user_active, role,
                       user_failed_logins, user_last_failed_login
                  FROM users
                 WHERE user_id = :user_id
                   AND user_remember_me_token = :user_remember_me_token
                   AND user_remember_me_token IS NOT NULL
                 LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([
            ':user_id' => $user_id,
            ':user_remember_me_token' => $token
        ]);

        return $query->fetch();
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function saveUserEdit($data): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $updates = [];
        $params = [':user_id' => Session::get('user_id')];
        $changed_data = [];

        if ($data['name'] != Session::get('user_name')) {
            $updates[] = 'user_name = :user_name';
            $params[':user_name'] = $data['name'];
            $changed_data[] = 'Benutzername';
        }
        if (!empty($data['password'])) {
            $updates[] = 'password_hash = :password_hash';
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $changed_data[] = 'Passwort';
        }
        if ($data['email'] != Session::get('user_email')) {
            $updates[] = 'email = :email';
            $params[':email'] = $data['email'];
            $changed_data[] = 'E-Mail-Adresse';
        }
        if ($data['location'] != Session::get('user_location')) {
            $updates[] = 'location = :location';
            $params[':location'] = $data['location'];
            $changed_data[] = 'Standort';
        }
        if ($data['about'] != Session::get('user_about')) {
            $updates[] = 'about = :about';
            $params[':about'] = $data['about'];
            $changed_data[] = 'Über mich';
        }

        if (empty($updates)) {
            return true;
        }

        $sql = "UPDATE users 
                SET " . implode(', ', $updates) . "
                WHERE user_id = :user_id 
                LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute($params);

        if ($query->rowCount() == 1) {
            if (!self::sendUpdateMail($data['name'], Session::get('user_email'), $changed_data)) {
                Session::add('feedback_negative', 'Fehler bei der Mail-Übermittlung');
                return false;
            }

            foreach ($params as $key => $value) {
                $sessionKey = str_replace([':'], '', $key);
                if ($sessionKey != 'user_id' && $sessionKey != 'password_hash') {
                    $prefix = ($sessionKey === 'about') ? '' : 'user_';
                    Session::set('user_data', $value, $prefix . $sessionKey);
                }
            }
            Session::add('feedback_positive', Text::get('FEEDBACK_PROFILE_UPDATE_SUCCESSFUL'));
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_PROFILE_UPDATE_FAILED'));
        return false;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function sendUpdateMail($name, $user_email, $changed_data): bool
    {
        $body = '
        <html lang="de">
                <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <style>
                        @font-face {
                            font-family: "Ghost";
                            src: url("https://respawngaming.at/fonts/Ghost/Ghost.woff2") format("woff2");                            
                        }
                        body { font-family: "Ghost", sans-serif; margin: 0; padding: 0; background-color: #f2f4f8; }
                        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
                        .header { background: linear-gradient(135deg, #721e1e, #982a2a); padding: 20px; text-align: center; }
                        .header img { max-width: 150px; margin-bottom: 10px; border-radius: 50%; }
                        .header h1 { margin: 0; color: #fff; font-size: 26px; }
                        .content { padding: 20px; color: #333; line-height: 1.6; }
                        .content h1 { font-size: 22px; margin-bottom: 10px; color: #333; }
                        .content h2 { font-size: 20px; margin-bottom: 8px; color: #333; }
                        .content h3 { font-size: 18px; margin-bottom: 6px; color: #333; }
                        .content p { font-size: 16px; margin-bottom: 12px; }
                        .content ul, .content ol { margin: 12px 0; padding-left: 20px; }
                        .content li { margin-bottom: 8px; }
                        .content .signature { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; font-style: italic; color: #666; }
                        .footer { background: #f2f2f2; padding: 15px; text-align: center; font-size: 12px; color: #777; }
                        .footer a { color: #2a5298; text-decoration: none; }
                        .footer a:hover { text-decoration: underline; }
                    </style>
                    <title>Respawn Gaming</title>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <img src="https://respawngaming.at/image/RG_MainLogo.png" alt="Respawn Gaming Logo">
                            <h1>Respawn Gaming</h1>
                        </div>
                        <div class="content">
                            <h1>Konto Änderungen</h1>
                            <p>Hallo ' . $name . ',</p>
                            <p>Ihre folgenden Daten wurden gerade geändert:</p>
                            <ul>
                                <li>' . implode('</li><li>', $changed_data) . '</li>
                            </ul>
                            <hr>
                            <p>Falls diese Änderungen nicht nach deinen Wünschen entstanden sind, wenden dich bitte an unseren Support unter <a href="mailto:support@respawngaming.at">Support</a></p>
                            <div class="signature">
                                <p>Freundliche Grüße</p>
                                <p>Ihr Respawn Gaming Team</p>
                            </div>
                        </div>
                        <div class="footer">
                            <p>© 2025 Respawn Gaming. Alle Rechte vorbehalten.</p>
                            <p><a href="https://www.respawngaming.at">www.respawngaming.at</a></p>
                        </div>
                    </div>
                </body>
            </html>
    ';

        $mail = new Mail();
        return $mail->sendMail($user_email, "no-reply@respawngaming.at", "No-Reply", "(WICHTIG) Konto Änderungen", $body);
    }

    public static function updatePassword($user_id, string $newPasswordHash): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE users 
            SET password_hash = :newPasswordHash
            WHERE user_id = :user_id
            LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([
            ':newPasswordHash' => $newPasswordHash,
            ':user_id' => $user_id
        ]);

        // Wenn genau eine Zeile geändert wurde, war es erfolgreich
        return ($query->rowCount() === 1);
    }

    public function findByResetToken(mixed $token)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        // Hier prüfen wir, ob der Token existiert und noch nicht abgelaufen ist.
        // Da user_password_reset_timestamp als UNIX-Timestamp gespeichert wird, vergleichen wir ihn mit UNIX_TIMESTAMP().
        $sql = "SELECT * FROM users 
            WHERE password_reset_token = :token 
              AND user_password_reset_timestamp > UNIX_TIMESTAMP()
            LIMIT 1";
        $stmt = $database->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function removeResetToken($id): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        // Setzt den Token und das Ablaufdatum zurück, damit er nicht erneut verwendet werden kann.
        $sql = "UPDATE users 
            SET password_reset_token = NULL, 
                user_password_reset_timestamp = NULL 
            WHERE user_id = :id";
        $stmt = $database->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }


    public function findByEmail(mixed $email)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $database->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveResetToken($id, string $token, int $expiry)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        // Hier wird der Token in der Spalte "password_reset_token" und das Ablaufdatum
        // (als UNIX-Timestamp) in "user_password_reset_timestamp" gespeichert.
        $sql = "UPDATE users SET password_reset_token = :token, user_password_reset_timestamp = :expiry WHERE user_id = :id";
        $stmt = $this->$database->prepare($sql);
        return $stmt->execute([
            ':token' => $token,
            ':expiry' => $expiry,
            ':id' => $id,
        ]);
    }

    public static function getGamesByUserId($id): array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT g.*
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN games g ON g.id = oi.game_id
                WHERE o.user_id = :id";
        $stmt = $database->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function uploadAvatar($file): array
    {
        $maxSize = 2 * 1024 * 1024; // 2MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Die Datei ist zu groß (max. 2MB)'];
        }

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Ungültiges Dateiformat'];
        }

        // Altes Avatar-Bild löschen
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT avatar FROM users WHERE user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => Session::get('user_id')]);
        $oldAvatar = $query->fetchColumn();

        if ($oldAvatar && file_exists(substr($oldAvatar, 1))) {
            unlink(substr($oldAvatar, 1));
        }

        $fileName = 'avatar_' . Session::get('user_id') . '.webp';
        $uploadPath = 'uploads/avatars/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $sql = "UPDATE users SET avatar = :avatar WHERE user_id = :user_id";
            $query = $database->prepare($sql);
            $query->execute([
                ':avatar' => '/' . $uploadPath,
                ':user_id' => Session::get('user_id')
            ]);

            Session::set('avatar', '/' . $uploadPath);
            Session::set('user_data', '/' . $uploadPath, 'avatar');

            return [
                'success' => true,
                'path' => '/' . $uploadPath
            ];
        }

        return ['success' => false, 'message' => 'Fehler beim Hochladen'];
    }

    public static function uploadBanner($file): array
    {
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Die Datei ist zu groß (max. 5MB)'];
        }

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Ungültiges Dateiformat'];
        }

        // Altes Banner-Bild löschen
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT banner FROM users WHERE user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => Session::get('user_id')]);
        $oldBanner = $query->fetchColumn();

        if ($oldBanner && file_exists(substr($oldBanner, 1))) {
            unlink(substr($oldBanner, 1));
        }

        $fileName = 'banner_' . Session::get('user_id') . '.webp';
        $uploadPath = 'uploads/banners/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $sql = "UPDATE users SET banner = :banner WHERE user_id = :user_id";
            $query = $database->prepare($sql);
            $query->execute([
                ':banner' => '/' . $uploadPath,
                ':user_id' => Session::get('user_id')
            ]);

            Session::set('banner', '/' . $uploadPath);
            Session::set('user_data', '/' . $uploadPath, 'banner');

            return [
                'success' => true,
                'path' => '/' . $uploadPath
            ];
        }

        return ['success' => false, 'message' => 'Fehler beim Hochladen'];
    }
}
