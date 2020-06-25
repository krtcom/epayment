<?php
namespace EPayments;

use EPayment\EPaymentException;
use EPayment\GP_webpay\GPwebpayPaymentRequest;
use EPayment\GP_webpay\GPwebpayPaymentResponse;

class GP_webpay extends Payment
{

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
        $request->ORDERNUMBER = $paymentObject->orderID . substr(microtime(), 2, 6);       // cislo platby
        $request->AMOUNT = (int)($paymentObject->amount * 100);    // suma (v eurocentoch)
        $request->CURRENCY = 978;                            // ISO 4217 kod pre menu EUR
        $request->DEPOSITFLAG = 1;                           // okamzita uhrada
        $request->MERORDERNUM = $paymentObject->variableSymbol; // variabilny symbol
        $request->URL = $paymentObject->returnUrl;    // návratová URL, na ktorú bude zaslaný payment response
        $request->REFERENCENUMBER = sprintf("%010d", $paymentObject->variableSymbol);
        $request->MD = $paymentObject->amount;    // vlastny parameter (suma)

        $request->validate();

        $request->signMessage(null);

        return $request->getRedirectUrl();
    }

    function response($fields = null)
    {
        $pres = new GPwebpayPaymentResponse($fields);
        $pres->setPublicKeyFile(EPAYMENT_GP_WEBPAY_PUBLIC_KEY_FILE);

        $pres->validate();

        $pres->verifySignature(EPAYMENT_GP_WEBPAY_MID);

        return $pres->getPaymentResponse();
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
        $PaymentResponseObject = new PaymentResponseObject($response->MD, $response->MERORDERNUM, $response->ORDERNUMBER, $response->getPaymentResponse());

        $this->transactionId = $response->TID;

        return $PaymentResponseObject;
    }
}
