<?php

namespace Omnipay\TBIBank\Message;

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\TBIBank\Trait;

class PurchaseResponse extends \Omnipay\Common\Message\AbstractResponse implements RedirectResponseInterface
{
    use Trait\Response;

    public function isSuccessful()
    {
        return $this->isSuccessfulResponse();
    }

    public function isRedirect()
    {
        // TODO: Dostosuj do struktury odpowiedzi TBIBank API
        $redirectUrl = $this->data['redirect_url'] ?? null;
        return ($this->isSuccessful() && $redirectUrl);
    }

    public function getTransactionId()
    {
        // TODO: Dostosuj do struktury odpowiedzi TBIBank API
        return ($this->isSuccessful()) ? $this->data['transaction_id'] ?? $this->data['transactionId'] : null;
    }

    public function getRedirectUrl()
    {
        // TODO: Dostosuj do struktury odpowiedzi TBIBank API
        return ($this->isRedirect()) ? $this->data['redirect_url'] : null;
    }

    public function getRedirectData()
    {
        return $this->data;
    }
}
