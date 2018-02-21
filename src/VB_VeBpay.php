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
     * @param $amount
     * @param $variableSymbol
     * @param null $returnUrl
     * @param null $name
     * @param null $language
     * @return string
     * @throws EPaymentException
     */
    function request($amount, $variableSymbol, $returnUrl = null, $name = null, $language = null)
    {

        $request = new VeBpayPaymentRequest();

        $request->MID = EPAYMENT_VB_VEBPAY_MID;
        $request->AMT = sprintf("%01.2f", $amount);
        $request->VS = $variableSymbol;
        $request->CS = '0308';
        $request->RURL = $returnUrl;

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