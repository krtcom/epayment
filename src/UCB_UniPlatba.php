<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPayment\UCB_UniPlatba\UniPlatbaPaymentHttpResponse;
use EPayment\UCB_UniPlatba\UniPlatbaPaymentRequest;

class UCB_UniPlatba extends Payment
{

    /**
     * UCB_UniPlatba constructor.
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

        if (!defined('EPAYMENT_UCB_UNIPLATBA_MID')) {
            throw new EPaymentException('EPAYMENT_UCB_UNIPLATBA_MID is not defined');
        }

        if (!defined('EPAYMENT_UCB_UNIPLATBA_SECRET')) {
            throw new EPaymentException('EPAYMENT_UCB_UNIPLATBA_SECRET is not defined');
        }

    }

    /**
     * @return string
     * @throws EPaymentException
     */
    function request()
    {
        $request = new UniPlatbaPaymentRequest();

        $request->MID = EPAYMENT_UCB_UNIPLATBA_MID;

        if (in_array(strtoupper($this->language), UniPlatbaPaymentRequest::VALID_LANGUAGES)) {
            $request->LNG = strtoupper($this->language);
        }

        $request->AMT = sprintf("%01.2f", $this->amount);
        $request->VS = $this->variableSymbol;
        $request->CS = '0308';

        $request->validate();

        $request->signMessage(EPAYMENT_UCB_UNIPLATBA_SECRET);

        return $request->getRedirectUrl();
    }

    /**
     * @return int
     * @throws EPaymentException
     */
    function response()
    {

        $response = new UniPlatbaPaymentHttpResponse();

        $response->validate();

        $response->verifySignature(EPAYMENT_UCB_UNIPLATBA_SECRET);

        return $response->getPaymentResponse();

    }
}