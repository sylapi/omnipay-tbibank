<?php

namespace Omnipay\TBIBank\Message;

use Exception;
use Omnipay\TBIBank\Trait;

class FetchTransactionRequest extends \Omnipay\Common\Message\AbstractRequest
{
    use Trait\Request;

    public function sendData($data)
    {
        throw new Exception('TBI does not provide transaction status API. Status updates come via callback only.');
    }

    public function getData()
    {
        throw new Exception('TBI does not provide transaction status API. Status updates come via callback only.');
    }
}
