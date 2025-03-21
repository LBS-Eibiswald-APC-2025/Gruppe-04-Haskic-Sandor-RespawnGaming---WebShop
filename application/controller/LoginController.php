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

    public function resetPassword(): void {
        $this->View->render('login/resetPassword',
            ['user_password_reset_hash' => Request::get('quadratfrosch')]
        );
    }

    public function requestPasswordReset(): void
    {
        $this->View->render('login/requestPasswordReset');
    }

    /**
     */
    public function submitPasswordReset(): void
    {
        $user_input = Request::post('user_name_or_email') ?? '';
        $captcha = Request::post('captcha') ?? '';

        $request = PasswordResetModel::requestPasswordReset($user_input, $captcha);

        if ($request->feedback === 'success') {
            Session::add('feedback_positive', 'Es wurde eine E-Mail mit weiteren Anweisungen versendet.');
            Redirect::to('login/index');
        } else {
            Session::add('feedback_negative', 'Passwort zurücksetzen fehlgeschlagen. Bitte überprüfen Sie: ' . $request->error);
            Redirect::to('login/requestPasswordReset');
        }
    }

    public function verifyPasswordReset($verification_code): void
    {
        if (PasswordResetModel::verifyPasswordReset($verification_code)) {
            Session::add('feedback_positive', 'Passwort zurücksetzen erfolgreich. Bitte geben Sie ein neues Passwort ein.');
            $this->View->render('login/resetPassword', [
                'user_password_reset_hash' => $verification_code
            ]);
        } else {
            Session::add('feedback_negative', 'Ungültiger oder abgelaufener Link.');
            Redirect::to('login/index');
        }
    }

    public function setNewPassword(): void
    {
        $return = PasswordResetModel::setNewPassword(
            Request::post('hash'),
            Request::post('password'),
            Request::post('password_repeat')
        );

        if ($return) {
            Session::add('feedback_positive', 'Passwort erfolgreich geändert. Bitte melden Sie sich an.');
            Redirect::to('login/index');
        } else {
            Redirect::to('login/resetPassword/?quadratfrosch=' . Request::post('hash'));
        }
    }

}
