<?php

namespace EPayments;


use EPayment\EPaymentException;
use EPayment\SK_24Pay\SK24PayPaymentNotification;
use EPayment\SK_24Pay\SK24PayPaymentRequest;
use EPayment\SK_24Pay\SK24PayPaymentResponse;
use Transliterator;

class SK_24Pay extends PaymentOnBackground
{

    /**
     * Payment constructor.
     * @throws EPaymentException
     */
    public function __construct()
    {
        if (!defined('EPAYMENT_SK_24PAY_MID')) {
            throw new EPaymentException('EPAYMENT_SK_24PAY_MID is not defined');
        }

        if (!defined('EPAYMENT_SK_24PAY_ESHOPID')) {
            throw new EPaymentException('EPAYMENT_SK_24PAY_ESHOPID is not defined');
        }

        if (!defined('EPAYMENT_SK_24PAY_SECRET')) {
            throw new EPaymentException('EPAYMENT_SK_24PAY_SECRET is not defined');
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

        $request = new SK24PayPaymentRequest();

        if ($endpoint) {
            $request->setRedirectUrlBase($endpoint);
        }

        $request->Mid = EPAYMENT_SK_24PAY_MID;
        $request->EshopId = EPAYMENT_SK_24PAY_ESHOPID;
        $request->Amount = sprintf("%01.2f", $paymentObject->amount);
        $request->CurrAlphaCode = "EUR";
        $request->ClientId = sprintf("%03d", $paymentObject->userId);
        $request->MsTxnId = $paymentObject->variableSymbol;

        $transliterator = Transliterator::create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC;");
        $name = trim($transliterator->transliterate($paymentObject->name));

        $names = explode(" ", $name);
        $FamilyName = array_pop($names);
        $FirstName = implode(" ", $names);

        $request->FirstName = $FirstName;
        $request->FamilyName = $FamilyName;

        $request->Email = $paymentObject->email;
        $request->Country = 'SVK';
        $request->Timestamp = date("Y-m-d H:i:s");

        if ($paymentObject->returnUrl) {
            $request->RURL = $paymentObject->returnUrl;
        }

        if ($paymentObject->notificationUrl) {
            $request->NURL = $paymentObject->notificationUrl;
        }

        $request->RedirectSign = 'true';

        $request->validate();

        $request->signMessage(EPAYMENT_SK_24PAY_SECRET);

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
        $response = new SK24PayPaymentResponse($fields);

        $response->Mid = EPAYMENT_SK_24PAY_MID;

        $response->validate();

        $response->verifySignature(EPAYMENT_SK_24PAY_SECRET);

        return $response->getPaymentResponse();
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function notification($fields = null)
    {
        $response = new SK24PayPaymentNotification($fields);

        $response->Mid = EPAYMENT_SK_24PAY_MID;

        $response->validate();

        $response->verifySignature(EPAYMENT_SK_24PAY_SECRET);

        return $response->getPaymentResponse();
    }
}