<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPayment\VUB_EPlatba2_HMAC\EPlatbaPaymentHttpResponse;
use EPayment\VUB_EPlatba2_HMAC\EPlatbaPaymentRequest;

class VUB_EPlatba extends Payment
{

    /**
     * VUB_EPlatba constructor.
     * @throws EPaymentException
     */
    public function __construct()
    {
        if (!defined('EPAYMENT_VUB_EPLATBA_MID')) {
            throw new EPaymentException('EPAYMENT_VUB_EPLATBA_MID is not defined');
        }

        if (!defined('EPAYMENT_VUB_EPLATBA_SECRET')) {
            throw new EPaymentException('EPAYMENT_VUB_EPLATBA_SECRET is not defined');
        }
    }

    /**
     * @param $amount
     * @param $variableSymbol
     * @param null $returnUrl
     * @param null $name
     * @param null $language
     * @return array
     * @throws EPaymentException
     */
    function request($amount, $variableSymbol, $returnUrl = null, $name = null, $language = null)
    {
        $request = new EPlatbaPaymentRequest();

        $request->MID = EPAYMENT_VUB_EPLATBA_MID;
        $request->AMT = sprintf("%01.2f", $amount);
        $request->VS = $variableSymbol;
        $request->CS = '0308';
        $request->RURL = $returnUrl;

        $request->validate();

        $request->signMessage(EPAYMENT_VUB_EPLATBA_SECRET);

        return [
            'action' => $request->getUrlBase(),
            'fields' => $request->getPaymentRequestFields()
        ];
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function response($fields = null)
    {

        $response = new EPlatbaPaymentHttpResponse($fields);

        $response->validate();

        $response->verifySignature(EPAYMENT_VUB_EPLATBA_SECRET);

        return $response->getPaymentResponse();

    }
}