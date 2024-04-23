<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use EPayment\EPaymentException;
use EPayment\VUB_eCard\VUBeCardPaymentRequest;
use EPayments\PaymentObject;
use EPayments\VUB_eCard;

define('EPAYMENT_VUB_ECARD_CLIENT_ID', "");
define('EPAYMENT_VUB_ECARD_STORE_KEY', "");

$payment = new VUB_eCard();

$po = new PaymentObject(6.5, '123456');
$po->email = 'info@example.com';
$po->name = 'Jožko Mrkvička';
$po->userId = 47;
$po->returnUrl = 'https://epayment.devel.webcreators.sk/examples/VUB_eCard/response.php';

try {
    $request = $payment->request($po, VUBeCardPaymentRequest::URL_BASE_TEST);
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