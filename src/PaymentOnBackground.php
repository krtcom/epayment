<?php

namespace EPayments;


use EPayment\EPaymentException;

abstract class PaymentOnBackground extends Payment
{

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    abstract function notification($fields = null);

}