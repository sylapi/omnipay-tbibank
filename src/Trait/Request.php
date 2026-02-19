<?php

namespace Omnipay\TBIBank\Trait;

trait Request {

    public function getStoreId()
    {
        return $this->getParameter('storeId');
    }

    public function setStoreId($value)
    {
        return $this->setParameter('storeId', $value);
    }

    public function getUsername()
    {
        return $this->getParameter('username');
    }

    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getProviderCode()
    {
        return $this->getParameter('providerCode');
    }

    public function setProviderCode($value)
    {
        return $this->setParameter('providerCode', $value);
    }

    public function getPublicKey()
    {
        return $this->getParameter('publicKey');
    }

    public function setPublicKey($value)
    {
        return $this->setParameter('publicKey', $value);
    }

    public function getPrivateKey()
    {
        return $this->getParameter('privateKey');
    }

    public function setPrivateKey($value)
    {
        return $this->setParameter('privateKey', $value);
    }

    public function getApiUrl()
    {        
        // TBI uses the same URL for both test and live
        return 'https://ecommerce.tbibank.ro/Api/LoanApplication';
    }

    public function setApiUrl($apiUrl)
    {
        $this->setParameter('apiUrl', $apiUrl);
    }

    public function getTestMode()
    {
        return $this->getParameter('testMode');
    }

    public function setTestMode($value)
    {
        return $this->setParameter('testMode', $value);
    }

    public function getHeaders(array $append = [])
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'TBIBank-OmniPay/1.0',
            'Cache-Control' => 'no-cache',
            'Connection' => 'close'
        ];

        return array_merge($headers, $append);
    }

    /**
     * Encrypt data according to TBI specification
     * RSA encryption with chunking
     */
    public function encryptOrderData($data)
    {
        $publicKeyContent = $this->getPublicKey();
        
        if (!$publicKeyContent) {
            throw new \Exception('Public key is required. Please use setPublicKey() to set the RSA public key content.');
        }

        $publicKey = openssl_pkey_get_public($publicKeyContent);
        if (!$publicKey) {
            throw new \Exception('Failed to load public key.');
        }

        $keyDetails = openssl_pkey_get_details($publicKey);
        
        // Calculate block size: (key_size / 8) - 11
        $chunkSize = ceil($keyDetails['bits'] / 8) - 11;
        $plaintext = json_encode($data);
        $output = '';

        // Encrypt data in chunks
        while ($plaintext) {
            $chunk = substr($plaintext, 0, (int)$chunkSize);
            $plaintext = substr($plaintext, (int)$chunkSize);
            $encrypted = '';

            if (!openssl_public_encrypt($chunk, $encrypted, $publicKey)) {
                throw new \Exception('Failed to encrypt data chunk.');
            }
            $output .= $encrypted;
        }

        return base64_encode($output);
    }
    
}