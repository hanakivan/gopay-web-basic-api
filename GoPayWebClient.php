<?php

use GoPay\Payments;
use Psr\Log\LoggerInterface;

//https://github.com/gopaycommunity/gopay-php-api
class GoPayWebClient {

    const API_URL = "https://gw.sandbox.gopay.com/api";

    private Payments $client;

    public function __construct(string $goId, string $clientId, string $clientSecret, string $gatewayUri, private ?LoggerInterface $logger = null)
    {
        $this->client = GoPay\Api::payments([
            'goid' => $goId,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'gatewayUrl' => $gatewayUri
        ]);

        $this->logger->info('Initializing client', [
            "ts" => (new DateTime())->format("c"),
        ]);
    }

    //https://doc.gopay.com/#payment-creation
    public function createPayment(float $amount, string $currency, string $orderNumber, string $userName, string $userEmail, string $userCountry): int
    {
        $this->logger->debug('Request: Creating payment', [
            "amount" => $amount,
            "currency" => $currency,
            "order_number" => $orderNumber,
        ]);

        $response = $this->client->createPayment([
            "amount" => $amount,
            "currency" => $currency,
            "order_number" => $orderNumber,
            "target" => [
                "type" => "ACCOUNT",
                "goid" => $this->client->getGopay()->getConfig("goid"),
            ],
        ]);

        $this->logger->debug('Response: Creating payment', [
            "code" => $response->statusCode,
            "raw" => (string)$response,
        ]);

        if($response->hasSucceed()) {
            return (int)$response->json['3000006529'];
        } else {
            $this->logger->debug('ERROR: Creating payment');

            throw new GoPayWebClientResponseException((string)$response, $response->statusCode);
        }
    }

    //https://doc.gopay.com/#payment-status
    public function getPaymentStatus(int $paymentId): string
    {
        $response = $this->client->getStatus($paymentId);

        if($response->hasSucceed()) {
            return (string)$response->json['state'];
        } else {
            throw new GoPayWebClientResponseException((string)$response, $response->statusCode);
        }
    }

    //https://doc.gopay.com/#payment-refund
    public function refundTransaction(int $paymentId, float $amount): bool
    {
        $response = $this->client->refundPayment($paymentId, $amount);

        if($response->hasSucceed()) {
            return $response->json['result'] === "FINISHED";
        } else {
            throw new GoPayWebClientResponseException((string)$response, $response->statusCode);
        }
    }
}