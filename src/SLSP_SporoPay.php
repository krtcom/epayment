<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPayment\SLSP_SporoPay\SporoPayPaymentHttpResponse;
use EPayment\SLSP_SporoPay\SporoPayPaymentRequest;

class SLSP_SporoPay extends Payment
{

    /**
     * SLSP_SporoPay constructor.
     * @throws EPaymentException
     */
    public function __construct()
    {
        if (!defined('EPAYMENT_SLSP_SPOROPAY_SECRET')) {
            throw new EPaymentException('EPAYMENT_SLSP_SPOROPAY_SECRET is not defined');
        }

        if (!defined('EPAYMENT_SLSP_SPOROPAY_PU_PREDCISLO')) {
            throw new EPaymentException('EPAYMENT_SLSP_SPOROPAY_PU_PREDCISLO is not defined');
        }

        if (!defined('EPAYMENT_SLSP_SPOROPAY_PU_CISLO')) {
            throw new EPaymentException('EPAYMENT_SLSP_SPOROPAY_PU_CISLO is not defined');
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
        $request = new SporoPayPaymentRequest();

        $request->pu_predcislo = EPAYMENT_SLSP_SPOROPAY_PU_PREDCISLO;
        $request->pu_cislo = EPAYMENT_SLSP_SPOROPAY_PU_CISLO;
        $request->suma = sprintf("%01.2f", $amount);
        $request->vs = $variableSymbol;
        $request->ss = '0308';
        $request->url = $returnUrl;
        $request->param = '';

        $request->validate();

        $request->signMessage(EPAYMENT_SLSP_SPOROPAY_SECRET);

        return $request->getRedirectUrl();
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function response($fields = null)
    {

        $response = new SporoPayPaymentHttpResponse($fields);

        $response->validate();

        $response->verifySignature(EPAYMENT_SLSP_SPOROPAY_SECRET);

        return $response->getPaymentResponse();

    }
}