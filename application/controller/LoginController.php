<?php

use JetBrains\PhpStorm\NoReturn;
use Random\RandomException;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ----------------- Authentifizierungs-Funktionen ----------------- */

    public function index(): void
    {
        // Wenn eingeloggt, dann weiter zur Startseite
        if (LoginModel::isUserLoggedIn()) {
            Redirect::home();
        } else {
            $data = ['redirect' => Request::get('redirect') ? Request::get('redirect') : null];
            $this->View->render('login/index', $data);
        }
    }

    #[NoReturn]
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Fehler: Dies ist keine POST-Anfrage.");
        }
        if (empty($_POST)) {
            die("⚠ Fehler: Keine POST-Daten empfangen.");
        }

        // CSRF check
        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        $user_name = trim(Request::post('user_name') ?? '');
        $user_password = trim(Request::post('user_password') ?? '');
        $set_remember_me_cookie = isset($_POST['set_remember_me_cookie']) && $_POST['set_remember_me_cookie'] == '1' ? 1 : 0;

        if (empty($user_name) || empty($user_password)) {
            Session::add('feedback_negative', "Benutzername oder Passwort fehlt.");
            Redirect::to('login/index');
            exit();
        }

        $login_successful = LoginModel::login($user_name, $user_password, $set_remember_me_cookie);

        if ($login_successful && Auth::checkAdminAuthentication()) {
            Redirect::to('admin/index');
            exit();
        }

        if ($login_successful) {
            Redirect::to('user/index');
            exit();
        }

        Redirect::to('login/index');
        exit();
    }

    #[NoReturn]
    public function logout(): void
    {
        LoginModel::logout();
        Redirect::home();
        exit();
    }

    public function loginWithCookie(): bool
    {
        $login_successful = LoginModel::loginWithCookie(Request::cookie('remember_me'));

        if ($login_successful) {
            Redirect::to('games/index');
        } else {
            LoginModel::deleteCookie();
            Redirect::to('login/index');
        }
        return true;
    }

    /* ----------------- Passwort-Reset-Funktionalität ----------------- */

    /**
     * Zeigt das Formular für die Passwort-Reset-Anfrage an.
     */
    public function requestPasswordResetForm(): void
    {
        $this->View->render('login/requestPasswordReset');
    }

    /**
     * Verarbeitet die Passwort-Reset-Anfrage.
     * Liest POST-Daten, sucht den Benutzer, generiert Token, speichert ihn und versendet die Reset-Mail.
     *
     * @throws RandomException
     */
    public function sendResetRequest(): void
    {
        $user_input = trim(Request::post('user_name_or_email') ?? '');
        $captcha = trim(Request::post('captcha') ?? '');  // Falls Captcha validiert werden soll

        if (empty($user_input)) {
            Session::add('feedback_negative', 'Bitte Benutzername oder E-Mail angeben.');
            Redirect::to('login/requestPasswordResetForm');
            return;
        }

        // Nutzer suchen (hier nutzen wir die statische Methode im Model)
        $user = PasswordResetModel::getUserByUserNameOrEmail($user_input);
        if (!$user) {
            Session::add('feedback_negative', 'Benutzer nicht gefunden.');
            Redirect::to('login/requestPasswordResetForm');
            return;
        }

        // Token generieren und Ablaufzeit (1 Stunde gültig)
        $token = bin2hex(random_bytes(32));
        $timestamp = time() + 3600;

        if (!PasswordResetModel::storeResetToken($user->user_id, $token, $timestamp)) {
            Session::add('feedback_negative', 'Fehler beim Speichern des Tokens.');
            Redirect::to('login/requestPasswordResetForm');
            return;
        }

        if (!PasswordResetModel::sendPasswordResetMail($user->user_name, $token, $user->email)) {
            Session::add('feedback_negative', 'Fehler beim Versenden der E-Mail.');
            Redirect::to('login/requestPasswordResetForm');
            return;
        }

        Session::add('feedback_positive', 'Eine E-Mail mit dem Passwort-Reset-Link wurde gesendet.');
        Redirect::to('login/requestPasswordResetForm');
    }

    /**
     * Zeigt das Formular zum Zurücksetzen des Passworts an.
     * Der Reset-Link enthält den Token als GET-Parameter.
     */
    public function resetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            Session::add('feedback_negative', 'Kein Token angegeben.');
            Redirect::to('login/index');
            return;
        }

        // Benutzer anhand des Tokens finden
        $user = $this->getUserByResetToken($token);
        if (!$user || $user->user_password_reset_timestamp < time()) {
            Session::add('feedback_negative', 'Ungültiger oder abgelaufener Token.');
            Redirect::to('login/index');
            return;
        }

        // Formular zum Setzen eines neuen Passworts anzeigen
        $this->View->render('login/resetPassword', [
            'user_name' => $user->user_name,
            'token'     => $token
        ]);
    }

    /**
     * Aktualisiert das Passwort nach Eingabe im Reset-Formular.
     */
    public function updatePassword(): void
    {
        $user_name = Request::post('user_name');
        $token = Request::post('token');
        $newPassword = Request::post('new_password');
        $repeatPassword = Request::post('repeat_password');

        if (empty($user_name) || empty($token) || empty($newPassword) || empty($repeatPassword)) {
            Session::add('feedback_negative', 'Bitte alle Felder ausfüllen.');
            Redirect::to('login/resetPassword?token=' . urlencode($token));
            return;
        }

        if ($newPassword !== $repeatPassword) {
            Session::add('feedback_negative', 'Die Passwörter stimmen nicht überein.');
            Redirect::to('login/resetPassword?token=' . urlencode($token));
            return;
        }

        // Benutzer suchen, um den Token zu überprüfen
        $user = PasswordResetModel::getUserByUserNameOrEmail($user_name);
        if (!$user || $user->user_password_reset_hash !== $token) {
            Session::add('feedback_negative', 'Ungültiger oder abgelaufener Passwort-Reset-Link.');
            Redirect::to('login/index');
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!PasswordResetModel::updatePassword($user->user_id, $hashedPassword)) {
            Session::add('feedback_negative', 'Fehler beim Speichern des neuen Passworts.');
            Redirect::to('login/resetPassword?token=' . urlencode($token));
            return;
        }

        Session::add('feedback_positive', 'Passwort erfolgreich geändert.');
        Redirect::to('login/index');
    }

    /**
     * Hilfsmethode, um einen Benutzer anhand des Reset-Tokens zu finden.
     */
    private function getUserByResetToken(string $token): ?object
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT * FROM users WHERE user_password_reset_hash = :token LIMIT 1";
        $stmt = $database->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }


    // --- Restliche Login-Funktionen (login, logout, loginWithCookie, etc.) bleiben unverändert ---
}
