<?php

namespace Omnipay\TBIBank\Message;

use Exception;
use GuzzleHttp\Psr7;
use Omnipay\TBIBank\Trait;

class VoidRequest extends \Omnipay\Common\Message\AbstractRequest
{
    use Trait\Request;

    const API_PATH = '/CanceledByCustomer';

    public function sendData($data)
    {
        $apiUrl = $this->getApiUrl() . self::API_PATH;
        $headers = $this->getHeaders();

        try {
            // Prepare cancellation data
            $cancelData = [
                'orderId' => $this->getTransactionReference(),
                'statusId' => '1', // 1 for cancellation
                'username' => $this->getUsername(),
                'password' => $this->getPassword()
            ];

            // Encrypt cancellation data
            $encryptedData = $this->encryptOrderData($cancelData);

            // Prepare form data
            $postData = [
                'orderData' => $encryptedData,
                'encryptCode' => $this->getProviderCode()
            ];

            $body = http_build_query($postData);
            
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
        // Validate required parameters for cancellation
        $this->validate('transactionReference', 'username', 'password', 'providerCode');
        
        return [];
    }
}
