<?php

namespace EPayment\Interfaces;

use EPayment\EPaymentException;

interface IEPaymentHttpPaymentResponse {


    /**
     * IEPaymentHttpPaymentResponse constructor.
     * @param null $fields
     */
    public function __construct($fields = null);

    /**
     * IEPaymentSignedPaymentRequest constructor.
     * @param $password
     * @throws EPaymentException
     */
    public function verifySignature($password);

    /**
     * @return int
     * @throws EPaymentException
     */
    public function getPaymentResponse();

    const RESPONSE_SUCCESS = 1;
    const RESPONSE_FAIL    = 2;
    const RESPONSE_TIMEOUT = 3;
    const RESPONSE_UNKNOWN = 4;
}