<?php


namespace EPayments;


class PaymentResponseObject
{

    public $amount;
    public $variableSymbol;
    public $transactionId;
    public $result;
    public $message;

    public function __construct($amount = null, $variableSymbol = null, $transactionId = null, $result = null, $message = null)
    {
        $this->amount = $amount;
        $this->variableSymbol = $variableSymbol;
        $this->transactionId = $transactionId;
        $this->result = $result;
        $this->message = $message;
    }
}

