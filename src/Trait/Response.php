<?php

namespace Omnipay\TBIBank\Trait;

trait Response {

    private $message;
    private $code;

    public function isSuccessfulResponse()
    {
        // TBI specific success logic
        // For most TBI responses, success is indicated by isSuccess: true
        return isset($this->data['isSuccess']) && $this->data['isSuccess'] === true;
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