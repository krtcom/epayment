<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPaymentment\VB_VeBpay\VeBpayPaymentHttpResponse;
use EPaymentment\VB_VeBpay\VeBpayPaymentRequest;

class VB_VeBpay extends Payment
{

    /**
     * VB_VeBpay constructor.
     * @throws EPaymentException
     */
    public function __construct()
    {
        if (!defined('EPAYMENT_VB_VEBPAY_MID')) {
            throw new EPaymentException('EPAYMENT_VB_VEBPAY_MID is not defined');
        }

        if (!defined('EPAYMENT_VB_VEBPAY_SECRET')) {
            throw new EPaymentException('EPAYMENT_VB_VEBPAY_SECRET is not defined');
        }
    }

    /**
     * @param PaymentObject $paymentObject
     * @param null $endpoint
     * @return string
     * @throws EPaymentException
     */
    function request(PaymentObject $paymentObject, $endpoint = null)
    {

        $request = new VeBpayPaymentRequest();

        if ($endpoint) {
            $request->setRedirectUrlBase($endpoint);
        }

        $request->MID = EPAYMENT_VB_VEBPAY_MID;
        $request->AMT = sprintf("%01.2f", $paymentObject->amount);
        $request->VS = $paymentObject->variableSymbol;
        $request->CS = '0308';
        $request->RURL = $paymentObject->returnUrl;

        $request->validate();

        $request->signMessage(EPAYMENT_VB_VEBPAY_SECRET);

        return $request->getRedirectUrl();
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function response($fields = null)
    {

        $response = new VeBpayPaymentHttpResponse($fields);

        $response->validate();

        $response->verifySignature(EPAYMENT_VB_VEBPAY_SECRET);

        return $response->getPaymentResponse();

    }
}