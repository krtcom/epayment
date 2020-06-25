<?php
namespace EPayments;

use EPayment\EPaymentException;
use EPayment\GP_webpay\GPwebpayPaymentRequest;
use EPayment\GP_webpay\GPwebpayPaymentResponse;

class GP_webpay extends Payment
{

    const VALID_LANGUAGES = ['ar', 'de_AT', 'bg', 'hr', 'cs', 'da', 'nl', 'en', 'fi', 'fr', 'de', 'el', 'hu', 'it', 'ja', 'lv', 'no', 'pl', 'pt', 'ro', 'ru', 'sk', 'es', 'sl', 'sv', 'uk', 'vi'];

    public function __construct()
    {
        if (!defined('EPAYMENT_GP_WEBPAY_MID')) {
            throw new EPaymentException('EPAYMENT_GP_WEBPAY_MID is not defined');
        }

        if (!defined('EPAYMENT_GP_WEBPAY_PRIVATE_KEY_FILE')) {
            throw new EPaymentException('EPAYMENT_GP_WEBPAY_PRIVATE_KEY_FILE is not defined');
        }

        if (!defined('EPAYMENT_GP_WEBPAY_PRIVATE_KEY_PASS')) {
            throw new EPaymentException('EPAYMENT_GP_WEBPAY_PRIVATE_KEY_PASS is not defined');
        }

        if (!defined('EPAYMENT_GP_WEBPAY_PUBLIC_KEY_FILE')) {
            throw new EPaymentException('EPAYMENT_GP_WEBPAY_PUBLIC_KEY_FILE is not defined');
        }
    }

    function request(PaymentObject $paymentObject, $endpoint = null)
    {
        $request = new GPwebpayPaymentRequest();

        if ($endpoint) {
            $request->setRedirectUrlBase($endpoint);
        }
        
        $request->setPrivateKeyFile(EPAYMENT_GP_WEBPAY_PRIVATE_KEY_FILE);
        $request->setPrivateKeyPass(EPAYMENT_GP_WEBPAY_PRIVATE_KEY_PASS);

        $request->MERCHANTNUMBER = EPAYMENT_GP_WEBPAY_MID;  // cislo obchodu
        $request->OPERATION = "CREATE_ORDER";
        $request->ORDERNUMBER = $paymentObject->orderID . substr(microtime(), 2, 6);       // cislo platby musi byt unikatne pre kazdu poziadavku
        $request->AMOUNT = (int)($paymentObject->amount * 100);    // suma (v eurocentoch)
        $request->CURRENCY = 978;                            // ISO 4217 kod pre menu EUR
        $request->DEPOSITFLAG = 1;                           // okamzita uhrada
        $request->MERORDERNUM = $paymentObject->variableSymbol; // variabilny symbol
        $request->URL = $paymentObject->returnUrl;    // návratová URL, na ktorú bude zaslaný payment response
        $request->MD = $paymentObject->amount;    // vlastny parameter (suma)

        if (in_array($paymentObject->language, self::VALID_LANGUAGES)) {
            $request->LANG = $paymentObject->language;
        }

        $request->validate();

        $request->signMessage(null);

        return $request->getRedirectUrl();
    }

    function response($fields = null)
    {
        $response = new GPwebpayPaymentResponse($fields);
        $response->setPublicKeyFile(EPAYMENT_GP_WEBPAY_PUBLIC_KEY_FILE);

        $response->validate();

        $response->verifySignature(EPAYMENT_GP_WEBPAY_MID);

        return $response->getPaymentResponse();
    }

    /**
     * @param null $fields
     * @return PaymentResponseObject
     * @throws EPaymentException
     */
    function responseObject($fields = null) {
        $response = new GPwebpayPaymentResponse($fields);
        $response->setPublicKeyFile(EPAYMENT_GP_WEBPAY_PUBLIC_KEY_FILE);

        $response->validate();

        $response->verifySignature(EPAYMENT_GP_WEBPAY_MID);

        $this->transactionId = $response->ORDERNUMBER;

        return new PaymentResponseObject($response->MD, $response->MERORDERNUM, $response->ORDERNUMBER, $response->getPaymentResponse());
    }
}
