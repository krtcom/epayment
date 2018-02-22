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
     * @param PaymentObject $paymentObject
     * @param null $endpoint
     * @return string
     * @throws EPaymentException
     */
    function request(PaymentObject $paymentObject, $endpoint = null)
    {
        $request = new UniPlatbaPaymentRequest();

        if ($endpoint) {
            $request->setRedirectUrlBase($endpoint);
        }

        $request->MID = EPAYMENT_UCB_UNIPLATBA_MID;

        if (in_array(strtoupper($paymentObject->language), UniPlatbaPaymentRequest::VALID_LANGUAGES)) {
            $request->LNG = strtoupper($paymentObject->language);
        }

        $request->AMT = sprintf("%01.2f", $paymentObject->amount);
        $request->VS = $paymentObject->variableSymbol;
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