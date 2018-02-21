<?php

namespace EPayment\Interfaces;

interface IEPaymentHttpPostPaymentRequest extends IEPaymentSignedPaymentRequest
{
    public function setUrlBase($url);

    public function getPaymentRequestFields();

    public function getUrlBase();
}