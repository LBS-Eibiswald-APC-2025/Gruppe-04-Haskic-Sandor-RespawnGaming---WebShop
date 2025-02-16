<?php

use JetBrains\PhpStorm\NoReturn;

/**
 * LoginController
 * Controls everything that is authentication-related
 */
class LoginController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class. The parent::__construct thing is necessary to
     * put checkAuthentication in here to make an entire controller only usable for logged-in users (for sure not
     * needed in the LoginController).
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Index, default action (shows the login form), when you do log in/index
     */
    public function index(): void
    {
        // if a user is logged in redirect to main-page, if not show the view
        if (LoginModel::isUserLoggedIn()) {
            Redirect::home();
        } else {
            $data = array('redirect' => Request::get('redirect') ? Request::get('redirect') : NULL);
            $this->View->render('login/index', $data);
        }
    }

    /**
     * The login action, when you do log in/login
     */
    #[NoReturn] public function login(): void
    {
        // Prüfen, ob es sich um eine POST-Anfrage handelt
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Fehler: Dies ist keine POST-Anfrage.");
        }

        // Debugging: Falls keine POST-Daten empfangen wurden
        if (empty($_POST)) {
            die("⚠ Fehler: Keine POST-Daten empfangen. Formular korrekt gesendet?");
        }

        // Überprüfung des CSRF-Tokens
        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        // Sicherstellen, dass die POST-Daten existieren
        $user_name = trim(Request::post('user_name') ?? '');
        $user_password = trim(Request::post('user_password') ?? '');

        // Checkbox "Eingeloggt bleiben" verarbeiten
        $set_remember_me_cookie = isset($_POST['set_remember_me_cookie']) && $_POST['set_remember_me_cookie'] == '1' ? 1 : 0;

        if (empty($user_name) || empty($user_password)) {
            Session::add('feedback_negative', "Fehlende Eingaben: Benutzername oder Passwort nicht vorhanden.");
            Redirect::to('login/index');
            exit();
        }

        // Login aufrufen
        $login_successful = LoginModel::login($user_name, $user_password, null, $set_remember_me_cookie);

        // Falls Login erfolgreich weiterleiten
        if ($login_successful) {
            $redirect_url = Request::post('redirect') ? ltrim(urldecode(Request::post('redirect')), '/') : 'user/index';
            Redirect::to($redirect_url);
            exit();
        }

        // Falls Login fehlschlägt, zur Login-Seite weiterleiten
        $redirect_fail_url = Request::post('redirect') ? 'login?redirect=' . ltrim(urlencode(Request::post('redirect')), '/') : 'login/index';
        Redirect::to($redirect_fail_url);
        exit();
    }


    /**
     * The logout action
     * Perform logout, redirect user to main-page
     */
    #[NoReturn] public function logout(): void
    {
        LoginModel::logout();
        Redirect::home();
        exit();
    }

    /**
     * Login with cookie
     */
    public function loginWithCookie(): void
    {
        // run the loginWithCookie() method in the login-model, put the result in $login_successful (true or false)
        $login_successful = LoginModel::loginWithCookie(Request::cookie('remember_me'));

        // if login successful, redirect to games/index ...
        if ($login_successful) {
            Redirect::to('games/index');
        } else {
            // if not, delete cookie (outdated? attack?) and route user to login form to prevent infinite login loops
            LoginModel::deleteCookie();
            Redirect::to('login/index');
        }
    }

    /**
     * Show the request-password-reset page
     */
    public function requestPasswordReset(): void
    {
        $this->View->render('login/requestPasswordReset');
    }

    /**
     * The request-password-reset action
     * POST-request after form submit
     */
    public function requestPasswordReset_action(): void
    {
        PasswordResetModel::requestPasswordReset(Request::post('user_name_or_email'), Request::post('captcha'));
        Redirect::to('login/index');
    }

    /**
     * Verify the verification token of that user (to show the user the password editing view or not)
     * @param string $user_name username
     * @param string $verification_code password reset verification token
     */
    public function verifyPasswordReset($user_name, $verification_code): void
    {
        // check if this the provided verification code fits the user's verification code
        if (PasswordResetModel::verifyPasswordReset($user_name, $verification_code)) {
            // pass URL-provided variable to view to display them
            $this->View->render('login/resetPassword', array(
                'user_name' => $user_name,
                'user_password_reset_hash' => $verification_code
            ));
        } else {
            Redirect::to('login/index');
        }
    }

    /**
     * Set the new password
     * Please note that this happens while the user is not logged in. The user identifies via the data provided by the
     * password reset link from the email, automatically filled into the <form> fields. See verifyPasswordReset()
     * for more. Then (regardless of result) route user to index page (user will get success/error via feedback message)
     * POST request !
     * TODO this is an _action
     */
    public function setNewPassword(): void
    {
        PasswordResetModel::setNewPassword(
            Request::post('user_name'), Request::post('user_password_reset_hash'),
            Request::post('user_password_new'), Request::post('user_password_repeat')
        );
        Redirect::to('login/index');
    }
}
