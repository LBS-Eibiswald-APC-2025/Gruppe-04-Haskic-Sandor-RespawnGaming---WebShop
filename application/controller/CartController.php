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
        if (!GameModel::getGameById($game_id)) {
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

    /**
     * Führt den Checkout durch und leitet nach erfolgreicher Bestellung weiter.
     */
    #[NoReturn]
    public function checkout(): void
    {
        $userid = Session::get('user_id') ?? null;
        if (!$userid) {
            Session::add('feedback_negative', 'Bitte melde dich an.');
            header('Location: /login');
            exit();
        }

        if (!CartModel::hasItems()) {
            Session::add('feedback_negative', 'Dein Warenkorb ist leer.');
            header('Location: /cart');
            exit();
        }

        $orderId = CartModel::checkout($userid);
        if ($orderId) {
            header('Location: /order/confirmation?orderId=' . $orderId);
        } else {
            Session::add('feedback_negative', 'Checkout fehlgeschlagen.');
            header('Location: /cart');
        }
        exit();
    }

    /**
     * Ruft die Methode zum Bereinigen alter Warenkörbe auf.
     */
    public function clearOldCarts(): void
    {
        CartModel::clearOldCarts();
    }
}
