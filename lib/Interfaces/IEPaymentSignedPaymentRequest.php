<?php

namespace EPayment\Interfaces;

interface IEPaymentSignedPaymentRequest
{
    public function __construct();

    public function signMessage($sharedSecret);
}