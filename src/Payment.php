<?php

namespace EPayments;


use EPayment\EPaymentException;

abstract class Payment
{

    /**
     * Payment constructor.
     * @throws EPaymentException
     */
    abstract public function __construct();

    /**
     * @param $amount
     * @param $variableSymbol
     * @param null $returnUrl
     * @param null $name
     * @param null $language
     * @return string | array
     * @throws EPaymentException
     */
    abstract function request($amount, $variableSymbol, $returnUrl = null, $name = null, $language = null);

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    abstract function response($fields = null);

}