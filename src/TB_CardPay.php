<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPayment\EPaymentLog;
use EPayment\TB_CardPay_v4_HMAC\CardPayPaymentHttpResponse;
use EPayment\TB_CardPay_v4_HMAC\CardPayPaymentRequest;
use Transliterator;

class TB_CardPay extends Payment
{

    /**
     * TB_CardPay constructor.
     * @throws EPaymentException
     */
    public function __construct()
    {
        if (!defined('EPAYMENT_TB_CARDPAY_MID')) {
            throw new EPaymentException('EPAYMENT_TB_CARDPAY_MID is not defined');
        }

        if (!defined('EPAYMENT_TB_CARDPAY_SECRET')) {
            throw new EPaymentException('EPAYMENT_TB_CARDPAY_SECRET is not defined');
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
        $request = new CardPayPaymentRequest();

        if ($endpoint) {
            $request->setRedirectUrlBase($endpoint);
        }

        $REMOTE_ADDR = '1.2.3.4';

        if (isset($_SERVER["REMOTE_ADDR"])) {
            $REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
        }
        
        $request->MID = EPAYMENT_TB_CARDPAY_MID;
        $request->AMT = sprintf("%01.2f", $paymentObject->amount);
        $request->CURR = "978";
        $request->VS = $paymentObject->variableSymbol;
        $request->RURL = $paymentObject->returnUrl;
        $request->IPC = $REMOTE_ADDR;

        $transliterator = Transliterator::create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC;");
        $name = trim($transliterator->transliterate($paymentObject->name));
        if (mb_strlen($name) > 30) {
            $name = mb_substr($name, 0, 30);
        }

        $request->NAME = $name;
        $request->TIMESTAMP = gmdate("dmYHis");

        if (in_array($paymentObject->language, CardPayPaymentRequest::VALID_LANGUAGES)) {
            $request->LANG = $paymentObject->language;
        }

        $request->validate();

        $request->signMessage(EPAYMENT_TB_CARDPAY_SECRET);

        return $request->getRedirectUrl();
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function response($fields = null)
    {

        $response = new CardPayPaymentHttpResponse($fields);

        EPaymentLog::log("TB_CardPay RESPONSE:\n" . print_r($response, true));

        $response->validate();

        $response->verifySignature(EPAYMENT_TB_CARDPAY_SECRET);

        $this->transactionId = $response->TID;

        return $response->getPaymentResponse();

    }

    /**
     * @param null $fields
     * @return PaymentResponseObject
     * @throws EPaymentException
     */
    function responseObject($fields = null)
    {

        $response = new CardPayPaymentHttpResponse($fields);

        EPaymentLog::log("TB_CardPay RESPONSE:\n" . print_r($response, true));

        $response->validate();

        $response->verifySignature(EPAYMENT_TB_CARDPAY_SECRET);

        $PaymentResponseObject = new PaymentResponseObject($response->AMT, $response->VS, $response->TID, $response->getPaymentResponse());

        $this->transactionId = $response->TID;

        return $PaymentResponseObject;

    }
}