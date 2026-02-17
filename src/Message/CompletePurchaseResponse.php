<?php

namespace Omnipay\TBIBank\Message;

use Omnipay\TBIBank\Trait;

class CompletePurchaseResponse extends \Omnipay\Common\Message\AbstractResponse
{   
    use Trait\Response;

    public function isSuccessful()
    {   
        return $this->isSuccessfulResponse();
    }

    public function getTransactionId()
    {
        // TODO: Dostosuj do struktury odpowiedzi TBIBank API
        return ($this->isSuccessful()) ? $this->data['transaction_id'] ?? $this->data['transactionId'] : null;
    }
}
