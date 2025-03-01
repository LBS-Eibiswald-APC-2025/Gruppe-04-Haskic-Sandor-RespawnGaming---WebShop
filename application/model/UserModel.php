<?php

class UserModel
{
    /**
     * Gets an array of all users (for admin or public listings).
     */
    public static function getPublicProfilesOfAllUsers(): array
    {

        $database = DatabaseFactory::getFactory()->getConnection();

        // Hier 'role' ergänzt:
        $sql = "SELECT user_id, user_name, email, user_active, role
                  FROM users";
        $query = $database->prepare($sql);
        $query->execute();

        $all_users_profiles = array();

        foreach ($query->fetchAll() as $user) {
            array_walk_recursive($user, 'Filter::XSSFilter');

            $all_users_profiles[$user->user_id] = new stdClass();
            $all_users_profiles[$user->user_id]->user_id     = $user->user_id;
            $all_users_profiles[$user->user_id]->user_name   = $user->user_name;
            $all_users_profiles[$user->user_id]->email       = $user->email;
            $all_users_profiles[$user->user_id]->user_active = $user->user_active;
            $all_users_profiles[$user->user_id]->role        = $user->role;
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

    public static function doesUsernameAlreadyExist($user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT user_id FROM users
                                       WHERE user_name = :user_name
                                       LIMIT 1");
        $query->execute([':user_name' => $user_name]);
        return ($query->rowCount() !== 0);
    }

    public static function doesEmailAlreadyExist($user_email)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT user_id FROM users
                                       WHERE email = :user_email
                                       LIMIT 1");
        $query->execute([':user_email' => $user_email]);
        return ($query->rowCount() !== 0);
    }

    public static function saveNewUserName($user_id, $new_user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users
                                        SET user_name = :user_name
                                      WHERE user_id = :user_id
                                      LIMIT 1");
        $query->execute([
            ':user_name' => $new_user_name,
            ':user_id'   => $user_id
        ]);
        return ($query->rowCount() === 1);
    }

    public static function saveNewEmailAddress($user_id, $new_user_email)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users
                                        SET email = :user_email
                                      WHERE user_id = :user_id
                                      LIMIT 1");
        $query->execute([
            ':user_email' => $new_user_email,
            ':user_id'    => $user_id
        ]);
        return ($query->rowCount() === 1);
    }

    public static function editUserName($new_user_name)
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

    public static function editUserEmail($new_user_email)
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
            ':user_id'               => $user_id,
            ':user_remember_me_token'=> $token
        ]);

        return $query->fetch();
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function saveUserEdit($data): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE users
                   SET user_name = :user_name,
                       password_hash = :password_hash,
                       email = :email,
                       location = :location,
                       about = :about
                 WHERE user_id = :user_id
                 LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute([
            ':user_name' => $data['name'],
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':email'     => $data['email'],
            ':location'  => $data['location'],
            ':about'     => $data['about'],
            ':user_id'   => Session::get('user_id')
        ]);

        $changed_data = array();

        if ($data['name'] != Session::get('user_name')) {
            $changed_data[] = 'Benutzername';
        }
        if ($data['password'] != null) {
            $changed_data[] = 'Passwort';
        }
        if ($data['email'] != Session::get('user_email')) {
            $changed_data[] = 'E-Mail-Adresse';
        }
        if ($data['location'] != Session::get('user_location')) {
            $changed_data[] = 'Standort';
        }
        if ($data['about'] != Session::get('user_about')) {
            $changed_data[] = 'Über mich';
        }

        if ($query->rowCount() == 1) {
            if (!self::sendUpdateMail($data['name'], Session::get('user_email'), $changed_data)) {
                Session::add('feedback_negative', 'Fehler bei der Mail-Übermittlung');
                return false;
            }

            Session::set('user_name', $data['name']);
            Session::set('user_email', $data['email']);
            Session::set('user_location', $data['location']);
            Session::set('user_about', $data['about']);
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
                            <p>Hallo ' .$name.',</p>
                            <p>Ihre folgenden Daten wurden gerade geändert:</p>
                            <ul>
                                <li>'.implode('</li><li>', $changed_data).'</li>
                            </ul>
                            <hr>
                            <p>Falls diese Änderungen nicht nach Ihren Wünschen entstanden sind, wenden Sie sich bitte an unseren Support unter <a href="mailto:support@respawngaming.at">Support</a></p>
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
}
