<?php

use PHPMailer\PHPMailer\PHPMailer;
use Random\RandomException;

require __DIR__ . '/../../vendor/autoload.php';

class PasswordResetModel
{
    private UserModel $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public static function getUserByUserNameOrEmail(string $input): ?object
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT user_id, user_name, email, user_password_reset_hash FROM users WHERE user_name = :input OR email = :input LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':input' => $input]);
        return $query->fetch() ?: null; //Erzwingen das null zurückgegeben wird, wenn kein Nutzer gefunden wird
    }

    public static function storeResetToken(int $user_id, string $token, int $timestamp): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE users SET user_password_reset_hash = :token, user_password_reset_timestamp = :timestamp WHERE user_id = :user_id";
        $query = $database->prepare($sql);
        return $query->execute([':token' => $token, ':timestamp' => $timestamp, ':user_id' => $user_id]);
    }

    public static function sendPasswordResetMail(string $user_name, string $token, string $email): bool
    {
        $resetLink = Config::get('URL') . "resetPassword?user=" . urlencode($user_name) . "&token=" . urlencode($token);
        $body = "Hier ist dein Passwort-Reset-Link: $resetLink";

        $mail = new PHPMailer(true);
        try {
            $mail->setFrom('no-reply@respawngaming.at', 'Respawn Gaming');
            $mail->addAddress($email);
            $mail->Subject = 'Passwort zurücksetzen';
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
                error_log("Password reset mail failed: " . $mail->ErrorInfo);
                return false;
            }
        }


    public static function updatePassword(int $user_id, string $hashedPassword): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE users SET password_hash = :password_hash, user_password_reset_hash = NULL, user_password_reset_timestamp = NULL WHERE user_id = :user_id";
        $query = $database->prepare($sql);
        return $query->execute([':password_hash' => $hashedPassword, ':user_id' => $user_id]);
    }

    public static function setNewPassword(mixed $user_name, mixed $user_password_reset_hash, mixed $user_password_new, mixed $user_password_repeat): bool
    {
        // Überprüfe, ob beide Passworteingaben übereinstimmen
        if ($user_password_new !== $user_password_repeat) {
            Session::add('feedback_negative', 'Die neuen Passwörter stimmen nicht überein.');
            return false;
        }

        // Neuen Passwort-Hash generieren
        $hashedPassword = password_hash($user_password_new, PASSWORD_DEFAULT);

        // Datenbankverbindung holen
        $database = DatabaseFactory::getFactory()->getConnection();

        // SQL-Abfrage, um das Passwort zu aktualisieren und den Reset-Token zu löschen
        $sql = "UPDATE users 
            SET password_hash = :password_hash, 
                user_password_reset_hash = NULL, 
                user_password_reset_timestamp = NULL 
            WHERE user_name = :user_name 
              AND user_password_reset_hash = :hash";

        $stmt = $database->prepare($sql);
        return $stmt->execute([
            ':password_hash' => $hashedPassword,
            ':user_name'     => $user_name,
            ':hash'          => $user_password_reset_hash
        ]);
    }

    public static function verifyPasswordReset($user_name, $verification_code): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $currentTime = time();
        $sql = "SELECT user_id FROM users 
            WHERE user_name = :user_name 
              AND user_password_reset_hash = :verification_code 
              AND user_password_reset_timestamp > :currentTime
            LIMIT 1";
        $stmt = $database->prepare($sql);
        $stmt->execute([
            ':user_name'         => $user_name,
            ':verification_code' => $verification_code,
            ':currentTime'       => $currentTime
        ]);

        // Falls ein Datensatz gefunden wurde, ist der Reset gültig
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }


    /**
     * @throws RandomException|\PHPMailer\PHPMailer\Exception
     */
    public function requestPasswordReset(): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];

            // Überprüfe, ob die E-Mail in der Datenbank existiert
            $user = $this->userModel->findByEmail($email);
            if ($user) {
                // Token generieren und speichern (z. B. in der Datenbank, Ablauf in 1 Stunde)
                $token = bin2hex(random_bytes(16));
                $expiry = time() + 3600; // 1 Stunde Gültigkeit
                $this->userModel->saveResetToken($user['id'], $token, $expiry);

                // Reset-Link und E-Mail-Body
                $resetLink = "https://deinedomain.de/password/reset?token=" . $token;
                $body = "Hallo " . htmlspecialchars($user['name']) . ",<br><br>" .
                    "Um dein Passwort zurückzusetzen, klicke bitte auf folgenden Link: <a href='{$resetLink}'>Passwort zurücksetzen</a>.<br><br>" .
                    "Falls du diese Anfrage nicht gestellt hast, ignoriere bitte diese E-Mail.";

                $mail = new Mail();
                $subject = "Passwort zurücksetzen";

                if ($mail->sendMail($email, "noreply@deinedomain.de", "Dein Projektname", $subject, $body)) {
                    echo "Eine E-Mail zum Zurücksetzen des Passworts wurde an deine Adresse gesendet.";
                } else {
                    echo "Fehler beim Senden der E-Mail: " . $mail->error;
                }
            } else {
                echo "Kein Benutzer mit dieser E-Mail gefunden.";
            }
        }
    }
}
