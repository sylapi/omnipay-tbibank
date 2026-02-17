<?php

namespace Omnipay\TBIBank;

use Omnipay\TBIBank\Trait;
use Omnipay\Common\AbstractGateway;
use Omnipay\TBIBank\Message\VoidRequest;
use Omnipay\TBIBank\Message\PurchaseRequest;
use Omnipay\TBIBank\Message\CompletePurchaseRequest;
use Omnipay\TBIBank\Message\FetchTransactionRequest;
use Omnipay\TBIBank\Message\RefundRequest;

class Gateway extends AbstractGateway
{

    use Trait\Request;

    public function getName()
    {
        return 'TBIBank';
    }

    public function getDefaultParameters()
    {
        return [
            'apiKey'       => '',
            'secretKey'    => '',
            'testMode'     => true
        ];
    }

    public function initialize(array $options = [])
    {
        parent::initialize($options);
        return $this;
    }

    public function purchase(array $options = array())
    {
        return parent::createRequest(PurchaseRequest::class, $options);
    }

    public function completePurchase(array $options = array())
    {
        return parent::createRequest(CompletePurchaseRequest::class, $options);
    }

    public function fetchTransaction(array $options = [])
    {
        return parent::createRequest(FetchTransactionRequest::class, $options);
    }

    public function refund(array $options = array())
    {
        return parent::createRequest(RefundRequest::class, $options);
    }

    public function void(array $options = array())
    {
        return parent::createRequest(VoidRequest::class, $options);
    }    
}
