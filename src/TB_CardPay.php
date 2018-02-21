<?php

namespace EPayments;


use EPayment\EPaymentException;
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
        $request = new CardPayPaymentRequest();

        $REMOTE_ADDR = '1.2.3.4';

        if (isset($_SERVER["REMOTE_ADDR"])) {
            $REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
        }
        
        $request->MID = EPAYMENT_TB_CARDPAY_MID;
        $request->AMT = sprintf("%01.2f", $amount);
        $request->CURR = "978";
        $request->VS = $variableSymbol;
        $request->RURL = $returnUrl;
        $request->IPC = $REMOTE_ADDR;

        $transliterator = Transliterator::create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC;");
        $name = trim($transliterator->transliterate($name));
        if (mb_strlen($name) > 30) {
            $name = mb_substr($name, 0, 30);
        }

        $request->NAME = $name;
        $request->TIMESTAMP = gmdate("dmYHis");

        if (in_array($language, CardPayPaymentRequest::VALID_LANGUAGES)) {
            $request->LANG = $language;
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

        $response->validate();

        $response->verifySignature(EPAYMENT_TB_CARDPAY_SECRET);

        return $response->getPaymentResponse();

    }
}