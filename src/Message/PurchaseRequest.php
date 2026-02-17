<?php

namespace Omnipay\TBIBank\Message;

use Exception;
use GuzzleHttp\Psr7;
use Omnipay\TBIBank\Trait;

class PurchaseRequest extends \Omnipay\Common\Message\AbstractRequest
{
    use Trait\Request;

    const API_PATH = '/payments';

    public function sendData($data)
    {
        // TODO: Implementuj wysyłanie żądania płatności zgodnie z TBIBank API
        $apiUrl = $this->getApiUrl() . self::API_PATH;
        $headers = $this->getHeaders();

        try {
            $body = Psr7\Utils::streamFor(json_encode($data));
            $result = $this->httpClient->request(
                'POST', 
                $apiUrl, 
                $headers, 
                $body
            );

            $response = json_decode($result->getBody(), true);
            $response['transactionId'] = $this->getTransactionReference();
            $this->response = $response;

            return new PurchaseResponse($this, $response);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

    public function getData()
    {
        // TODO: Przygotuj dane zgodnie z dokumentacją TBIBank API
        $data = [
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'order_id' => $this->getTransactionReference(),
            'description' => $this->getDescription(),
            'return_url' => $this->getReturnUrl(),
            'cancel_url' => $this->getCancelUrl(),
            'notify_url' => $this->getNotifyUrl(),
            // TODO: Dodaj inne wymagane pola zgodnie z dokumentacją
        ];

        return $data;
    }

    // TODO: Dodaj dodatkowe metody gettery/settery jeśli wymagane przez TBIBank API
}
