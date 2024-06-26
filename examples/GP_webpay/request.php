<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use EPayment\EPaymentException;
use EPayments\GP_webpay;
use EPayments\PaymentObject;

define('EPAYMENT_GP_WEBPAY_MID', "999999999");
define('EPAYMENT_GP_WEBPAY_PRIVATE_KEY_FILE', __DIR__ . "/test_key.pem");
define('EPAYMENT_GP_WEBPAY_PRIVATE_KEY_PASS', "changeit");
define('EPAYMENT_GP_WEBPAY_PUBLIC_KEY_FILE', __DIR__ . "/test_cert.pem");

$payment = new GP_webpay();

$po = new PaymentObject(6.5, '123456');

$po->email = 'info@example.com';
$po->name = 'Jožko Mrkvička';
$po->userId = 47;
$po->returnUrl = 'http://epayment.devel.webcreators.sk/examples/GP_webpay/response.php';
$po->orderID = 333;

$po->BillToName = "Jožko Mrkvička";
$po->BillToStreet1 = "Testovacia 123";
$po->BillToCity = "Test";
$po->BillToPostalCode = "12345";
$po->BillToCountryISO = 703;
$po->BillToPhone = "0910123456";
$po->BillToEmail = "test@epayment.com";

$po->ShipToName = "Jožko Mrkvička";
$po->ShipToStreet1 = "Dodaci ulice 123";
$po->ShipToCity = "Dodaci Město";
$po->ShipToPostalCode = "99999";
$po->ShipToCountryISO = 203;
$po->ShipToPhone = "0911111111";
$po->ShipToEmail = "test@epayment.com";

$lang = 'sk';
if (in_array(strtolower($lang), GP_webpay::$VALID_LANGUAGES)) {
    $po->language = strtolower($lang);
}

try {
    $redirectURL = $payment->request($po, 'https://test.3dsecure.gpwebpay.com/pgw/order.do');
    ?>
    <a href="<?=$redirectURL;?>">Zaplatiť</a>
    <?php
} catch (EPaymentException $e) {
    echo "Error: ". $e->getMessage();
}