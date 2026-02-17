<?php

namespace Omnipay\TBIBank\Trait;

trait Request {

    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey($secretKey)
    {
        return $this->setParameter('secretKey', $secretKey);
    }

    public function getApiUrl()
    {        
        if ($this->getTestMode()) {
            return 'https://sandbox-api.tbibank.com.ge';
        } else {
            return 'https://api.tbibank.com.ge';
        }
    }

    public function setApiUrl($apiUrl)
    {
        $this->setParameter('apiUrl', $apiUrl);
    }

    public function getHeaders(array $append = [])
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getApiKey(),
        ];

        return array_merge($headers,$append);
    }

    // TODO: Implementuj metodę do generowania podpisu zgodnie z dokumentacją TBIBank
    public function getSignature(array $data)
    {
        // Placeholder - należy zaimplementować zgodnie z dokumentacją TBIBank API
        return hash_hmac('sha256', json_encode($data), $this->getSecretKey());
    }

    
}