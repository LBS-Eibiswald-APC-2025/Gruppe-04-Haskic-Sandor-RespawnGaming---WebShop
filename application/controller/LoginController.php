<?php

use JetBrains\PhpStorm\NoReturn;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        // wenn eingeloggt, dann weiter zur Startseite
        if (LoginModel::isUserLoggedIn()) {
            Redirect::home();
        } else {
            $data = array('redirect' => Request::get('redirect') ? Request::get('redirect') : NULL);
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

        // Wenn Login erfolgreich und user = Admin → Admin-Dashboard
        if ($login_successful && Auth::checkAdminAuthentication()) {
            Redirect::to('admin/index');
            exit();
        }

        // Wenn Login erfolgreich (aber kein Admin) → user/index
        if ($login_successful) {
            Redirect::to('user/index');
            exit();
        }

        // Falls Login fehlschlägt
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
        }return true;
    }


    public function requestPasswordReset(string $user_input, string $captcha): bool
    {
        // Hier kannst du z.B. noch die Captcha-Eingabe prüfen
        // if (!self::verifyCaptcha($captcha)) {
        //     return false;
        // }

        // Suche den Benutzer anhand des eingegebenen Benutzernamens oder der E-Mail
        $user = self::getUserByUserNameOrEmail($user_input);
        if (!$user) {
            return false;
        }

        // Generiere einen sicheren Token und setze einen Zeitstempel (z.B. 1 Stunde Gültigkeit)
        $token = sha1(uniqid(mt_rand(), true));
        $timestamp = time() + 3600; // Token gültig für 1 Stunde

        // Speichere den Reset-Token in der Datenbank
        if (!self::storeResetToken($user->user_id, $token, $timestamp)) {
            return false;
        }

        // Sende die Reset-Mail an den Benutzer
        if (!self::sendPasswordResetMail($user->user_name, $token, $user->email)) {
            return false;
        }

        return true;
    }


    /**
     */
    public function requestPasswordReset_action()
    {

        // Holen der Benutzereingabe und Captcha (falls vorhanden)
        $user_input = Request::post('user_name_or_email') ?? '';
        $captcha = Request::post('captcha') ?? '';

        $success = (new PasswordResetModel(new UserModel()))->requestPasswordReset($user_input, $captcha);

        // Falls erfolgreich, zur Bestätigungsseite weiterleiten
        if ($success) {
            Redirect::to('login/resetRequestConfirmation');
        } else {
            Redirect::to('login/requestPasswordReset');
        }
    }

    /**
     */
    public function sendResetRequest(): void
    {
        $user_input = trim(Request::post('user_input'));

        if (empty($user_input)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_EMAIL_FIELD_EMPTY'));
            Redirect::to('login/requestPasswordReset');
            return;
        }

        $user = PasswordResetModel::getUserByUserNameOrEmail($user_input);
        if (!$user) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USER_DOES_NOT_EXIST'));
            Redirect::to('login/requestPasswordReset');
            return;
        }

        $token = sha1(uniqid(mt_rand(), true));
        $timestamp = time();

        if (!PasswordResetModel::storeResetToken($user->user_id, $token, $timestamp)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_RESET_TOKEN_FAIL'));
            Redirect::to('login/requestPasswordReset');
            return;
        }

        if (!PasswordResetModel::sendPasswordResetMail($user->user_name, $token, $user->email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_RESET_MAIL_SENDING_ERROR'));
            Redirect::to('login/requestPasswordReset');
            return;
        }

        Session::add('feedback_sweetalert', Text::get('FEEDBACK_PASSWORD_RESET_CONFIRMATION'));
        Redirect::to('login/requestPasswordReset');
    }


    public function verifyPasswordReset($user_name, $verification_code): void
    {
        if (PasswordResetModel::verifyPasswordReset($user_name, $verification_code)) {
            $this->View->render('login/resetPassword', [
                'user_name' => $user_name,
                'user_password_reset_hash' => $verification_code
            ]);
        } else {
            Redirect::to('login/index');
        }
    }

    public function setNewPassword(): void
    {
        PasswordResetModel::setNewPassword(
            Request::post('user_name'),
            Request::post('user_password_reset_hash'),
            Request::post('user_password_new'),
            Request::post('user_password_repeat')
        );
        Redirect::to('login/index');
    }

}