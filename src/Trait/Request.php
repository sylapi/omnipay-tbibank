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

    public function getPublicKeyPath()
    {
        return $this->getParameter('publicKeyPath');
    }

    public function setPublicKeyPath($value)
    {
        return $this->setParameter('publicKeyPath', $value);
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

    public function getHeaders(array $append = [])
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        return array_merge($headers, $append);
    }

    /**
     * Encrypt data according to TBI specification
     * RSA encryption with chunking
     */
    public function encryptOrderData($data)
    {
        $publicKeyPath = $this->getPublicKeyPath();
        
        // Try file-based key first, then fallback to TBI test key
        $publicKeyContent = null;
        $keySource = 'unknown';
        if ($publicKeyPath && file_exists($publicKeyPath)) {
            $publicKeyContent = file_get_contents($publicKeyPath);
            $keySource = 'file: ' . $publicKeyPath;
        } else {
            // Fallback to TBI test public key from working example
            $publicKeyContent = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA41/0nOIwjmgor4E3cmuN
fBqylJ781ceKkxUvukvP1uBWdDEV+U8ed2jVzhi/DSyGZZjxCrHT7YjueKOAXknD
PD/Jw7WzIV8xX2k4OJrqqREmbiUE0cjlPH1pfrAUgi6DcLASoJD6gcdqcyV/cYlM
qfXnYIWQpIx2iTtPGpc4XEx5jqH6lePWkv7fCULx/1VeBKeERMjZSvLamm5g7S+h
YsbhQ+kzKy6J6psxzj3u6Suwrnzs7Q8lB4tKAjMFSbWWbpf+EDh+LNiIC0L5br86
Vt2XtiUnKjPx0CBqkZoL7MQ/8QK5iuPSh79hng093hcfhG65HaGwMbqYFeyME4/t
ewIDAQAB
-----END PUBLIC KEY-----
EOD;
            $keySource = 'embedded TBI test key';
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

    /**
     * Calculate promo code based on order total according to TBI logic
     */
    public function calculatePromo($orderTotal)
    {
        // TODO: Implement promo logic based on order amount
        // According to documentation: "promo parameter is determined by the order total"
        // You need to get the specific promo logic from TBI team
        return 0;
    }

    
}