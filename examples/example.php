<?php

require_once __DIR__ . '/../vendor/autoload.php';

use EPayments\PaymentObject;
use EPayments\TB_CardPay;

define('EPAYMENT_TB_CARDPAY_MID', "9999");
define('EPAYMENT_TB_CARDPAY_SECRET', "31323334353637383930313233343536373839303132333435363738393031323132333435363738393031323334353637383930313233343536373839303132");

$payment = new TB_CardPay();

$po = new PaymentObject(6.5, '123456', 'https://www.example.com', 'Jožko Mrkvička');

echo $payment->request($po);