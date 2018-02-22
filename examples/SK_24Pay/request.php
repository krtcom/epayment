<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use EPayment\EPaymentException;
use EPayments\PaymentObject;
use EPayments\SK_24Pay;

define('EPAYMENT_SK_24PAY_MID', "demoOMED");
define('EPAYMENT_SK_24PAY_ESHOPID', "11111111");
define('EPAYMENT_SK_24PAY_SECRET', "1234567812345678123456781234567812345678123456781234567812345678");

$payment = new SK_24Pay();

$po = new PaymentObject(6.5, '123456');

$po->email = 'info@example.com';
$po->name = 'Jožko Mrkvička';
$po->userId = 47;
$po->returnUrl = 'http://epayment.devel.webcreators.sk/examples/SK_24Pay/response.php';
$po->notificationUrl = 'http://epayment.devel.webcreators.sk/examples/SK_24Pay/notification.php';

try {
    $request = $payment->request($po, 'https://test.24-pay.eu/pay_gate/paygt');
    ?>
    <form action="<?= $request["action"]; ?>" method="post">
        <?php foreach ($request["fields"] as $name => $value) { ?>
            <input type="hidden" name="<?= $name; ?>" value="<?= $value; ?>" />
        <?php } ?>
        <button type="submit">Odoslať</button>
    </form>
    <?php
} catch (EPaymentException $e) {
    echo "Error: ". $e->getMessage();
}