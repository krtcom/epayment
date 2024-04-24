<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;
use EPayments\VUB_eCard;

define('EPAYMENT_VUB_ECARD_CLIENT_ID', "10062601");
define('EPAYMENT_VUB_ECARD_STORE_KEY', "VL3Y2OF9UEV6I30X");

$payment = new VUB_eCard();
try {
    $notificationResult = $payment->response();

    switch ($notificationResult) {
        case IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS:
            $result = "Payment success";
            break;
        case IEPaymentHttpPaymentResponse::RESPONSE_FAIL:
            $result = sprintf("Payment failed [%s]", $payment->responseMessage);
            break;
        case IEPaymentHttpPaymentResponse::RESPONSE_TIMEOUT:
            $result = "Payment timeout";
            break;
        case IEPaymentHttpPaymentResponse::RESPONSE_PENDING:
            $result = "Payment pending";
            break;
    }
} catch (EPaymentException $e) {
    $result = "ERROR: " . $e->getMessage();
}

echo $result;