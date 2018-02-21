<?php

namespace EPayment\Interfaces;

interface IEPaymentHttpRedirectPaymentRequest extends IEPaymentSignedPaymentRequest
{
    public function setRedirectUrlBase($url);

    /**
     * @return string
     */
    public function getRedirectUrl();
}