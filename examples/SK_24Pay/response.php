<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;
use EPayments\SK_24Pay;

define('EPAYMENT_SK_24PAY_MID', "demoOMED");
define('EPAYMENT_SK_24PAY_ESHOPID', "11111111");
define('EPAYMENT_SK_24PAY_SECRET', "1234567812345678123456781234567812345678123456781234567812345678");

$payment = new SK_24Pay();
try {
    $notificationResult = $payment->response();

    switch($notificationResult) {
        case IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS:
            $result = "Payment success";
            break;
        case IEPaymentHttpPaymentResponse::RESPONSE_FAIL:
            $result =  "Payment failed";
            break;
        case IEPaymentHttpPaymentResponse::RESPONSE_TIMEOUT:
            $result =  "Payment timeout";
            break;
        case IEPaymentHttpPaymentResponse::RESPONSE_PENDING:
            $result =  "Payment pending";
            break;
    }
} catch (EPaymentException $e) {
    $result = "ERROR: ". $e->getMessage();
}

echo $result;