<?php

use Random\RandomException;

class RegistrationModel
{
    /**
     * Registriert einen neuen Nutzer und speichert ihn in der Datenbank
     *
     * @return bool
     * @throws RandomException
     * @throws Exception
     */
    public static function registerNewUser(): bool
    {
        // Eingaben bereinigen
        $user_name = trim(strip_tags(Request::post('user_name')));
        $user_email = trim(filter_var(Request::post('user_email'), FILTER_VALIDATE_EMAIL));
        // $user_email_repeat = trim(filter_var(Request::post('user_email_repeat'), FILTER_VALIDATE_EMAIL));
        $user_password_new = Request::post('user_password_new');
        $user_password_repeat = Request::post('user_password_repeat');

        // Validierung der Eingaben
        if (!self::validateUserInputs($user_name, $user_email, $user_password_new, $user_password_repeat)) {
            return false;
        }

        // Passwort hashes
        $user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT);

        // E-Mail Verifizierung-Hash generieren
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

        // Bestätigung-E-Mail senden
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
    private static function validateUserInputs(string $user_name, string $user_email, string $user_password_new, string $user_password_repeat): bool
    {
        if (empty($user_name) || empty($user_email) || empty($user_password_new)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_MISSING_FIELDS'));
            return false;
        }

        if (strlen($user_password_new) < 8) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_TOO_SHORT'));
            return false;
        }

        if (UserModel::doesUsernameAlreadyExist($user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_ALREADY_TAKEN'));
            return false;
        }

        if (UserModel::doesEmailAlreadyExist($user_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_ALREADY_TAKEN'));
            return false;
        }

                if ($user_password_new !== $user_password_repeat) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_REPEAT_WRONG'));
            return false;
        }

        return true;
    }

    /**
     * Schreibt den neuen Benutzer in die Datenbank.
     */
    private static function writeNewUserToDatabase(string $user_name, string $user_password_hash, string $user_email, string $user_activation_hash): bool
    {
        try {
            $db = DatabaseFactory::getFactory()->getConnection();

            // Benutzer in die Datenbank schreiben
            $sql = "INSERT INTO users (user_name, password_hash, email, created_at, role, user_active, user_activation_hash) 
                VALUES (:name, :password_hash, :email, NOW(), 'Kunde', 0, :activation_hash)";

            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                ':name' => $user_name,
                ':password_hash' => $user_password_hash,
                ':email' => $user_email,
                ':activation_hash' => $user_activation_hash
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
     * Sendet die Bestätigung-E-Mail.
     * @throws Exception
     */
    private static function sendVerificationEmail(int $user_id, string $user_email, string $user_activation_hash): bool
    {
        $verification_link = Config::get('URL') . "register/verify/" . urlencode($user_id) . "/" . urlencode($user_activation_hash);

        $body = '
            <html lang="de">
                    <head>
                        <meta charset="utf-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <style>
                            @font-face {
                                font-family: "Ghost";
                                src: url("https://respawngaming.at/fonts/Ghost/Ghost.woff2") format("woff2");                            
                            }
                            body { font-family: "Ghost", sans-serif; margin: 0; padding: 0; background-color: #f2f4f8; }
                            .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
                            .header { background: linear-gradient(135deg, #1e3c72, #2a5298); padding: 20px; text-align: center; }
                            .header img { max-width: 150px; margin-bottom: 10px; border-radius: 50%; }
                            .header h1 { margin: 0; color: #fff; font-size: 26px; }
                            .content { padding: 20px; color: #333; line-height: 1.6; }
                            .content h1 { font-size: 22px; margin-bottom: 10px; color: #333; }
                            .content h2 { font-size: 20px; margin-bottom: 8px; color: #333; }
                            .content h3 { font-size: 18px; margin-bottom: 6px; color: #333; }
                            .content p { font-size: 16px; margin-bottom: 12px; }
                            .content ul, .content ol { margin: 12px 0; padding-left: 20px; }
                            .content li { margin-bottom: 8px; }
                            .content .signature { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; font-style: italic; color: #666; }
                            .footer { background: #f2f2f2; padding: 15px; text-align: center; font-size: 12px; color: #777; }
                            .footer a { color: #2a5298; text-decoration: none; }
                            .footer a:hover { text-decoration: underline; }
                        </style>
                        <title>Respawn Gaming</title>
                    </head>
                    <body>
                        <div class="container">
                            <div class="header">
                                <img src="https://respawngaming.at/image/RG_MainLogo.png" alt="Respawn Gaming Logo">
                                <h1>Respawn Gaming</h1>
                            </div>
                            <div class="content">
                                <h1>E-Mail-Bestätigung</h1>
                                <p>Hallo,</p>
                                <p>Vielen Dank für deine Registrierung bei Respawn Gaming. Bitte klicke auf den folgenden Link, um deine E-Mail-Adresse zu bestätigen:</p>
                                <p><a href="' . $verification_link . '">Verify Mail</a></p>
                                <div class="signature">
                                    <p>Freundliche Grüße</p>
                                    <p>Ihr Respawn Gaming Team</p>
                                </div>
                            </div>
                            <div class="footer">
                                <p>© 2025 Respawn Gaming. Alle Rechte vorbehalten.</p>
                                <p><a href="https://www.respawngaming.at">www.respawngaming.at</a></p>
                            </div>
                        </div>
                    </body>
                </html>
        ';

        $mail = new Mail();
        return $mail->sendMail($user_email, "no-reply@respawngaming.at", "No-Reply", "E-Mail-Bestätigung", $body);
    }

    /**
     * Löscht einen Benutzer, falls die Registrierung fehlschlägt.
     */
    private static function rollbackRegistrationByUserId(int $user_id): void
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $user_id]);
    }

    /**
     * Bestätigt den neuen Benutzer über den E-Mail-Link.
     */
    public static function verifyNewUser(int $user_id, string $user_activation_verification_code): bool
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("UPDATE users SET user_active = 1, user_activation_hash = NULL WHERE user_id = :id AND user_activation_hash = :activation_hash LIMIT 1");
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
