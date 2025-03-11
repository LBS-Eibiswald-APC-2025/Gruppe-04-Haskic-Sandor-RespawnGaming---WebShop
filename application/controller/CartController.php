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

    // Verarbeitet den Zahlungsvorgang
    public function processPayment(): void
    {
        // 1. Zahlungsdaten aus dem Formular auslesen
        $paymentEmail = Request::post('payment_email');

        // 2. Fiktive Zahlung simulieren:
        // Hier könntest du eine einfache Logik einbauen, z.B.
        // wenn die E-Mail gültig ist, gilt die Zahlung als erfolgreich.
        $paymentSuccess = filter_var($paymentEmail, FILTER_VALIDATE_EMAIL);

        // 3. Falls die Zahlung erfolgreich war:
        if ($paymentSuccess) {
            // Bestellung in der Datenbank anlegen, Warenkorb leeren
            $orderId = CartModel::checkout(Session::get('user_id'));

            if ($orderId) {
                // 4. PDF-Rechnung generieren (z.B. OrderModel::createInvoicePdf($orderId))
                // Hier wird vorausgesetzt, dass du eine OrderModel-Klasse erstellst.
                $pdfFilePath = OrderModel::createInvoicePdf($orderId);

                // 5. PDF per Mail an den Kunden senden
                OrderModel::sendInvoiceByEmail($pdfFilePath, $paymentEmail);

                // 6. SweetAlert-Feedback (über Session oder direkt via Redirect)
                Session::add('feedback_sweetalert_success', 'Zahlung erfolgreich! Deine Rechnung wurde per E-Mail verschickt.');

                // Weiterleitung (z. B. zu einer Erfolgsseite)
                Redirect::to('cart/checkoutSuccess');
                return;
            } else {
                // Wenn Bestellung fehlschlägt
                Session::add('feedback_sweetalert_error', 'Bestellung fehlgeschlagen. Bitte versuche es erneut.');
            }
        } else {
            Session::add('feedback_sweetalert_error', 'Zahlung fehlgeschlagen. Bitte überprüfe deine Zahlungsdaten.');
        }

        // Bei Fehler: Zurück zur Checkout-Seite
        Redirect::to('cart/checkout');
    }

    // Erfolgsseite nach dem Check-out
    public function checkoutSuccess(): void
    {
        $this->View->render('cart/checkoutSuccess');
    }


    /**
     * Ruft die Methode zum Bereinigen alter Warenkörbe auf.
     */
    public function clearOldCarts(): void
    {
        CartModel::clearOldCarts();
    }


}
