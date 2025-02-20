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
                    user_remember_me_token, 
                    user_failed_logins, 
                    user_last_failed_login
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

        // Statt Zahlenwert (7) prüfen wir hier, ob user_role == 'Admin'
        $account_type = ($user->role === 'Admin') ? 'Admin' : 'User';

        // Session setzen
        Session::init();
        Session::set('user_logged_in', true);
        Session::set('user_id', $user->user_id);
        Session::set('user_name', $user->user_name);
        Session::set('user_email', $user->email);

        // user_account_type => 'Admin' oder 'User'
        Session::set('user_data', [
            'user_id'           => $user->user_id,
            'user_name'         => $user->user_name,
            'user_account_type' => $account_type
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
