<?php

/**
 * This is the "base controller class". All other "real" controllers extend this class.
 * Whenever a controller is created, we also
 * 1. initialize a session
 * 2. check if the user is not logged in anymore (session timeout) but has a cookie
 */
class Controller
{
    /** @var View View The view object */
    public $View;

    /** @var array Die Parameter aus der URL */
    protected array $parameters = [];

    /**
     * Construct the (base) controller. This passiert, wenn ein echter Controller instanziiert wird.
     */
    public function __construct($parameters = [])
    {
        // always initialize a session
        Session::init();

        // check session concurrency
        Auth::checkSessionConcurrency();

        // User ist nicht eingeloggt, hat aber ein Remember-Me-Cookie? Dann einloggen.
        if (!Session::userIsLoggedIn() AND Request::cookie('remember_me')) {
            header('location: ' . Config::get('URL') . 'login/loginWithCookie');
        }

        // View-Objekt erstellen
        $this->View = new View();

        // Die Parameter speichern
        $this->parameters = $parameters;
    }
}

