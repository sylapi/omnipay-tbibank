<?php

namespace Omnipay\TBIBank\Message;

use Exception;
use GuzzleHttp\Psr7;
use Omnipay\TBIBank\Trait;

class PurchaseRequest extends \Omnipay\Common\Message\AbstractRequest
{
    use Trait\Request;

    const API_PATH = '/Finalize';

    public function sendData($data)
    {
        $apiUrl = $this->getApiUrl() . self::API_PATH;
        $headers = $this->getHeaders();

        try {
            // Prepare order data for encryption
            $orderData = [
                'store_id' => $this->getStoreId(),
                'order_id' => $this->getTransactionReference(),
                'back_ref' => $this->getNotifyUrl(),
                'order_total' => $this->getAmount(),
                'username' => $this->getUsername(),
                'password' => $this->getPassword(),
                'customer' => $this->getCustomerData(),
                'items' => $this->getItemsData()
            ];

            // Encrypt order data
            $encryptedData = $this->encryptOrderData($orderData);

            // Prepare form data
            $postData = [
                'order_data' => $encryptedData,
                'providerCode' => $this->getProviderCode()
            ];

            $body = http_build_query($postData);
            
            $result = $this->httpClient->request(
                'POST', 
                $apiUrl, 
                $headers, 
                $body
            );

            // Check for successful redirect response
            $rawBody = $result->getBody();
            $statusCode = $result->getStatusCode();
            
            $response = json_decode($rawBody, true);
            
            // Handle successful redirect (301/302) as success
            if ($statusCode >= 300 && $statusCode < 400) {
                $location = $result->getHeader('Location');
                if (isset($location[0]) && !empty($location[0])) {
                    $response = [
                        'isSuccess' => true,
                        'redirect_url' => $location[0],
                        'status_code' => $statusCode,
                        'message' => 'Credit application submitted successfully - customer should be redirected'
                    ];
                }
            }
            
            if ($response === null && json_last_error() !== JSON_ERROR_NONE) {
                echo "❌ JSON Parse Error: " . json_last_error_msg() . "\n";
                $response = ['error' => 'Invalid JSON response from TBI API', 'raw_response' => substr($rawBody, 0, 500)];
            }
            
            return new PurchaseResponse($this, $response);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

    public function getData()
    {
        // Validate required parameters
        $this->validate('storeId', 'username', 'password', 'providerCode', 'transactionReference', 'amount');
        
        return [];
    }

    protected function getCustomerData()
    {
        return [
            'fname' => $this->getParameter('customerFirstName') ?? '',
            'lname' => $this->getParameter('customerLastName') ?? '',
            'cnp' => $this->getParameter('customerCnp') ?? '',
            'email' => $this->getParameter('customerEmail') ?? '',
            'phone' => $this->getParameter('phone') ?: '',
            'billing_address' => $this->getParameter('billingAddress') ?: '',
            'billing_city' => $this->getParameter('billingCity') ?: '',
            'billing_county' => $this->getParameter('billingCounty') ?: '',
            'shipping_address' => $this->getParameter('shippingAddress') ?: '',
            'shipping_city' => $this->getParameter('shippingCity') ?: '',
            'shipping_county' => $this->getParameter('shippingCounty') ?: '',
            'promo' => $this->getParameter('promo') ?? 0 // User can set promo code, default 0
        ];
    }

    protected function getItemsData()
    {
        $items = $this->getParameter('items');
        if (!$items) {
            // Default single item if no items specified
            return [
                [
                    'name' => $this->getDescription() ?: 'Product',
                    'qty' => '1.0000',
                    'price' => $this->getAmount(),
                    'category' => '1',
                    'sku' => $this->getTransactionReference(),
                    'ImageLink' => ''
                ]
            ];
        }
        
        return $items;
    }

    // Customer data setters/getters
    public function setCustomerFirstName($value)
    {
        return $this->setParameter('customerFirstName', $value);
    }

    public function setCustomerLastName($value)
    {
        return $this->setParameter('customerLastName', $value);
    }

    public function setCustomerEmail($value)
    {
        return $this->setParameter('customerEmail', $value);
    }

    public function setPhone($value)
    {
        return $this->setParameter('phone', $value);
    }

    public function getPhone()
    {
        return $this->getParameter('phone');
    }

    public function setCustomerCnp($value)
    {
        return $this->setParameter('customerCnp', $value);
    }

    public function setBillingAddress($value)
    {
        return $this->setParameter('billingAddress', $value);
    }

    public function setBillingCity($value)
    {
        return $this->setParameter('billingCity', $value);
    }

    public function setBillingCounty($value)
    {
        return $this->setParameter('billingCounty', $value);
    }

    public function setShippingAddress($value)
    {
        return $this->setParameter('shippingAddress', $value);
    }

    public function setShippingCity($value)
    {
        return $this->setParameter('shippingCity', $value);
    }

    public function setShippingCounty($value)
    {
        return $this->setParameter('shippingCounty', $value);
    }

    public function setInstalments($value)
    {
        return $this->setParameter('instalments', $value);
    }

    public function setItems($items)
    {
        return $this->setParameter('items', $items);
    }

    public function setPromo($value)
    {
        return $this->setParameter('promo', $value);
    }

    public function getPromo()
    {
        return $this->getParameter('promo') ?? 0;
    }
}
