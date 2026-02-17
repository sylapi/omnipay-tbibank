<?php

namespace Omnipay\TBIBank\Message;

use Omnipay\TBIBank\Trait;

class RefundResponse extends \Omnipay\Common\Message\AbstractResponse
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

    public function getMessage()
    {
        return 'TBI does not provide refund API. Contact TBI support for refunds.';
    }
}
