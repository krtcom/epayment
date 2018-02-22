<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPayment\TB_TatraPay_v4_HMAC\TatraPayPaymentHttpResponse;
use EPayment\TB_TatraPay_v4_HMAC\TatraPayPaymentRequest;
use Transliterator;

class TB_TatraPay extends Payment
{

    /**
     * TB_TatraPay constructor.
     * @throws EPaymentException
     */
    public function __construct()
    {
        if (!defined('EPAYMENT_TB_TATRAPAY_MID')) {
            throw new EPaymentException('EPAYMENT_TB_TATRAPAY_MID is not defined');
        }

        if (!defined('EPAYMENT_TB_TATRAPAY_SECRET')) {
            throw new EPaymentException('EPAYMENT_TB_TATRAPAY_SECRET is not defined');
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
        $request = new TatraPayPaymentRequest();

        if ($endpoint) {
            $request->setRedirectUrlBase($endpoint);
        }

        $REMOTE_ADDR = '1.2.3.4';

        if (isset($_SERVER["REMOTE_ADDR"])) {
            $REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
        }
        
        $request->MID = EPAYMENT_TB_TATRAPAY_MID;
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

        if (in_array($paymentObject->language, TatraPayPaymentRequest::VALID_LANGUAGES)) {
            $request->LANG = $paymentObject->language;
        }

        $request->validate();

        $request->signMessage(EPAYMENT_TB_TATRAPAY_SECRET);

        return $request->getRedirectUrl();
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function response($fields = null)
    {

        $response = new TatraPayPaymentHttpResponse($fields);

        $response->validate();

        $response->verifySignature(EPAYMENT_TB_TATRAPAY_SECRET);

        return $response->getPaymentResponse();

    }
}