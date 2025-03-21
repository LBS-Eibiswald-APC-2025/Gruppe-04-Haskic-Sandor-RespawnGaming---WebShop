<?php

class PasswordResetController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Zeigt das Formular für die Passwort-Reset-Anfrage
     */
    public function requestPasswordReset(): void
    {
        $this->View->render('login/requestPasswordReset');
    }

    public function submitPasswordReset() {
        $user_input = trim(Request::post('user_name_or_email'));
        $captcha = Request::post('captcha');

        if (empty($user_input) || empty($captcha)) {
            Session::add('feedback_negative', 'Bitte Benutzername oder E-Mail und Captcha eingeben.');
            Redirect::to('login/requestPasswordReset');
            return;
        }

        echo $captcha;
        die();

        $user = PasswordResetModel::getUserByUserNameOrEmail($user_input);
        if (!$user) {
            Session::add('feedback_negative', 'Benutzer nicht gefunden.');
            Redirect::to('login/requestPasswordReset');
            return;
        }

        $token = sha1(uniqid(mt_rand(), true));
        $timestamp = time();

        if (!PasswordResetModel::storeResetToken($user->user_id, $token, $timestamp)) {
            Session::add('feedback_negative', 'Fehler beim Speichern des Tokens.');
            Redirect::to('login/requestPasswordReset');
            return;
        }

        if (!PasswordResetModel::sendPasswordResetMail($user->user_name, $token, $user->email)) {
            Session::add('feedback_negative', 'Fehler beim Versenden der E-Mail.');
            Redirect::to('login/requestPasswordReset');
            return;
        }

        // Nachricht anzeigen, dass die E-Mail gesendet wurde
        Session::add('feedback_positive', 'Eine E-Mail mit dem Passwort-Reset-Link wurde gesendet.');
        Redirect::to('login/requestPasswordReset');
    }
}
