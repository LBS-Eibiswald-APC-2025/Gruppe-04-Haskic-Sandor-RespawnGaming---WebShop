<?php

class PasswordResetController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function request(): void
    {
        $this->View->render('login/requestPasswordReset');
    }

    public function sendResetEmail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Config::get('URL') . 'login/requestPasswordReset');
            exit();
        }

        $user_input = trim($_POST['user_input'] ?? '');
        if (empty($user_input)) {
            Session::add('feedback_negative', 'Bitte E-Mail oder Benutzername eingeben.');
            header('Location: ' . Config::get('URL') . 'login/requestPasswordReset');
            exit();
        }

        $user = PasswordResetModel::getUserByUserNameOrEmail($user_input);
        if (!$user) {
            Session::add('feedback_negative', 'Benutzer nicht gefunden.');
            header('Location: ' . Config::get('URL') . 'login/requestPasswordReset');
            exit();
        }

        $token = bin2hex(random_bytes(32));
        $timestamp = time();

        if (!PasswordResetModel::storeResetToken($user->user_id, $token, $timestamp)) {
            Session::add('feedback_negative', 'Fehler beim Speichern des Tokens.');
            header('Location: ' . Config::get('URL') . 'login/requestPasswordReset');
            exit();
        }

        if (!PasswordResetModel::sendPasswordResetMail($user->user_name, $token, $user->email)) {
            Session::add('feedback_negative', 'Fehler beim Versenden der E-Mail.');
            header('Location: ' . Config::get('URL') . 'login/requestPasswordReset');
            exit();
        }

        Session::add('feedback_positive', 'Falls dein Konto existiert, haben wir eine E-Mail gesendet.');
        header('Location: ' . Config::get('URL') . 'login/requestPasswordReset');
        exit();
    }

    public function reset(): void
    {
        if (!isset($_GET['user'], $_GET['token'])) {
            Session::add('feedback_negative', 'Ungültiger Link.');
            header('Location: ' . Config::get('URL') . 'login/index');
            exit();
        }

        $user_name = $_GET['user'];
        $token = $_GET['token'];

        $user = PasswordResetModel::getUserByUserNameOrEmail($user_name);
        if (!$user || !PasswordResetModel::verifyResetToken($user->user_id, $token)) {
            Session::add('feedback_negative', 'Ungültiger oder abgelaufener Link.');
            header('Location: ' . Config::get('URL') . 'login/index');
            exit();
        }

        $this->View->render('login/resetPassword', ['user_name' => $user_name, 'token' => $token]);
    }

    public function updatePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Config::get('URL') . 'login/requestPasswordReset');
            exit();
        }

        $user_name = $_POST['user_name'];
        $token = $_POST['token'];
        $new_password = $_POST['new_password'];
        $repeat_password = $_POST['repeat_password'];

        if ($new_password !== $repeat_password || strlen($new_password) < 6) {
            Session::add('feedback_negative', 'Passwortanforderungen nicht erfüllt.');
            header("Location: " . Config::get('URL') . "passwordReset/reset?user=" . urlencode($user_name) . "&token=" . urlencode($token));
            exit();
        }

        $user = PasswordResetModel::getUserByUserNameOrEmail($user_name);
        if (!$user || !PasswordResetModel::verifyResetToken($user->user_id, $token)) {
            Session::add('feedback_negative', 'Ungültiger Token.');
            header('Location: ' . Config::get('URL') . 'login/index');
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        PasswordResetModel::updatePassword($user->user_id, $hashed_password);

        Session::add('feedback_positive', 'Passwort erfolgreich geändert.');
        header('Location: ' . Config::get('URL') . 'login/index');
        exit();
    }
}
