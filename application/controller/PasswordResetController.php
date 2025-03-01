<?php

class PasswordResetController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Zeigt das Formular für "Passwort vergessen"
     */
    public function request(): void
    {
        $this->View->render('password_reset/request');
    }

    /**
     * Verarbeitet das "Passwort vergessen"-Formular
     */
    public function sendResetEmail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /password-reset/request');
            exit();
        }

        $input = $_POST['user_input'] ?? '';

        // Benutzer anhand von Benutzername oder E-Mail suchen
        $user = PasswordResetModel::getUserByEmailOrUsername($input);
        if (!$user) {
            header('Location: /password-reset/request?error=user_not_found');
            exit();
        }

        // Generiere Token & speichere in DB
        $token = bin2hex(random_bytes(32));
        PasswordResetModel::storeResetToken($user->user_id, $token);

        // Link für den Reset
        $resetLink = Config::get('URL') . "password-reset/reset?token=$token";

        // E-Mail senden
        $subject = "Passwort zurücksetzen - RespawnGaming";
        $message = "Hallo,\n\nKlicke auf den folgenden Link, um dein Passwort zurückzusetzen:\n$resetLink\n\nFalls du diese Anfrage nicht gestellt hast, ignoriere diese Nachricht.";
        mail($user->email, $subject, $message, "From: support@respawngaming.at");

        // Weiterleitung zur Bestätigungsseite
        header('Location: /password-reset/confirmation');
        exit();
    }

    /**
     * Zeigt das Passwort-Reset-Formular
     */
    public function reset(): void
    {
        $this->View->render('password_reset/reset', ['token' => $_GET['token'] ?? '']);
    }

    /**
     * Verarbeitet das neue Passwort
     */
    public function updatePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /password-reset/request');
            exit();
        }

        $token = $_POST['token'] ?? '';
        $newPassword = $_POST['password'] ?? '';

        // Benutzer anhand des Tokens finden
        $user = PasswordResetModel::getUserByToken($token);
        if (!$user) {
            header('Location: /password-reset/reset?error=invalid_token');
            exit();
        }

        // Passwort hashen & speichern
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        PasswordResetModel::updatePassword($user->user_id, $hashedPassword);

        // Token entfernen
        PasswordResetModel::clearResetToken($user->user_id);

        // Weiterleitung zur Erfolgsseite
        header('Location: /login?success=password_reset');
        exit();
    }
}
