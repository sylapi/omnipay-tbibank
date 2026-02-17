<?php

namespace Omnipay\TBIBank\Trait;

trait Response {

    private $message;
    private $code;

    public function isSuccessfulResponse()
    {
        // TODO: Implementuj logikę sprawdzania sukcesu zgodnie z TBIBank API
        // Placeholder - należy dostosować do rzeczywistej struktury odpowiedzi TBIBank
        $success = (isset($this->data['success']) && $this->data['success'] === true); 

        if($success === false) {
            $this->setMessage($this->data['error']['message'] ?? 'Something went wrong.');
            $this->setCode($this->data['error']['code'] ?? null);
        }

        return $success;
    }
    
    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($value)
    {
        return $this->message = $value;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($value)
    {
        return $this->code = $value;
    }
    
}