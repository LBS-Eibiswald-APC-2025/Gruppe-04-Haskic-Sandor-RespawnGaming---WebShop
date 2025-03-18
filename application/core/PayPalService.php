<?php

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalService
{
    private PayPalHttpClient $client;

    public function __construct()
    {
        // PayPal Sandbox-Zugangsdaten
        $clientId = 'AWNZYlCOkG2zCBgveVD3SKSOC08C3B1vgJi-JssZ0KtIjcYEjvWDb2lyCpEyqRC3NO4BEh2VTZc78_b6';
        $clientSecret = 'EGNOV5JfT8M6LMI7Sez6W4_mxrnVkhmj4dcl675pdT2l6NlY8FyQftXoQYDye-4mHyRWo7y-iuxS9p_C';

        // Environment erstellen (Sandbox für Entwicklung)
        $environment = new SandboxEnvironment($clientId, $clientSecret);
        $this->client = new PayPalHttpClient($environment);
    }

    /**
     * Erstellt eine PayPal-Zahlung und gibt die Redirect-URL zurück
     *
     * @param float $amount Gesamtbetrag
     * @param string $returnUrl URL für erfolgreiche Zahlung
     * @param string $cancelUrl URL für abgebrochene Zahlung
     * @return string|null Redirect-URL oder null bei Fehler
     */
    public function createPayment(float $amount, string $returnUrl, string $cancelUrl): ?string
    {
        $request = new OrdersCreateRequest(); //Test Push
        $request->prefer('return=representation');

        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'EUR',
                    'value' => number_format($amount, 2, '.', '')
                ]
            ]],
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl
            ]
        ];

        try {
            $response = $this->client->execute($request);

            // Finde den Approval-Link
            foreach ($response->result->links as $link) {
                if ($link->rel === 'approve') {
                    return $link->href;
                }
            }
        } catch (Exception $e) {
            error_log('PayPal Fehler: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Führt eine PayPal-Zahlung aus
     *
     * @param string $paymentId PayPal Payment ID
     * @return bool Erfolg oder Misserfolg
     */
    public function executePayment(string $paymentId): bool
    {
        $request = new OrdersCaptureRequest($paymentId);
        $request->prefer('return=representation');

        try {
            $response = $this->client->execute($request);
            return $response->result->status === 'COMPLETED';
        } catch (Exception $e) {
            error_log('PayPal Fehler: ' . $e->getMessage());
            return false;
        }
    }
}