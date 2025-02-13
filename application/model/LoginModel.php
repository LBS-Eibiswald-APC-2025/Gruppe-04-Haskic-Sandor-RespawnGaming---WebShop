<?php

/**
 * LoginModel
 *
 * Handles login/logout functionalities securely.
 */
class LoginModel
{
    /**
     * Login process (for DEFAULT user accounts).
     *
     * @param string $user_name The user's name
     * @param string $user_password The user's password
     * @param string $user_email The user's email
     * @param mixed|null $set_remember_me_cookie Marker for "remember me" feature
     *
     * @return bool success state
     */
    public static function login(string $user_name, string $user_password, string $user_email, mixed $set_remember_me_cookie = null): bool
    {
        // CSRF-Schutz prüfen
        if (!isset($_POST['csrf_token']) || !Session::validateCSRFToken($_POST['csrf_token'])) {
            Session::add('feedback_negative', Text::get('FEEDBACK_CSRF_FAILED'));
            return false;
        }

        // Überprüfung auf leere Eingaben
        if (empty($user_name) || empty($user_password)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_EMAIL_OR_PASSWORD_FIELD_EMPTY'));
            return false;
        }

        // Nutzer validieren
        $user = self::validateAndGetUser($user_name, $user_password);

        if (!$user) {
            return false; // Fehlerfeedback wird bereits in `validateAndGetUser()` gesetzt
        }

        // Gesperrte oder gelöschte Konten blockieren
        if ($user->user_deleted == 1) {
            Session::add('feedback_negative', Text::get('FEEDBACK_DELETED'));
            return false;
        }

        if ($user->user_suspension_timestamp && $user->user_suspension_timestamp > time()) {
            $suspensionTime = round(($user->user_suspension_timestamp - time()) / 3600, 2);
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_SUSPENDED') . " $suspensionTime hours left");
            return false;
        }

        // Login-Zeit speichern und Fehllogins zurücksetzen
        self::resetFailedLoginCounterOfUser($user->user_name);
        self::saveTimestampOfLoginOfUser($user->user_name);

        // "Remember Me"-Funktion setzen
        if ($set_remember_me_cookie) {
            self::setRememberMeInDatabaseAndCookie($user->user_id);
        }

        // Erfolgreiches Login
        self::setSuccessfulLoginIntoSession($user->user_id, $user->user_name, $user->user_email, $user->user_account_type);
        return true;
    }

    /**
     * Validates user credentials and retrieves user data.
     *
     * @param string $user_name
     * @param string $user_password
     * @return mixed|bool User object if successful, false otherwise
     */
    private static function validateAndGetUser(string $user_name, string $user_password): mixed
    {
        // Schutz vor Brute-Force
        if (Session::get('failed-login-count') >= 3 && Session::get('last-failed-login') > (time() - 30)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_LOGIN_FAILED_3_TIMES'));
            return false;
        }

        // Nutzer abrufen
        $user = UserModel::getUserDataByUsername($user_name);

        if (!$user) {
            self::incrementUserNotFoundCounter();
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_OR_PASSWORD_WRONG'));
            return false;
        }

        // Sperrung prüfen
        if ($user->user_failed_logins >= 3 && $user->user_last_failed_login > (time() - 30)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_WRONG_3_TIMES'));
            return false;
        }

        // Passwort prüfen
        if (!password_verify($user_password, $user->user_password_hash)) {
            self::incrementFailedLoginCounterOfUser($user->user_name);
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_OR_PASSWORD_WRONG'));
            return false;
        }

        // Aktivierungsprüfung
        if ($user->user_active != 1) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_NOT_ACTIVATED_YET'));
            return false;
        }

        self::resetUserNotFoundCounter();
        return $user;
    }

    /**
     * Setzt die Session für eingeloggte Nutzer.
     */
    public static function setSuccessfulLoginIntoSession(int $user_id, string $user_name, string $user_email, string $user_account_type): void
    {
        Session::init();
        session_regenerate_id(true);
        $_SESSION = [];

        Session::set('user_id', $user_id);
        Session::set('user_name', $user_name);
        Session::set('user_email', $user_email);
        Session::set('user_account_type', $user_account_type);
        Session::set('user_logged_in', true);
        Session::updateSessionId($user_id, session_id());

        setcookie(session_name(), session_id(), time() + Config::get('SESSION_RUNTIME'), Config::get('COOKIE_PATH'),
            Config::get('COOKIE_DOMAIN'), Config::get('COOKIE_SECURE'), Config::get('COOKIE_HTTP'));
    }

    /**
     * Erhöht den Zähler für fehlerhafte Logins.
     */
    public static function incrementFailedLoginCounterOfUser(string $user_name): void
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE users SET user_failed_logins = user_failed_logins+1, user_last_failed_login = :time WHERE user_name = :user_name";
        $stmt = $db->prepare($sql);
        $stmt->execute([':user_name' => $user_name, ':time' => time()]);
    }

    /**
     * Löscht das Remember-Me-Cookie und den Token in der Datenbank.
     */
    public static function deleteCookie(?int $user_id = null): void
    {
        if ($user_id) {
            $db = DatabaseFactory::getFactory()->getConnection();
            $sql = "UPDATE users SET user_remember_me_token = NULL WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
        }

        setcookie('remember_me', '', time() - 3600, Config::get('COOKIE_PATH'),
            Config::get('COOKIE_DOMAIN'), Config::get('COOKIE_SECURE'), Config::get('COOKIE_HTTP'));
    }

    /**
     * CSRF-Token validieren
     */
    public static function validateCSRFToken(string $csrf_token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $csrf_token);
    }

    public static function isUserLoggedIn(): void
    {
    }
}
