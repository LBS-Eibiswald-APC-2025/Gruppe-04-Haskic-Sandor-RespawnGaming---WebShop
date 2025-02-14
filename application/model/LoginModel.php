<?php

use JetBrains\PhpStorm\NoReturn;

/**
 * LoginModel
 *
 * Handles login/logout functionalities securely.
 */
class LoginModel
{
    /**
     * Login process (for DEFAULT user accounts).
     */
    public static function login(string $user_name, string $user_password, ?string $user_email = null, int $set_remember_me_cookie = 0): bool
    {
        // CSRF-Schutz prüfen
        if (!isset($_POST['csrf_token']) || !hash_equals(Session::get('csrf_token'), $_POST['csrf_token'])) {
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
            return false;
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
        self::resetFailedLoginCounterOfUser($user->name);
        self::saveTimestampOfLoginOfUser($user->name);

        // "Remember Me"-Funktion setzen
        if ($set_remember_me_cookie) {
            self::setRememberMeInDatabaseAndCookie($user->id);
        }

        // Erfolgreiches Login
        self::setSuccessfulLoginIntoSession($user->id, $user->name, $user->email, $user->role);
        return true;
    }

    /**
     * Validates user credentials and retrieves user data.
     */
    private static function validateAndGetUser(string $user_name, string $user_password): mixed
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE name = :user_name LIMIT 1");
        $stmt->execute([':user_name' => $user_name]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$user) {
            self::incrementFailedLoginCounterOfUser($user_name);
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_OR_PASSWORD_WRONG'));
            return false;
        }

        // Sperrung prüfen
        if ($user->user_failed_logins >= 5 && $user->user_last_failed_login > (time() - 600)) {
            Session::add('feedback_negative', "Zu viele fehlgeschlagene Versuche. Bitte warte 10 Minuten.");
            return false;
        }

        // Passwort prüfen
        if (!password_verify($user_password, $user->password_hash)) {
            self::incrementFailedLoginCounterOfUser($user_name);
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_OR_PASSWORD_WRONG'));
            return false;
        }

        self::resetFailedLoginCounterOfUser($user_name);
        return $user;
    }

    /**
     * Setzt die Session für eingeloggte Nutzer.
     */
    public static function setSuccessfulLoginIntoSession(int $user_id, string $user_name, string $user_email, string $user_account_type): void
    {
        Session::init();

        $_SESSION = [];
        Session::set('user_id', $user_id);
        Session::set('user_name', $user_name);
        Session::set('user_email', $user_email);
        Session::set('user_account_type', $user_account_type);
        Session::set('user_logged_in', true);

        session_regenerate_id(true); // Sicherstellen, dass die Session nicht übernommen wird.

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
        $sql = "UPDATE users SET user_failed_logins = user_failed_logins+1, user_last_failed_login = :time WHERE name = :user_name";
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
            $sql = "UPDATE users SET user_remember_me_token = NULL WHERE id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
        }

        setcookie('remember_me', '', time() - 3600, Config::get('COOKIE_PATH'),
            Config::get('COOKIE_DOMAIN'), Config::get('COOKIE_SECURE'), Config::get('COOKIE_HTTP'));
    }

    /**
     * Loggt den Benutzer aus, löscht die Session und das Remember-Me-Cookie.
     */
    #[NoReturn] public static function logout(): void
    {
        Session::init();

        // Nutzer-ID aus der Session holen
        $user_id = Session::get('user_id');

        // Falls ein Benutzer eingeloggt ist, das Remember-Me-Cookie entfernen
        if ($user_id) {
            self::deleteCookie($user_id);
        }

        // Sitzung sicher zerstören
        Session::destroy();

        // Nutzer auf die Login-Seite weiterleiten
        header("Location: " . Config::get('URL') . "login");
        exit();
    }

    /**
     * Überprüft, ob der Benutzer eingeloggt ist.
     *
     * @return bool Gibt `true` zurück, wenn der Benutzer eingeloggt ist, sonst `false`.
     */
    public static function isUserLoggedIn(): bool
    {
        return Session::get('user_logged_in') === true;
    }
}
