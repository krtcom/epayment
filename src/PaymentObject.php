<?php

namespace EPayments;


class PaymentObject
{
    public $amount;
    public $variableSymbol;
    public $returnUrl;
    public $name;
    public $language;
    public $email;
    public $notificationUrl;
    public $userId;

    public function __construct($amount, $variableSymbol, $returnUrl = null, $name = null, $email = null, $language = null)
    {
        $this->amount = $amount;
        $this->variableSymbol = $variableSymbol;
        $this->returnUrl = $returnUrl;
        $this->name = $name;
        $this->email = $email;
        $this->language = $language;
    }

}