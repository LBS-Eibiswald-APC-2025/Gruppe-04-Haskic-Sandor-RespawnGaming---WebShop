<?php

/**
 * Class Redirect
 */
class Redirect
{
    /**
     * To the last visited page before user logged in (useful when people are on a certain page inside your application
     * and then want to log in (to edit or comment something for example) and don't to be redirected to the main page).
     *
     * @param $path string
     */
    public static function toPreviousViewedPageAfterLogin(string $path): void
    {
        header('location: https://' . $_SERVER['HTTP_HOST'] . '/' . $path);
    }

    /**
     * To the homepage
     */
    public static function home(): void
    {
        header("location: https://respawngaming.at/");
    }

    /**
     * To the defined page, uses a relative path (like "user/profile")
     *
     * @param $path string
     */
    public static function to(string $path): void
    {
        header("location: " . Config::get('URL') . $path);
    }
}
