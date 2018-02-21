<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPayment\VUB_EPlatba2_HMAC\EPlatbaPaymentHttpResponse;
use EPayment\VUB_EPlatba2_HMAC\EPlatbaPaymentRequest;

class VUB_EPlatba extends Payment
{

    /**
     * VUB_EPlatba constructor.
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

        if (!defined('EPAYMENT_VUB_EPLATBA_MID')) {
            throw new EPaymentException('EPAYMENT_VUB_EPLATBA_MID is not defined');
        }

        if (!defined('EPAYMENT_VUB_EPLATBA_SECRET')) {
            throw new EPaymentException('EPAYMENT_VUB_EPLATBA_SECRET is not defined');
        }

    }

    /**
     * @return array
     * @throws EPaymentException
     */
    function request()
    {
        $request = new EPlatbaPaymentRequest();

        $request->MID = EPAYMENT_VUB_EPLATBA_MID;
        $request->AMT = sprintf("%01.2f", $this->amount);
        $request->VS = $this->variableSymbol;
        $request->CS = '0308';
        $request->RURL = $this->returnUrl;

        $request->validate();

        $request->signMessage(EPAYMENT_VUB_EPLATBA_SECRET);

        return [
            'action' => $request->getUrlBase(),
            'fields' => $request->getPaymentRequestFields()
        ];
    }

    /**
     * @return int
     * @throws EPaymentException
     */
    function response()
    {

        $response = new EPlatbaPaymentHttpResponse();

        $response->validate();

        $response->verifySignature(EPAYMENT_VUB_EPLATBA_SECRET);

        return $response->getPaymentResponse();

    }
}