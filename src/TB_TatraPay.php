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

        if (!defined('EPAYMENT_TB_TATRAPAY_MID')) {
            throw new EPaymentException('EPAYMENT_TB_TATRAPAY_MID is not defined');
        }

        if (!defined('EPAYMENT_TB_TATRAPAY_SECRET')) {
            throw new EPaymentException('EPAYMENT_TB_TATRAPAY_SECRET is not defined');
        }

    }

    /**
     * @return string
     * @throws EPaymentException
     */
    function request()
    {
        $request = new TatraPayPaymentRequest();

        $REMOTE_ADDR = '1.2.3.4';

        if (isset($_SERVER["REMOTE_ADDR"])) {
            $REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
        }
        
        $request->MID = EPAYMENT_TB_TATRAPAY_MID;
        $request->AMT = sprintf("%01.2f", $this->amount);
        $request->CURR = "978";
        $request->VS = $this->variableSymbol;
        $request->RURL = $this->returnUrl;
        $request->IPC = $REMOTE_ADDR;

        $transliterator = Transliterator::create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC;");
        $name = trim($transliterator->transliterate($this->name));
        if (mb_strlen($name) > 30) {
            $name = mb_substr($name, 0, 30);
        }

        $request->NAME = $name;
        $request->TIMESTAMP = gmdate("dmYHis");

        if (in_array($this->language, TatraPayPaymentRequest::VALID_LANGUAGES)) {
            $request->LANG = $this->language;
        }

        $request->validate();

        $request->signMessage(EPAYMENT_TB_TATRAPAY_SECRET);

        return $request->getRedirectUrl();
    }

    /**
     * @return int
     * @throws EPaymentException
     */
    function response()
    {

        $response = new TatraPayPaymentHttpResponse();

        $response->validate();

        $response->verifySignature(EPAYMENT_TB_TATRAPAY_SECRET);

        return $response->getPaymentResponse();

    }
}