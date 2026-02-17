<?php

namespace Omnipay\TBIBank\Message;

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\TBIBank\Trait;

class PurchaseResponse extends \Omnipay\Common\Message\AbstractResponse implements RedirectResponseInterface
{
    use Trait\Response;

    public function isSuccessful()
    {
        // TBI returns success if credit application is submitted successfully
        // The actual approval/rejection comes via callback
        return isset($this->data['isSuccess']) && $this->data['isSuccess'] === true;
    }

    public function isRedirect()
    {
        // TBI handles the customer interaction on their platform
        // After successful submission, customer should be redirected to TBI
        return $this->isSuccessful() && !empty($this->getRedirectUrl());
    }

    public function getTransactionId(): ?string
    {
        // TBI uses order_id as transaction reference
        $request = $this->getRequest();
        if ($request instanceof \Omnipay\Common\Message\AbstractRequest) {
            return $request->getTransactionReference();
        }
        return null;
    }

    public function getRedirectUrl()
    {
        // TBI will provide redirect URL in successful response
        // TODO: Check actual TBI response format for redirect URL
        return $this->data['redirect_url'] ?? null;
    }

    public function getRedirectData()
    {
        return $this->data;
    }

    public function getMessage()
    {
        if (!$this->isSuccessful()) {
            return $this->data['error'] ?? 'Unknown error occurred';
        }
        return 'Credit application submitted successfully';
    }

    public function getCode()
    {
        return $this->data['errorCode'] ?? null;
    }
}
