<?php

namespace EPayment\Interfaces;

use EPayment\EPaymentException;
use EvInOrder;

interface IEPaymentSignedPaymentRequest
{
    public function __construct();

    public function signMessage($sharedSecret);
}