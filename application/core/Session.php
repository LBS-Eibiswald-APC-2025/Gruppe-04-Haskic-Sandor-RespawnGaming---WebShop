<?php

use Random\RandomException;

class Session
{
    public static function init(): void
    {
        if (session_id() == '') {
            session_start();
        }
    }

    /**
     * @throws RandomException
     */
    public static function regenerateCSRFToken(): void
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function set($key, $value, $filter = null): void
    {
        if ($filter !== null) {
            $_SESSION[$key][$filter] = $value;
        } else {
            $_SESSION[$key] = $value;
        }
    }

    public static function get($key, $filter = null)
    {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            if ($filter !== null && isset($value[$filter])) {
                return Filter::XSSFilter($value[$filter]);
            }
            return Filter::XSSFilter($value);
        }
        return null;
    }

    public static function add($key, $value): void
    {
        $_SESSION[$key][] = $value;
    }

    public static function remove($key, $filter = null): void
    {
        if ($filter !== null) {
            unset($_SESSION[$key][$filter]);
        } else {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy(): void
    {
        session_destroy();
    }

    public static function updateSessionId($userId, $sessionId = null): void
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE users SET session_id = :session_id WHERE user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute([
            ':session_id' => $sessionId,
            ':user_id'    => $userId
        ]);
    }

    public static function isConcurrentSessionExists(): bool
    {
        $session_id = session_id();
        $userId     = self::get('user_id');

        if ($userId && $session_id) {
            $database = DatabaseFactory::getFactory()->getConnection();
            $sql = "SELECT session_id FROM users WHERE user_id = :user_id LIMIT 1";
            $query = $database->prepare($sql);
            $query->execute([":user_id" => $userId]);
            $result = $query->fetch();
            $userSessionId = $result ? $result->session_id : null;

            return $session_id !== $userSessionId;
        }

        return false;
    }

    public static function userIsLoggedIn(): bool
    {
        return (bool) self::get('user_logged_in');
    }

    public static function validateCSRFToken(mixed $csrf_token): bool
    {
        if (!isset($_SESSION['csrf_token']) || empty($csrf_token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $csrf_token);
    }
}
