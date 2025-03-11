<?php

use PHPMailer\PHPMailer\PHPMailer;

class OrderModel
{
public static function createInvoicePdf(int $orderId): string
{
// Bestelldaten aus der DB holen (z.B. über getOrderById($orderId))
$order = self::getOrderById($orderId);
$orderItems = self::getOrderItems($orderId);

// FPDF initialisieren (stelle sicher, dass FPDF per Composer oder manuell eingebunden ist)
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Respawn Gaming - Rechnung',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,'Bestellnummer: ' . $order->id,0,1);
// Weitere Details (Datum, Kundendaten, Artikel, Preise etc.)
foreach ($orderItems as $item) {
$pdf->Cell(0,10,$item->title . ' - ' . $item->quantity . ' x ' . number_format($item->price, 2) . ' €',0,1);
}
$pdf->Cell(0,10,'Gesamtsumme: ' . number_format($order->total_price, 2) . ' €',0,1);

// PDF auf dem Server speichern
$filePath = 'invoices/invoice_' . $order->id . '.pdf';
$pdf->Output('F', $filePath);
return $filePath;
}

public static function sendInvoiceByEmail(string $pdfFilePath, string $toEmail): bool
{
$mail = new PHPMailer(true);
try {
$mail->setFrom('no-reply@respawngaming.at', 'Respawn Gaming');
$mail->addAddress($toEmail);
$mail->Subject = 'Deine Rechnung von Respawn Gaming';
$mail->Body = 'Im Anhang findest du deine Rechnung.';
$mail->addAttachment($pdfFilePath);
$mail->send();
return true;
} catch (Exception) {
error_log("Fehler beim Versand der Rechnung: " . $mail->ErrorInfo);
return false;
}
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
        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        $stmt = $database->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        // Alle Positionen als Array von Objekten zurückgeben
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

}
