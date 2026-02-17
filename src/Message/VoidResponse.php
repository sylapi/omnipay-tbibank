<?php

namespace Omnipay\TBIBank\Message;

use Omnipay\TBIBank\Trait;

class VoidResponse extends \Omnipay\Common\Message\AbstractResponse
{   
    use Trait\Response;

    public function isSuccessful()
    {   
        // TBI cancellation response: {"isSuccess": true, "error": null}
        return isset($this->data['isSuccess']) && $this->data['isSuccess'] === true;
    }

    public function getTransactionId(): ?string
    {
        // Return the order ID that was canceled
        $request = $this->getRequest();
        if ($request instanceof \Omnipay\Common\Message\AbstractRequest) {
            return $request->getTransactionReference();
        }
        return null;
    }

    public function getMessage()
    {
        if ($this->isSuccessful()) {
            return 'Order canceled successfully';
        }
        
        return $this->data['error'] ?? 'Cancellation failed';
    }
}
