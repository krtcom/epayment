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
    public $orderID;
    public $currency;

    public function __construct($amount, $variableSymbol, $returnUrl = null, $name = null, $email = null, $language = null, $orderID = null)
    {
        $this->amount = $amount;
        $this->variableSymbol = $variableSymbol;
        $this->returnUrl = $returnUrl;
        $this->name = $name;
        $this->email = $email;
        $this->language = $language;
        $this->orderID = $orderID;
    }

}