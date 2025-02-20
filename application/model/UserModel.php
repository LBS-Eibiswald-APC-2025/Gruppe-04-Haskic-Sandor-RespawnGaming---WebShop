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
}
