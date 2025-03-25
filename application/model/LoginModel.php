<?php

class LoginModel
{
    public static function login($user_name, $user_password, $set_remember_me_cookie = 0): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // Wir suchen in der DB nach einem Datensatz, der in der Spalte 'name' oder 'email' passt
        // und holen uns 'user_role' (statt 'role'), 'name' (statt 'user_name'), usw.
        $sql = "SELECT 
                    user_id,
                    user_name,
                    email,
                    password_hash,
                    user_active, 
                    role,
                    avatar,
                    banner,
                    user_remember_me_token, 
                    user_failed_logins, 
                    user_last_failed_login,
                    created_at,
                    location,
                    about
                FROM users
                WHERE (user_name = :user_name OR email = :user_name)
                LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':user_name' => $user_name]);
        $user = $query->fetch();

        if (!$user) {
            Session::add('feedback_negative', 'Benutzer existiert nicht.');
            return false;
        }

        if (!password_verify($user_password, $user->password_hash)) {
            Session::add('feedback_negative', 'Passwort ist falsch.');
            return false;
        }

        // Ist der Account aktiv? (user_active != 1 => gesperrt oder inaktiv)
        if ($user->user_active != 1) {
            Session::add('feedback_negative', 'Account ist nicht aktiviert oder gesperrt.');
            return false;
        }

        // Session setzen
        Session::init();
        Session::set('user_logged_in', true);
        Session::set('user_id', $user->user_id);
        Session::set('user_name', $user->user_name);
        Session::set('user_email', $user->email);
        Session::set('avatar', $user->avatar);
        Session::set('banner', $user->banner);

        // user_account_type => 'Admin' oder 'User'
        Session::set('user_data', [
            'user_id'           => $user->user_id,
            'user_name'         => $user->user_name,
            'user_account_type' => $user->role,
            'user_email'        => $user->email,
            'user_location'     => $user->location,
            'user_member_since' => $user->created_at,
            'about'             => $user->about,
            'avatar'            => $user->avatar,
            'banner'            => $user->banner
        ]);

        // concurrency-check
        Session::updateSessionId($user->user_id, session_id());

        if ($set_remember_me_cookie) {
            self::setRememberMe($user->user_id);
        }

        Session::add('feedback_positive', 'Login erfolgreich.');
        return true;
    }

    private static function setRememberMe($user_id)
    {
        // ...
    }

    public static function deleteCookie()
    {
        // ...
    }

    public static function logout(): void
    {
        self::deleteCookie();
        Session::set('user_logged_in', false);
        Session::remove('user_data');
        Session::destroy();
    }

    public static function loginWithCookie($cookie)
    {
        // ...
    }

    public static function isUserLoggedIn(): bool
    {
        return Session::userIsLoggedIn();
    }
}
