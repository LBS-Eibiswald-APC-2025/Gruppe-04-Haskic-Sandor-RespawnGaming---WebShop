<?php

use Fpdf\Fpdf;

class CartModel
{
    /**
     * Fügt ein Spiel zum Warenkorb (in der Session) hinzu.
     * Existiert das Spiel bereits, wird die Menge erhöht.
     *
     * @param int $game_id ID des hinzuzufügenden Spiels.
     */
    public static function addToCart(int $game_id): void
    {
        // Wenn noch kein Warenkorb existiert, anlegen.
        if (Session::get('cart') === null || !is_array(Session::get('cart'))) {
            Session::set('cart', []);
        }

        // Falls das Spiel schon im Warenkorb ist, Menge erhöhen.
        if (isset(Session::get('cart')[$game_id])) {
            Session::set('cart', Session::get('cart')[$game_id] + 1, $game_id);
        } else {
            Session::set('cart', 1, $game_id);
        }
    }

    /**
     * Entfernt ein Spiel aus dem Warenkorb (Session).
     *
     * @param int $game_id ID des zu entfernenden Spiels.
     */
    public static function removeFromCart(int $game_id): void
    {
        if (isset(Session::get('cart')[$game_id])) {
            if (Session::get('cart')[$game_id] > 1) {
                Session::set('cart', Session::get('cart')[$game_id] - 1, $game_id);
            } else {
                Session::remove('cart', $game_id);
            }
        }
    }

    /**
     * Holt alle Warenkorbeinträge mitsamt Spieldetails.
     * Hierzu werden die Spiel-IDs aus der Session gelesen und
     * die Details per GamesModel::getGameById geholt.
     *
     * @return array Enthält Objekte mit Spielinformationen und der Menge.
     */

    public static function getCartItemsWithDetails(): array
    {
        $cartItems = [];
        if (empty(Session::get('cart'))) {
            return $cartItems;
        }

        // Über jeden Eintrag im Session-Warenkorb iterieren.
        foreach (Session::get('cart') as $game_id => $quantity) {
            // Spiel anhand der ID holen.
            $game = GamesModel::getGameById($game_id);
            if ($game) {
                // Menge als Eigenschaft hinzufügen, sodass die View sie nutzen kann.
                $game['quantity'] = $quantity;
                $cartItems[] = $game;
            }
        }
        return $cartItems;
    }

    /**
     * Führt den Check-out durch:
     * - Berechnet den Gesamtpreis
     * - Legt die Bestellung in der Datenbank an
     * - Fügt die Bestellpositionen hinzu
     * - Leert den Session-Warenkorb
     *
     * @param int $user_id ID des Benutzers, der bestellt.
     * @return int|null ID der Bestellung oder null bei Fehler.
     */
    public static function checkout(int $user_id): ?int
    {
        $cartItems = self::getCartItemsWithDetails();
        $totalPrice = 0.0;

        foreach ($cartItems as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        if ($totalPrice <= 0) {
            return null;
        }

        // Bestellung in der Datenbank anlegen.
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO orders (user_id, total_price, status) VALUES (:user_id, :total_price, 'pending')";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id, ':total_price' => $totalPrice]);
        $orderId = $database->lastInsertId();

        // Für jedes Spiel einen Eintrag in der Bestellpositionen-Tabelle anlegen.
        foreach ($cartItems as $item) {
            $sql = "INSERT INTO order_items (order_id, game_id, price_at_purchase) 
                    VALUES (:order_id, :game_id, :price)";
            $query = $database->prepare($sql);
            $query->execute([
                ':order_id' => $orderId,
                ':game_id' => $item['id'],
                ':price' => $item['price']
            ]);
        }

        // Warenkorb in der Session leeren.
        Session::remove('cart');
        return $orderId;
    }

    /**
     * Prüft, ob ein bestimmtes Spiel im Warenkorb enthalten ist.
     *
     * @param int $game_id ID des zu prüfenden Spiels.
     * @return bool true, wenn vorhanden.
     */
    public static function isGameInCart(int $game_id): bool
    {
        return Session::get('cart', $game_id) !== null;
    }

    /**
     * Überprüft, ob der Warenkorb Einträge enthält.
     *
     * @return bool true, wenn mindestens ein Eintrag vorhanden ist.
     */
    public static function hasItems(): bool
    {
        return !empty(Session::get('cart'));
    }

    /**
     * Alte Warenkörbe bereinigen – mit Session ist das nicht notwendig,
     * deshalb bleibt diese Methode leer.
     */
    public static function clearOldCarts(): void
    {
        // Keine Aktion nötig, da Session-Warenkorb nicht zeitabhängig ist.
    }

    public static function createInvoicePdf(int $orderId): string
    {
        // Bestelldaten aus der DB holen
        $order = self::getOrderById($orderId);
        $orderItems = self::getOrderItems($orderId);
        $user = Session::get('user_name');

        // Verzeichnis erstellen, falls es nicht existiert
        $invoiceDir = __DIR__ . '/../../public/invoices';
        if (!is_dir($invoiceDir)) {
            mkdir($invoiceDir, 0755, true);
        }

        // Absoluten Dateipfad verwenden
        $fileName = 'invoice_' . $orderId . '.pdf';
        $filePath = $invoiceDir . '/' . $fileName;

        // PDF erzeugen - Standard-Font verwenden
        $pdf = new FPDF();
        $pdf->AddPage();

        // Kopfzeile
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Respawn Gaming', 0, 0, 'R');
        $pdf->Ln(15);

        // Benutzer und Rechnungsdaten
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(95, 8, 'Benutzername: ' . $user, 0, 0, 'L');
        $pdf->Cell(95, 8, 'Bestellnummer: ' . $order->id, 0, 1, 'R');
        $pdf->Cell(95, 8, 'Datum: ' . date('d.m.Y', strtotime($order->created_at)), 0, 1, 'L');

        // Trennlinie
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        // Überschrift
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'RECHNUNG', 0, 1, 'C');
        $pdf->Ln(5);

        // Tabellenkopf
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(190, 8, 'Spiel', 1, 1, 'L', true);

        // Artikel auflisten
        $pdf->SetFont('Arial', '', 10);

        // Ein Array erstellen, um die Spiele zu zählen und zu gruppieren
        $groupedItems = [];

        foreach ($orderItems as $item) {
            if (!isset($groupedItems[$item->title])) {
                $groupedItems[$item->title] = [
                    'count' => 1,
                    'price' => $item->price,
                    'total' => $item->price
                ];
            } else {
                $groupedItems[$item->title]['count']++;
                $groupedItems[$item->title]['total'] += $item->price;
            }
        }

        // Gruppierte Artikel anzeigen
        foreach ($groupedItems as $title => $details) {
            // Falls zu wenig Platz auf Seite, neue Seite anfangen
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();

                // Tabellenkopf auf neuer Seite wiederholen
                $pdf->SetFillColor(200, 200, 200);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(100, 8, 'Spiel', 1, 0, 'L', true);
                $pdf->Cell(30, 8, 'Anzahl', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Preis', 1, 0, 'R', true);
                $pdf->Cell(30, 8, 'Summe', 1, 1, 'R', true);
                $pdf->SetFont('Arial', '', 10);
            }

            // Produkte mit Anzahl, Einzelpreis und Summe anzeigen
            $pdf->Cell(100, 8, $title, 1, 0, 'L');
            $pdf->Cell(30, 8, $details['count'], 1, 0, 'C');
            $pdf->Cell(30, 8, number_format($details['price'], 2, ',', '.') . ' EUR', 1, 0, 'R');
            $pdf->Cell(30, 8, number_format($details['total'], 2, ',', '.') . ' EUR', 1, 1, 'R');
        }

        // Gesamtsumme - hier direkt den Wert aus der Datenbank verwenden
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(160, 10, 'Gesamtsumme:', 0, 0, 'R');
        $pdf->Cell(30, 10, number_format($order->total_price, 2, ',', '.') . ' EUR', 0, 1, 'R');

        // Status
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(160, 8, 'Status:', 0, 0, 'R');
        $pdf->Cell(30, 8, $order->status, 0, 1, 'R');

        // Dankesnachricht mit ASCII-Zeichen anstelle von UTF-8
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, 'Vielen Dank für deinen Einkauf bei Respawn Gaming!', 0, 1, 'C');

        // PDF speichern
        $pdf->Output('F', $filePath);

        // Relativen Pfad für Webzugriff zurückgeben
        return 'invoices/' . $fileName;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function sendInvoiceByEmail(int $orderId, string $pdfFilePath, string $toEmail): bool
    {
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
                            .header { background: linear-gradient(135deg, #1e3c72, #2a5298); padding: 20px; text-align: center; }
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
                                <h1>Ihre Bestellung</h1>
                                <p>Hallo,</p>
                                <p>Anbei erhalten Sie Ihre Rechnung für Ihre Bestellung bei Respawn Gaming.</p>
                                <h2>Bestellnummer: ' . $orderId . '</h2>
                                <h3>Bestellpositionen</h3>
                                <ul>';

        foreach (self::getOrderItems($orderId) as $item) {
            $body .= '<li>' . $item->title . ' - ' . $item->quantity . ' x ' . number_format($item->price, 2) . ' €</li>';
        }

        $body .= '
                                </ul>
                                <p>Gesamtsumme: ' . number_format(self::getOrderById($orderId)->total_price, 2) . ' €</p>
                                <p>Vielen Dank für Ihre Bestellung bei Respawn Gaming.</p>
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

        return $mail->sendMail($toEmail, "no-reply@respawngaming.at", "No-Reply", "Ihre Bestellung", $body, $pdfFilePath);
    }

// Methoden getOrderById() und getOrderItems() implementieren
    private static function getOrderById(int $orderId)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        // Angenommen, die Bestellungen liegen in der Tabelle "orders" und der Primärschlüssel heißt "order_id"
        $sql = "SELECT * FROM orders WHERE id = :order_id LIMIT 1";
        $stmt = $database->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        // Hier holen wir das Ergebnis als Objekt (z.B. stdClass), sodass du in der PDF-Erstellung mit Objekt-Eigenschaften arbeiten kannst.
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    private static function getOrderItems(int $orderId): array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        // Angenommen, die Bestellpositionen liegen in der Tabelle "order_items" und haben eine Spalte "order_id"
        $sql = "SELECT oi.*, g.*
                FROM order_items oi
                JOIN games g ON oi.game_id = g.id
                WHERE order_id = :order_id";
        $stmt = $database->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        // Alle Positionen als Array von Objekten zurückgeben
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
