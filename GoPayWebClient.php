<?php

use GoPay\Payments;

//https://github.com/gopaycommunity/gopay-php-api
class GoPayWebClient {

    const API_URL = "https://gw.sandbox.gopay.com/api";

    private Payments $client;

    public function __construct(string $goId, string $clientId, string $clientSecret, string $gatewayUri)
    {
        $this->client = GoPay\Api::payments([
            'goid' => $goId,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'gatewayUrl' => $gatewayUri
        ]);
    }

    //https://doc.gopay.com/#payment-creation
    public function createPayment(float $amount, string $currency, string $orderNumber, string $userName, string $userEmail, string $userCountry)
    {
        return $this->client->createPayment([
            "amount" => $amount,
            "currency" => $currency,
            "order_number" => $orderNumber,
            "target" => [
                "type" => "ACCOUNT",
                "goid" => $this->client->getGopay()->getConfig("goid"),
            ],
        ]);
    }

    //https://doc.gopay.com/#payment-status
    public function getPaymentStatus(int $paymentId)
    {
        return $this->client->getStatus($paymentId);
    }

    //https://doc.gopay.com/#payment-refund
    public function refundTransaction(int $paymentId, float $amount)
    {
        return $this->client->refundPayment($paymentId, $amount);
    }
}