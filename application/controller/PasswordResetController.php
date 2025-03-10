<?php

use Random\RandomException;


class PasswordResetController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct(); // Ruft den Konstruktor der Elternklasse auf
        // weitere Initialisierungen, z.B. Model laden:
        $this->userModel = new UserModel();
    }

    /**
     * Zeigt das Formular für die Passwort-Reset-Anfrage
     */
    public function requestPasswordReset(): void
    {
        $this->View->render('login/requestPasswordReset');
    }

    /**
     * Verarbeitet die Anfrage für den Passwort-Reset
     * @throws RandomException
     */
    public function sendResetRequest(): void
    {
        $user_input = trim(Request::post('user_input'));

        if (empty($user_input)) {
            Session::add('feedback_negative', 'Bitte Benutzername oder E-Mail angeben.');
            $this->View->render('login/requestPasswordReset');
            return;
        }

        $user = PasswordResetModel::getUserByUserNameOrEmail($user_input);
        if (!$user) {
            Session::add('feedback_negative', 'Benutzer nicht gefunden.');
            $this->View->render('login/requestPasswordReset');
            return;
        }

        $token = bin2hex(random_bytes(32)); // Sichere Methode zur Token-Erstellung (kryptografisch)
        $timestamp = time();

        if (!PasswordResetModel::storeResetToken($user->user_id, $token, $timestamp)) {
            Session::add('feedback_negative', 'Fehler beim Speichern des Tokens.');
            $this->View->render('login/requestPasswordReset');
            return;
        }

        if (!PasswordResetModel::sendPasswordResetMail($user->user_name, $token, $user->email)) {
            Session::add('feedback_negative', 'Fehler beim Versenden der E-Mail.');
            $this->View->render('login/requestPasswordReset');
            return;
        }

        // Nachricht anzeigen, dass die E-Mail gesendet wurde
        Session::add('feedback_positive', 'Eine E-Mail mit dem Passwort-Reset-Link wurde gesendet.');
        $this->View->render('login/requestPasswordReset');
    }

    /**
     * Zeigt das Formular für das Setzen eines neuen Passworts
     */
    public function resetPassword(): void
    {
        // Annahme: der Token wird als GET-Parameter übergeben
        $token = $_GET['token'] ?? '';

        // Token validieren (sowie die Ablaufzeit prüfen)
        $user = $this->userModel->findByResetToken($token);
        if ($user && $user['token_expiry'] > time()) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $newPassword = $_POST['password'];
                // Validierung und Passwort-Hashing
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                // Passwort aktualisieren und Token entfernen
                $this->userModel->updatePassword($user['id'], $hashedPassword);
                $this->userModel->removeResetToken($user['id']);

                echo "Dein Passwort wurde erfolgreich zurückgesetzt.";
            } else {
                // Zeige das Formular (evtl. über eine View einbinden)
                include "application/view/login/resetPassword.php";
            }
        } else {
            echo "Ungültiger oder abgelaufener Token.";
        }
    }

    /**
     * Setzt das neue Passwort und aktualisiert es in der Datenbank
     */
    public function updatePassword(): void
    {
        $user_name = Request::post('user_name');
        $token = Request::post('token');
        $newPassword = Request::post('new_password');
        $repeatPassword = Request::post('repeat_password');

        if (empty($user_name) || empty($token) || empty($newPassword) || empty($repeatPassword)) {
            Session::add('feedback_negative', 'Bitte alle Felder ausfüllen.');
            Redirect::to('resetPassword?user=' . urlencode($user_name) . '&token=' . urlencode($token));
            return;
        }

        if ($newPassword !== $repeatPassword) {
            Session::add('feedback_negative', 'Die Passwörter stimmen nicht überein.');
            Redirect::to('resetPassword?user=' . urlencode($user_name) . '&token=' . urlencode($token));
            return;
        }

        $user = PasswordResetModel::getUserByUserNameOrEmail($user_name);
        if (!$user || $user->user_password_reset_hash !== $token) {
            Session::add('feedback_negative', 'Ungültiger oder abgelaufener Passwort-Reset-Link.');
            Redirect::to('login/index');
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!PasswordResetModel::updatePassword($user->user_id, $hashedPassword)) {
            Session::add('feedback_negative', 'Fehler beim Speichern des neuen Passworts.');
            Redirect::to('resetPassword?user=' . urlencode($user_name) . '&token=' . urlencode($token));
            return;
        }

        Session::add('feedback_positive', 'Passwort erfolgreich geändert.');
        Redirect::to('login/index');
    }
}
