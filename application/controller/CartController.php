<?php



use JetBrains\PhpStorm\NoReturn;

class CartController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Zeigt den Warenkorb an.
     */
    public function index(): void
    {
        // Warenkorbeinträge via Session abrufen.
        $cartItems = CartModel::getCartItemsWithDetails();

        $this->View->render('cart/index', ['cartItems' => $cartItems]);
    }

    /**
     * Fügt ein Spiel zum Warenkorb hinzu.
     * Das Spiel wird per POST übermittelt.
     */
    #[NoReturn]
    public function addToCart(): void
    {
        // Spiel-ID aus den POST-Daten auslesen.
        if (!isset($_POST['game_id'])) {
            Session::add('feedback_negative', 'Kein Spiel angegeben.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $game_id = (int)$_POST['game_id'];

        // Vorab prüfen, ob das Spiel existiert.
        if (!GamesModel::getGameById($game_id)) {
            Session::add('feedback_negative', 'Spiel nicht gefunden.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Spiel in den Session-Warenkorb legen.
        CartModel::addToCart($game_id);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    /**
     * Entfernt ein Spiel aus dem Warenkorb.
     * Die Spiel-ID wird per POST übermittelt.
     */
    #[NoReturn]
    public function removeFromCart(): void
    {
        if (!isset($_POST['game_id'])) {
            Session::add('feedback_negative', 'Kein Spiel angegeben.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $game_id = (int)$_POST['game_id'];

        // Prüfen, ob das Spiel im Warenkorb ist.
        if (!CartModel::isGameInCart($game_id)) {
            Session::add('feedback_negative', 'Spiel nicht im Warenkorb.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Spiel entfernen.
        CartModel::removeFromCart($game_id);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Zeigt die Checkout-Seite an
    public function checkout(): void
    {
        if (!Session::userIsLoggedIn()) {
            Session::add('feedback_negative', 'Bitte loggen Sie sich ein, um fortzufahren.');
            Redirect::to('login/index');
            exit();
        }


        $cartItems = CartModel::getCartItemsWithDetails();
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            // Falls mit Array gearbeitet wird
            $totalPrice += $item['price'] * $item['quantity'];
        }

        // Übergib die Daten an die View
        $this->View->render('cart/checkout', [
            'cartItems'  => $cartItems,
            'totalPrice' => $totalPrice
        ]);
    }

    /**
     * Verarbeitet die Bestellung und leitet zur PayPal-Zahlung weiter
     */
    #[NoReturn] public function process(): void
    {
        // Prüfen, ob der Benutzer eingeloggt ist
        if (!Session::userIsLoggedIn()) {
            Session::add('feedback_negative', 'Bitte loggen Sie sich ein, um fortzufahren.');
            Redirect::to('login/index');
            exit();
        }

        // Prüfen, ob Warenkorb leer ist
        if (!CartModel::hasItems()) {
            Session::add('feedback_negative', 'Ihr Warenkorb ist leer.');
            Redirect::to('cart/index');
            exit();
        }

        // PayPal API initialisierten
        $paypal = new PayPalService();

        // Warenkorbdaten abrufen
        $cartItems = CartModel::getCartItemsWithDetails();
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        // Bestellungsdetails
        $orderDetails = [
            'user_id' => Session::get('user_id'),
            'items' => $cartItems,
            'total' => $totalPrice
        ];

        // In Session speichern für späteren Zugriff
        Session::set('pending_order', $orderDetails);

        // PayPal Zahlung erstellen und Benutzer zur PayPal-Seite weiterleiten
        $response = $paypal->createPayment($totalPrice, Config::get('URL') . 'cart/complete', Config::get('URL') . 'cart/cancel');

        if ($response) {
            // Zu PayPal weiterleiten
            header('Location: ' . $response);
            exit(); // Wichtig: Nach der Weiterleitung sofort beenden
        } else {
            // Fehler bei PayPal
            Session::add('feedback_negative', 'Es gab ein Problem bei der Verbindung mit PayPal. Bitte versuchen Sie es später erneut.');
            ob_end_clean(); // Leere den Puffer
            Redirect::to('cart/checkout');
            exit(); // Sofort beenden
        }
    }

    /**
     * Wird aufgerufen, wenn die PayPal-Zahlung erfolgreich war
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function complete(): void
    {
        error_reporting(0);

        // Prüfen, ob der Benutzer eingeloggt ist
        if (!Session::userIsLoggedIn()) {
            Session::add('feedback_negative', 'Bitte loggen Sie sich ein, um fortzufahren.');
            Redirect::to('login/index');
            exit();
        }

        // PayPal-Parameter prüfen
        $token = $_GET['token'] ?? null;
        $payerId = $_GET['PayerID'] ?? null;

        if (!$token || !$payerId) {
            Session::add('feedback_negative', 'Ungültige Zahlungsdetails erhalten.');
            Redirect::to('cart/checkout');
            exit();
        }

        // Bestelldetails aus der Session holen
        $orderDetails = Session::get('pending_order');

        if (!$orderDetails) {
            Session::add('feedback_negative', 'Bestelldetails nicht gefunden.');
            Redirect::to('cart/index');
            exit();
        }

        // PayPal API initialisieren und Zahlung ausführen
        $paypal = new PayPalService();
        $result = $paypal->executePayment($token, $payerId);

        if ($result) {
            // Bestellung in Datenbank speichern
            $orderId = CartModel::checkout((int)$orderDetails['user_id']);

            if ($orderId) {
                // PDF-Rechnung erstellen
                $pdfPath = CartModel::createInvoicePdf($orderId);

                // Rechnung per E-Mail senden
                $user = UserModel::getUserDataByUserID(Session::get('user_id'));

                // var_dump($user);

                if ($user && isset($user->email)) {
                    CartModel::sendInvoiceByEmail($orderId, $pdfPath, $user->email);
                }

                // Bestellung aus der Session entfernen
                Session::remove('pending_order');

                // Erfolgsseite anzeigen
                Session::add('feedback_positive', 'Vielen Dank für Ihre Bestellung! Eine Bestätigung wurde an Ihre E-Mail-Adresse gesendet.');
                $this->View->render('cart/success', [
                    'orderId' => $orderId,
                    'orderDetails' => $orderDetails
                ]);
            } else {
                Session::add('feedback_negative', 'Fehler beim Speichern der Bestellung.');
                Redirect::to('cart/checkout');
            }
        } else {
            Session::add('feedback_negative', 'Fehler bei der Verarbeitung der Zahlung.');
            Redirect::to('cart/checkout');
        }
    }

    /**
     * Wird aufgerufen, wenn die PayPal-Zahlung abgebrochen wurde
     */
    public function cancel(): void
    {
        Session::add('feedback_negative', 'Die Zahlung wurde abgebrochen.');
        Redirect::to('cart/checkout');
    }

    /**
     * Ruft die Methode zum Bereinigen alter Warenkörbe auf.
     */
    public function clearOldCarts(): void
    {
        CartModel::clearOldCarts();
    }
}
