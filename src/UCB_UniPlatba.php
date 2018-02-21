<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPayment\UCB_UniPlatba\UniPlatbaPaymentHttpResponse;
use EPayment\UCB_UniPlatba\UniPlatbaPaymentRequest;

class UCB_UniPlatba extends Payment
{

    /**
     * UCB_UniPlatba constructor.
     * @throws EPaymentException
     */
    public function __construct()
    {
        if (!defined('EPAYMENT_UCB_UNIPLATBA_MID')) {
            throw new EPaymentException('EPAYMENT_UCB_UNIPLATBA_MID is not defined');
        }

        if (!defined('EPAYMENT_UCB_UNIPLATBA_SECRET')) {
            throw new EPaymentException('EPAYMENT_UCB_UNIPLATBA_SECRET is not defined');
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
        $request = new UniPlatbaPaymentRequest();

        $request->MID = EPAYMENT_UCB_UNIPLATBA_MID;

        if (in_array(strtoupper($language), UniPlatbaPaymentRequest::VALID_LANGUAGES)) {
            $request->LNG = strtoupper($language);
        }

        $request->AMT = sprintf("%01.2f", $amount);
        $request->VS = $variableSymbol;
        $request->CS = '0308';

        $request->validate();

        $request->signMessage(EPAYMENT_UCB_UNIPLATBA_SECRET);

        return $request->getRedirectUrl();
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function response($fields = null)
    {

        $response = new UniPlatbaPaymentHttpResponse($fields);

        $response->validate();

        $response->verifySignature(EPAYMENT_UCB_UNIPLATBA_SECRET);

        return $response->getPaymentResponse();

    }
}