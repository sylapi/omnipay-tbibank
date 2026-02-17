<?php

namespace Omnipay\TBIBank\Message;

use Omnipay\TBIBank\Trait;

class FetchTransactionResponse extends \Omnipay\Common\Message\AbstractResponse
{
    use Trait\Response;

    public function isSuccessful()
    {
        return false;
    }

    public function getTransactionId()
    {
        return $this->data['transaction_id'] ?? null;
    }

    public function getStatus()
    {
        return $this->data['status'] ?? null;
    }

    public function getMessage()
    {
        return 'TBI does not provide transaction status API. Status updates come via callback only.';
    }
}
