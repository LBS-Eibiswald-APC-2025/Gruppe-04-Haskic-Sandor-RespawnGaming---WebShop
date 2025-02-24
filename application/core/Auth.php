<?php

class Auth
{
    public static function checkAuthentication(): void
    {
        Session::init();

        if (!Session::userIsLoggedIn()) {
            Session::destroy();
            header('location: ' . Config::get('URL') . 'login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
    }

    public static function checkAdminAuthentication(): bool
    {
        Session::init();

        // Prüfen, ob eingeloggt und ob user_account_type = Admin
        if (!Session::userIsLoggedIn() || Session::get("user_data")['user_account_type'] !== 'Admin') {
            return false;
        }

        return true;
    }

    public static function checkSessionConcurrency(): void
    {
        if (Session::userIsLoggedIn()) {
            if (Session::isConcurrentSessionExists()) {
                LoginModel::logout();
                Redirect::home();
                exit();
            }
        }
    }
}
