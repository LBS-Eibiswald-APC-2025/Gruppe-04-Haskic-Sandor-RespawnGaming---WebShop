<?php

class RegistrationModel
{
    /**
     * Registriert einen neuen Nutzer und speichert ihn in der Datenbank
     *
     * @return bool
     */
    public static function registerNewUser(): bool
    {
        // Eingaben bereinigen
        $user_name = trim(strip_tags(Request::post('user_name')));
        $user_email = trim(filter_var(Request::post('user_email'), FILTER_VALIDATE_EMAIL));
        $user_email_repeat = trim(filter_var(Request::post('user_email_repeat'), FILTER_VALIDATE_EMAIL));
        $user_password_new = Request::post('user_password_new');
        $user_password_repeat = Request::post('user_password_repeat');

        // Validierung der Eingaben
        if (!self::validateUserInputs($user_name, $user_email, $user_email_repeat, $user_password_new, $user_password_repeat)) {
            return false;
        }

        // Passwort hashen
        $user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT);

        // E-Mail Verifizierungs-Hash generieren
        $user_activation_hash = bin2hex(random_bytes(32));

        // Benutzer in die Datenbank schreiben
        if (!self::writeNewUserToDatabase($user_name, $user_password_hash, $user_email, $user_activation_hash)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            return false;
        }

        // Benutzer-ID abrufen
        $user_id = UserModel::getUserIdByUsername($user_name);
        if (!$user_id) {
            Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
            return false;
        }

        // Bestätigungs-E-Mail senden
        if (self::sendVerificationEmail($user_id, $user_email, $user_activation_hash)) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_SUCCESSFULLY_CREATED'));
            return true;
        }

        // Falls E-Mail-Versand fehlschlägt, Benutzer wieder entfernen
        self::rollbackRegistrationByUserId($user_id);
        Session::add('feedback_negative', Text::get('FEEDBACK_VERIFICATION_MAIL_SENDING_FAILED'));
        return false;
    }

    /**
     * Validiert die Eingaben des Nutzers.
     */
    private static function validateUserInputs(string $user_name, string $user_email, string $user_email_repeat, string $user_password_new, string $user_password_repeat): bool
    {
        if (empty($user_name) || empty($user_email) || empty($user_password_new)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_MISSING_FIELDS'));
            return false;
        }

        if ($user_email !== $user_email_repeat) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_REPEAT_WRONG'));
            return false;
        }

        if (strlen($user_password_new) < 8) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_TOO_SHORT'));
            return false;
        }

        return true;
    }

    /**
     * Schreibt den neuen Benutzer in die Datenbank.
     */
    private static function writeNewUserToDatabase(string $user_name, string $user_password_hash, string $user_email): bool
    {
        try {
            $db = DatabaseFactory::getFactory()->getConnection();

            // KORRIGIERT: `user_activation_hash` entfernt, weil es in der DB nicht existiert.
            $sql = "INSERT INTO users (name, password_hash, email, created_at, role) 
                VALUES (:name, :password_hash, :email, NOW(), 'Kunde')";

            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                ':name' => $user_name,
                ':password_hash' => $user_password_hash,
                ':email' => $user_email
            ]);

            // Falls die Abfrage fehlschlägt, Debug-Info anzeigen
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                die("SQL-Fehler: " . print_r($errorInfo, true));
            }

            return true;
        } catch (Exception $e) {
            die("Datenbank-Fehler: " . $e->getMessage());
        }
    }

    /**
     * Sendet die Bestätigungs-E-Mail.
     */
    private static function sendVerificationEmail(int $user_id, string $user_email, string $user_activation_hash): bool
    {
        $verification_link = Config::get('URL') . "verify/" . urlencode($user_id) . "/" . urlencode($user_activation_hash);
        $body = "Bitte bestätige deine Registrierung mit folgendem Link: $verification_link";

        $mail = new Mail();
        return $mail->sendMail($user_email, "no-reply@deinewebsite.com", "Dein Team", "E-Mail-Bestätigung", $body);
    }

    /**
     * Löscht einen Benutzer, falls die Registrierung fehlschlägt.
     */
    private static function rollbackRegistrationByUserId(int $user_id): void
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
    }

    /**
     * Bestätigt den neuen Benutzer über den E-Mail-Link.
     */
    public static function verifyNewUser(int $user_id, string $user_activation_verification_code): bool
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("UPDATE users SET user_active = 1, user_activation_hash = NULL WHERE id = :id AND user_activation_hash = :activation_hash LIMIT 1");
        $stmt->execute([
            ':id' => $user_id,
            ':activation_hash' => $user_activation_verification_code
        ]);

        if ($stmt->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_SUCCESSFUL'));
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_FAILED'));
        return false;
    }
}
