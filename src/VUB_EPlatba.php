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
     * @param PaymentObject $paymentObject
     * @param null $endpoint
     * @param null $endpoint
     * @return array
     * @throws EPaymentException
     */
    function request(PaymentObject $paymentObject, $endpoint = null)
    {
        $request = new EPlatbaPaymentRequest();

        if ($endpoint) {
            $request->setRedirectUrlBase($endpoint);
        }

        $request->MID = EPAYMENT_VUB_EPLATBA_MID;
        $request->AMT = sprintf("%01.2f", $paymentObject->amount);
        $request->VS = $paymentObject->variableSymbol;
        $request->CS = '0308';
        $request->RURL = $paymentObject->returnUrl;

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