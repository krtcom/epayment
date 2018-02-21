<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPaymentment\VB_VeBpay\VeBpayPaymentHttpResponse;
use EPaymentment\VB_VeBpay\VeBpayPaymentRequest;

class VB_VeBpay extends Payment
{

    /**
     * VB_VeBpay constructor.
     * @param null $amount
     * @param null $variableSymbol
     * @param null $returnUrl
     * @param null $name
     * @param null $language
     * @throws EPaymentException
     */
    public function __construct($amount, $variableSymbol, $returnUrl = null, $name = null, $language = null)
    {
        parent::__construct($amount, $variableSymbol, $returnUrl, $name, $language);

        if (!defined('EPAYMENT_VB_VEBPAY_MID')) {
            throw new EPaymentException('EPAYMENT_VB_VEBPAY_MID is not defined');
        }

        if (!defined('EPAYMENT_VB_VEBPAY_SECRET')) {
            throw new EPaymentException('EPAYMENT_VB_VEBPAY_SECRET is not defined');
        }

    }

    /**
     * @return array
     * @throws EPaymentException
     */
    function request()
    {
        $request = new VeBpayPaymentRequest();

        $request->MID = EPAYMENT_VB_VEBPAY_MID;
        $request->AMT = sprintf("%01.2f", $this->amount);
        $request->VS = $this->variableSymbol;
        $request->CS = '0308';
        $request->RURL = $this->returnUrl;

        $request->validate();

        $request->signMessage(EPAYMENT_VB_VEBPAY_SECRET);

        return $request->getRedirectUrl();
    }

    /**
     * @return int
     * @throws EPaymentException
     */
    function response()
    {

        $response = new VeBpayPaymentHttpResponse();

        $response->validate();

        $response->verifySignature(EPAYMENT_VB_VEBPAY_SECRET);

        return $response->getPaymentResponse();

    }
}