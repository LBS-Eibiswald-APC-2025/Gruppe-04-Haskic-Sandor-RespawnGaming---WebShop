<?php

/**
 * Class Error
 * This controller simply contains some methods that can be used to give proper feedback in certain error scenarios,
 * like a proper 404 response with an additional HTML page behind when something does not exist.
 */
class ErrorController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Verwenden Sie dies, wenn etwas nicht gefunden wird. Es liefert eine korrekte 404-Header-Antwort sowie eine
     * normale Seite zurück (auf der Sie eine gut gestaltete Fehlermeldung oder etwas anderes, das für Ihre Benutzer nützlicher ist,
     * anzeigen können). Dies können Sie in Aktion in /core/Application.php → __construct sehen.
     */
    public function error404(): void
    {
        header('HTTP/1.0 404 Not Found', true, 404);
        $this->View->render('error/404');
    }
}
