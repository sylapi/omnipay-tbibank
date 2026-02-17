<?php

namespace Omnipay\TBIBank\Message;

use Exception;
use GuzzleHttp\Psr7;
use Omnipay\TBIBank\Trait;

class VoidRequest extends \Omnipay\Common\Message\AbstractRequest
{
    use Trait\Request;

    // TODO: Ustaw właściwą ścieżkę API dla TBIBank
    const API_PATH = '/payments/void';
    
    public function sendData($data)
    {
        // TODO: Implementuj anulowanie płatności zgodnie z TBIBank API
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

            return new VoidResponse($this, $response);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

    public function getData()
    {
        // TODO: Przygotuj dane zgodnie z dokumentacją TBIBank API
        $data = [
            'transaction_id' => $this->getTransactionReference(),
            // TODO: Dodaj inne wymagane pola
        ];

        return $data;
    }
}
