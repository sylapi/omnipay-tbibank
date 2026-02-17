<?php

namespace Omnipay\TBIBank\Message;

use Omnipay\TBIBank\Trait;

class CompletePurchaseResponse extends \Omnipay\Common\Message\AbstractResponse
{
    use Trait\Response;

    public function isSuccessful()
    {
        // TBI callback success is determined by status_id
        // status_id: 0 = rejected/canceled, 1 = approved
        return isset($this->data['status_id']) && $this->data['status_id'] === '1';
    }

    public function isCancelled()
    {
        return isset($this->data['status_id']) && $this->data['status_id'] === '0';
    }

    public function getTransactionId()
    {
        return $this->data['order_id'] ?? null;
    }

    public function getStatus()
    {
        $statusId = $this->data['status_id'] ?? null;
        
        switch ($statusId) {
            case '1':
                return 'approved';
            case '0':
                return 'rejected';
            default:
                return 'unknown';
        }
    }

    public function getMessage()
    {
        if ($this->isSuccessful()) {
            return 'Credit application approved';
        } elseif ($this->isCancelled()) {
            return $this->data['motiv'] ?? 'Credit application rejected/canceled';
        }
        
        return 'Unknown status';
    }

    public function getRejectionReason()
    {
        return $this->data['motiv'] ?? null;
    }
}
