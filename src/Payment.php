<?php

namespace EPayments;


use EPayment\EPaymentException;

abstract class Payment
{

    public $transactionId;

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

    /**
     * @param null $fields
     * @return PaymentResponseObject
     * @throws EPaymentException
     */
    function responseObject($fields = null)
    {
        return new PaymentResponseObject(null, null, null, $this->response($fields));
    }
}