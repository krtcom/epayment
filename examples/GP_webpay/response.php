<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;
use EPayments\GP_webpay;

define('EPAYMENT_GP_WEBPAY_MID', "999999999");
define('EPAYMENT_GP_WEBPAY_PRIVATE_KEY_FILE', __DIR__ . "/test_key.pem");
define('EPAYMENT_GP_WEBPAY_PRIVATE_KEY_PASS', "changeit");
define('EPAYMENT_GP_WEBPAY_PUBLIC_KEY_FILE', __DIR__ . "/test_cert.pem");

$payment = new GP_webpay();
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