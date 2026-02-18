<?php

namespace Omnipay\TBIBank;

use Omnipay\TBIBank\Trait;
use Omnipay\Common\AbstractGateway;
use Omnipay\TBIBank\Message\VoidRequest;
use Omnipay\TBIBank\Message\PurchaseRequest;
use Omnipay\TBIBank\Message\CompletePurchaseRequest;

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
            'storeId'       => '',
            'username'      => '',
            'password'      => '',
            'providerCode'  => '',
            'testMode'      => true
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

    public function void(array $options = array())
    {
        return parent::createRequest(VoidRequest::class, $options);
    }    
}
