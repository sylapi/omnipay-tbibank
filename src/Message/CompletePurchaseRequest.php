<?php

namespace Omnipay\TBIBank\Message;

use Exception;
use Omnipay\TBIBank\Trait;

class CompletePurchaseRequest extends \Omnipay\Common\Message\AbstractRequest
{
    use Trait\Request;

    public function sendData($data)
    {
        // This is used for processing TBI callback (ReturnToProvider)
        // TBI sends callback data to merchant's notify URL
        // No HTTP request needed - just process the callback data
        return new CompletePurchaseResponse($this, $data);
    }

    public function getData()
    {
        // Get callback data from TBI
        // TBI sends: {"order_id": "145003523", "status_id": "0", "motiv": "Rejection reason"}
        $httpRequest = $this->httpRequest;
        
        if ($httpRequest->getMethod() === 'POST') {
            $body = $httpRequest->getContent();
            $data = json_decode($body, true);
            
            if (json_last_error() === JSON_ERROR_NONE && $data) {
                return $data;
            }
        }
        
        // Try to get data from POST parameters
        $postData = $httpRequest->request->all();
        if (!empty($postData)) {
            return $postData;
        }
        
        throw new Exception('No callback data received from TBI');
    }

    public function getPrivateKeyPath()
    {
        return $this->getParameter('privateKeyPath');
    }

    public function setPrivateKeyPath($value)
    {
        return $this->setParameter('privateKeyPath', $value);
    }

    public function getPrivateKeyPassword()
    {
        return $this->getParameter('privateKeyPassword');
    }

    public function setPrivateKeyPassword($value)
    {
        return $this->setParameter('privateKeyPassword', $value);
    }

    /**
     * Decrypt callback data if it's encrypted
     */
    public function decryptCallbackData($encryptedData)
    {
        $privateKeyPath = $this->getPrivateKeyPath();
        $privateKeyPassword = $this->getPrivateKeyPassword();
        
        if (!$privateKeyPath || !file_exists($privateKeyPath)) {
            throw new Exception('Private key file not found. Please set privateKeyPath parameter.');
        }

        $privateKey = openssl_pkey_get_private(
            file_get_contents($privateKeyPath), 
            $privateKeyPassword ?? ''
        );
        
        if (!$privateKey) {
            throw new Exception('Failed to load private key.');
        }

        $keyDetails = openssl_pkey_get_details($privateKey);
        
        // Calculate block size: key_size / 8
        $chunkSize = ceil($keyDetails['bits'] / 8);
        $encrypted = base64_decode($encryptedData);
        $output = '';

        // Decrypt data in chunks
        while ($encrypted) {
            $chunk = substr($encrypted, 0, (int)$chunkSize);
            $encrypted = substr($encrypted, (int)$chunkSize);
            $decrypted = '';

            if (!openssl_private_decrypt($chunk, $decrypted, $privateKey)) {
                throw new Exception('Failed to decrypt callback data chunk.');
            }
            $output .= $decrypted;
        }

        return json_decode($output, true);
    }
}
