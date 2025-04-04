<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPayment\EPaymentLog;
use EPayment\VUB_eCard\VUBeCardPaymentRequest;
use EPayment\VUB_eCard\VUBeCardPaymentResponse;

class VUB_eCard extends Payment
{

    public $responseMessage;

    /**
     * VUB_EPlatba constructor.
     * @throws EPaymentException
     */
    public function __construct()
    {
        if (!defined('EPAYMENT_VUB_ECARD_CLIENT_ID')) {
            throw new EPaymentException('EPAYMENT_VUB_ECARD_CLIENT_ID is not defined');
        }

        if (!defined('EPAYMENT_VUB_ECARD_STORE_KEY')) {
            throw new EPaymentException('EPAYMENT_VUB_ECARD_STORE_KEY is not defined');
        }
    }

    /**
     * @param PaymentObject $paymentObject
     * @param null $endpoint
     * @return array
     * @throws EPaymentException
     */
    function request(PaymentObject $paymentObject, $endpoint = null)
    {
        $request = new VUBeCardPaymentRequest();

        if ($endpoint) {
            $request->setRedirectUrlBase($endpoint);
        }

        $request->clientid = EPAYMENT_VUB_ECARD_CLIENT_ID;
        $request->amount = sprintf("%01.2f", $paymentObject->amount);
        $request->oid = $paymentObject->variableSymbol;
        $request->okUrl = $paymentObject->returnUrl;
        $request->failUrl = $paymentObject->returnUrl;
        $request->rnd = substr(str_shuffle(MD5(microtime())), 0, 20);

        if (isset($paymentObject->currency)) {
            $request->currency = $paymentObject->currency;
        }

        if (isset($paymentObject->language) && in_array($paymentObject->language, VUBeCardPaymentRequest::VALID_LANGUAGES)) {
            $request->lang = $paymentObject->language;
        }

        $request->setOptionalFields($paymentObject);

        $request->validate();

        $request->signMessage(EPAYMENT_VUB_ECARD_STORE_KEY);

        $returnValue = [
            'action' => $request->getUrlBase(),
            'fields' => $request->getPaymentRequestFields()
        ];

        EPaymentLog::log("VUB_eCard REQUEST:\n" . json_encode($returnValue));

        return $returnValue;
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function response($fields = null)
    {

        $response = new VUBeCardPaymentResponse($fields);

        EPaymentLog::log("VUB_eCard RESPONSE:\n" . json_encode($response));

        $response->validate();

        $response->verifySignature(EPAYMENT_VUB_ECARD_STORE_KEY);

        $this->responseMessage = $response->ErrMsg ?: $response->mdErrorMsg;

        return $response->getPaymentResponse();

    }

    /**
     * @param $fields
     * @return PaymentResponseObject
     * @throws EPaymentException
     */
    function responseObject($fields = null)
    {
        $response = new VUBeCardPaymentResponse($fields);

        EPaymentLog::log("VUB_eCard RESPONSE:\n" . json_encode($response));

        $response->validate();

        $response->verifySignature(EPAYMENT_VUB_ECARD_STORE_KEY);

        $this->responseMessage = $response->ErrMsg ?: $response->mdErrorMsg;

        return new PaymentResponseObject(null, $response->ReturnOid, $response->xid, $response->getPaymentResponse(), $this->responseMessage);
    }
}