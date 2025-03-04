<?php

class PasswordResetModel
{
    /**
     * Holt einen Benutzer anhand von Benutzername oder E-Mail.
     * Achtung: Die Spalte in der Datenbank heißt `email`, nicht `user_email`!
     */
    public static function getUserByUserNameOrEmail(string $input): ?object
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT user_id, user_name, email FROM users WHERE user_name = :input OR email = :input LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':input' => $input]);

        $user = $query->fetch();
        if (!$user || empty($user->email)) {
            return null; // Falls kein gültiger Nutzer mit E-Mail existiert.
        }
        return $user;
    }

    public static function requestPasswordReset(string $user_input, string $captcha): bool
    {
        if (!CaptchaModel::checkCaptcha($captcha)) {
            Session::add('feedback_negative', 'Ungültiges Captcha.');
            return false;
        }

        $user = self::getUserByUserNameOrEmail($user_input);
        if (!$user) {
            Session::add('feedback_negative', 'Kein Benutzer mit dieser E-Mail oder diesem Benutzernamen gefunden.');
            return false;
        }

        // Token generieren
        $token = bin2hex(random_bytes(32));
        $timestamp = time();

        // Speichert den Token in der Datenbank
        if (!self::storeResetToken($user->user_id, $token, $timestamp)) {
            Session::add('feedback_negative', 'Fehler beim Speichern des Tokens.');
            return false;
        }

        // Sende die E-Mail mit Reset-Link
        if (!self::sendPasswordResetMail($user->user_name, $token, $user->email)) {
            Session::add('feedback_negative', 'Fehler beim Versenden der E-Mail.');
            return false;
        }

        Session::add('feedback_positive', 'Falls dein Konto existiert, wurde eine E-Mail gesendet.');
        return true;
    }

    public static function sendPasswordResetMail(string $user_name, string $token, string $email): bool
    {
        $resetLink = Config::get('URL') . "passwordReset/reset?user=" . urlencode($user_name) . "&token=" . urlencode($token);
        $body = "Hallo $user_name,\n\nKlicke auf diesen Link, um dein Passwort zurückzusetzen:\n$resetLink\n\nFalls du dies nicht angefordert hast, ignoriere diese Nachricht.";
        $subject = "Passwort zurücksetzen";
        $headers = "From: " . Config::get('EMAIL_PASSWORD_RESET_FROM_EMAIL');

        if (mail($email, $subject, $body, $headers)) {
            Session::add('feedback_positive', 'E-Mail mit Passwort-Reset-Link wurde gesendet.');
            return true;
        }

        Session::add('feedback_negative', 'Fehler beim Versenden der E-Mail.');
        return false;
    }
}