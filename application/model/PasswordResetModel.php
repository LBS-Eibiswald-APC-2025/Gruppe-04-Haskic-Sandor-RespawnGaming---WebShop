<?php

class PasswordResetModel
{
    public static function getUserByUserNameOrEmail(string $input): ?object
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT user_id, user_name, email, user_password_reset_hash FROM users WHERE user_name = :input OR email = :input LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':input' => $input]);
        return $query->fetch();
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

        return mail($email, "Passwort zurücksetzen", $body, "From: no-reply@respawngaming.at");
    }

    public static function updatePassword(int $user_id, string $hashedPassword): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE users SET password_hash = :password_hash, user_password_reset_hash = NULL, user_password_reset_timestamp = NULL WHERE user_id = :user_id";
        $query = $database->prepare($sql);
        return $query->execute([':password_hash' => $hashedPassword, ':user_id' => $user_id]);
    }

    public static function requestPasswordReset(mixed $user_input, mixed $captcha)
    {
    }
}
