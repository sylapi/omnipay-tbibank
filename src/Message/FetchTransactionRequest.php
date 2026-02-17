<?php

namespace Omnipay\TBIBank\Message;

use Exception;
use Omnipay\TBIBank\Trait;
use GuzzleHttp\Psr7;

class FetchTransactionRequest extends \Omnipay\Common\Message\AbstractRequest
{
    // TODO: Ustaw właściwą ścieżkę API dla TBIBank
    const API_PATH = '/payments/status';

    use Trait\Request;

    public function sendData($data)
    {
        // TODO: Implementuj pobieranie statusu transakcji zgodnie z TBIBank API
        $apiUrl = $this->getApiUrl() . self::API_PATH;
        $headers = $this->getHeaders();

        try {
            $result = $this->httpClient->request(
                'GET', 
                $apiUrl . '?' . http_build_query($data), 
                $headers
            );

            $response = json_decode($result->getBody(), true);

            return new FetchTransactionResponse($this, $response);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

    public function getData()
    {
        // TODO: Przygotuj dane zgodnie z dokumentacją TBIBank API
        $data = [
            'transaction_id' => $this->getTransactionReference(),
        ];

        return $data;
    }
}
