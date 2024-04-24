<?php

namespace EPayments;


/**
 * @property string $BillToCompany;
 * @property string $BillToName;
 * @property string $BillToStreet1;
 * @property string $BillToStreet2;
 * @property string $BillToCity;
 * @property string $BillToState;
 * @property string $BillToPostalCode;
 * @property string $BillToCountry;
 *
 * @property string $ShipToCompany;
 * @property string $ShipToName;
 * @property string $ShipToStreet1;
 * @property string $ShipToStreet2;
 * @property string $ShipToCity;
 * @property string $ShipToStateProv;
 * @property string $ShipToPostalCode;
 * @property string $ShipToCountry;
 */
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