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

        // Wenn Login erfolgreich und user = Admin -> Admin-Dashboard
        if ($login_successful && Auth::checkAdminAuthentication()) {
            Redirect::to('admin/index');
            exit();
        }

        // Wenn Login erfolgreich (aber kein Admin) -> user/index
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

    public function loginWithCookie(): void
    {
        $login_successful = LoginModel::loginWithCookie(Request::cookie('remember_me'));

        if ($login_successful) {
            Redirect::to('games/index');
        } else {
            LoginModel::deleteCookie();
            Redirect::to('login/index');
        }
    }

    public function requestPasswordReset(): void
    {
        $this->View->render('login/requestPasswordReset');
    }

    public function requestPasswordReset_action(): void
    {
        PasswordResetModel::requestPasswordReset(Request::post('user_name_or_email'), Request::post('captcha'));
        Redirect::to('login/index');
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
