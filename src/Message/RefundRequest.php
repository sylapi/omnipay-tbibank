<?php

namespace Omnipay\TBIBank\Message;

use Exception;
use Omnipay\TBIBank\Trait;

class RefundRequest extends \Omnipay\Common\Message\AbstractRequest
{
    use Trait\Request;

    public function sendData($data)
    {
        throw new Exception('TBI does not provide refund API. Contact TBI support for refunds.');
    }

    public function getData()
    {
        throw new Exception('TBI does not provide refund API. Contact TBI support for refunds.');
    }
}
