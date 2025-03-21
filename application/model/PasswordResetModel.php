<?php

class PasswordResetModel
{
    public static function requestPasswordReset(mixed $user_input, mixed $captcha): stdClass
    {
        $request = new stdClass();
        $request->feedback = 'success';

        $validateCaptcha = CaptchaModel::checkCaptcha($captcha);

        if (!$validateCaptcha) {
            Session::add('feedback_negative', Text::get('FEEDBACK_CAPTCHA_WRONG'));
            $request->feedback = 'error';
            $request->error = 'Captcha';
            return $request;
        }

        // Überprüfen, ob Benutzername oder E-Mail-Adresse eingegeben wurde
        if (empty($user_input)) {
            $request->feedback = 'error';
            $request->error = 'Benutzername oder E-Mail-Adresse';
            return $request;
        }

        // Überprüfen, ob Benutzername oder E-Mail-Adresse in der Datenbank existiert
        $result = UserModel::getUserDataByUserNameOrEmail($user_input);
        if (!$result) {
            $request->feedback = 'error';
            $request->error = 'Benutzername oder E-Mail-Adresse';
            return $request;
        }

        $user_email = $result->email;
        $user_name = $result->user_name;

        // generate passwort reset token and save to db
        $token = self::generatePasswordResetToken();
        if (!self::savePasswordResetToken($user_name, $token)) {
            $request->feedback = 'error';
            $request->error = 'Allgemeiner Fehler';
            return $request;
        }


        // Senden der E-Mail mit dem Link zum Zurücksetzen des Passworts
        if (!self::sendPasswordResetMail($user_name, $token, $user_email)) {
            $request->feedback = 'error';
            $request->error = 'Allgemeiner Fehler';
            return $request;
        }

        return $request;
    }

    public static function sendPasswordResetMail($user_name, $token, $user_email)
    {
        try {
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
                                .header { background: linear-gradient(135deg, #721e1e, #982a2a); padding: 20px; text-align: center; }
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
                                    <h1>Passwort Reset</h1>
                                    <p>Hallo ' .$user_name.',</p>
                                    <p>Sie haben gerade eine Passwortrücksetzung beantragt. Klicken Sie bitte auf dem nachstehen Link um dies zu vervollständigen.</p>
                                    <br>
                                    <a href="https://respawngaming.at/login/verifyPasswordReset/' . $token . '">Passwort zurücksetzen</a>
                                    <hr>
                                    <p>Falls diese Änderungen nicht nach deinen Wünschen entstanden sind, wenden dich bitte an unseren Support unter <a href="mailto:support@respawngaming.at">Support</a></p>
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
            $mail->sendMail($user_email, "no-reply@respawngaming.at", "No-Reply", "(WICHTIG) Passwort Reset", $body);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private static function generatePasswordResetToken(): string
    {
        return sha1(uniqid(mt_rand(), true));
    }

    private static function savePasswordResetToken($user_name, string $token): bool
    {
        try {
            $database = DatabaseFactory::getFactory()->getConnection();

            $sql = "UPDATE users SET password_reset_token = :token WHERE user_name = :user_name";
            $query = $database->prepare($sql);
            $query->execute(array(':token' => $token, ':user_name' => $user_name));

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function verifyPasswordReset($verification_code)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name FROM users WHERE password_reset_token = :token";
        $query = $database->prepare($sql);
        $query->execute(array(':token' => $verification_code));

        if ($query->rowCount() == 0) {
            return false;
        }

        $result = $query->fetch();

        return true;
    }

    public static function setNewPassword(mixed $hash, mixed $password, mixed $password_repeat)
    {
        if (empty($hash) || empty($password) || empty($password_repeat)) {
            Session::add('feedback_negative', 'Es fehlen Informationen.');
            return false;
        }

        if ($password !== $password_repeat) {
            Session::add('feedback_negative', 'Die Passwörter stimmen nicht überein.');
            return false;
        }

        if (strlen($password) < 6) {
            Session::add('feedback_negative', 'Das Passwort muss mindestens 6 Zeichen lang sein.');
            return false;
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE users SET password_hash = :user_password_hash, password_reset_token = NULL WHERE password_reset_token = :token";
        $query = $database->prepare($sql);
        $query->execute(array(':user_password_hash' => $password_hash, ':token' => $hash));

        if ($query->rowCount() == 1) {
            return true;
        }

        Session::add('feedback_negative', 'Passwort konnte nicht geändert werden.');
        return false;
    }
}
