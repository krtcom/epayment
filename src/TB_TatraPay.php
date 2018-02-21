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
        $request = new TatraPayPaymentRequest();

        $REMOTE_ADDR = '1.2.3.4';

        if (isset($_SERVER["REMOTE_ADDR"])) {
            $REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
        }
        
        $request->MID = EPAYMENT_TB_TATRAPAY_MID;
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

        if (in_array($language, TatraPayPaymentRequest::VALID_LANGUAGES)) {
            $request->LANG = $language;
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