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
     * @param PaymentObject $paymentObject
     * @param null $endpoint
     * @return mixed
     * @throws EPaymentException
     */
    abstract function request(PaymentObject $paymentObject, $endpoint = null);

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    abstract function response($fields = null);

}