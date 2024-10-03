<?php

namespace EPayments;

use EPayment\EPaymentException;
use EPayment\GP_webpay\GPwebpayPaymentRequest;
use EPayment\GP_webpay\GPwebpayPaymentResponse;
use SimpleXMLElement;
use Transliterator;

class GP_webpay extends Payment
{

    public static $VALID_LANGUAGES = ['ar', 'de_AT', 'bg', 'hr', 'cs', 'da', 'nl', 'en', 'fi', 'fr', 'de', 'el', 'hu', 'it', 'ja', 'lv', 'no', 'pl', 'pt', 'ro', 'ru', 'sk', 'es', 'sl', 'sv', 'uk', 'vi'];
    public static $VALID_CURRENCIES = [978];

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

    function request(PaymentObject $paymentObject, $endpoint = null, $redirect = true)
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

        // ISO 4217 kod pre menu
        if (is_array(self::$VALID_CURRENCIES) && in_array($paymentObject->currency, self::$VALID_CURRENCIES)) {
            $request->CURRENCY = $paymentObject->currency;
        } else {
            $request->CURRENCY = 978; //EUR
        }

        $request->DEPOSITFLAG = 1;                           // okamzita uhrada
        $request->MERORDERNUM = $paymentObject->variableSymbol; // variabilny symbol
        $request->URL = $paymentObject->returnUrl;    // návratová URL, na ktorú bude zaslaný payment response
        $request->MD = $paymentObject->amount;    // vlastny parameter (suma)
        $request->REFERENCENUMBER = $paymentObject->variableSymbol; // variabilny symbol

        $request->ADDINFO = $this->createAddinfoElements($paymentObject);

        //jazyk aplikacie
        if (is_array(self::$VALID_LANGUAGES) && in_array($paymentObject->language, self::$VALID_LANGUAGES)) {
            $request->LANG = $paymentObject->language;
        } else {
            $request->LANG = 'sk';
        }

        $request->validate();

        $request->signMessage(null);

        if ($redirect) {
            return $request->getRedirectUrl();
        } else {
            return $request->getSubmitForm();
        }

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
    function responseObject($fields = null)
    {
        $response = new GPwebpayPaymentResponse($fields);
        $response->setPublicKeyFile(EPAYMENT_GP_WEBPAY_PUBLIC_KEY_FILE);

        $response->validate();

        $response->verifySignature(EPAYMENT_GP_WEBPAY_MID);

        $this->transactionId = $response->ORDERNUMBER;

        return new PaymentResponseObject($response->MD, $response->MERORDERNUM, $response->ORDERNUMBER, $response->getPaymentResponse());
    }

    private function createAddinfoElements(PaymentObject $paymentObject)
    {
        $transliterator = Transliterator::create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC;");

        $sxe = new SimpleXMLElement('<additionalInfoRequest xmlns="http://gpe.cz/gpwebpay/additionalInfo/request" version="5.0"></additionalInfoRequest>');

        $cardholderInfo = $sxe->addChild('cardholderInfo');

        $cardholderDetails = $cardholderInfo->addChild('cardholderDetails');
        $cardholderDetails->addChild('name', trim($transliterator->transliterate($paymentObject->name)));
        $cardholderDetails->addChild('email', $paymentObject->email);

        if (!empty($paymentObject->BillToName) &&
            !empty($paymentObject->BillToStreet1) &&
            !empty($paymentObject->BillToCity) &&
            !empty($paymentObject->BillToPostalCode) &&
            !empty($paymentObject->BillToCountryISO)
        ) {
            $billingDetails = $cardholderInfo->addChild('billingDetails');
            $billingDetails->addChild('name', $paymentObject->BillToName);
            $billingDetails->addChild('address1', $paymentObject->BillToStreet1);
            if (!empty($paymentObject->BillToStreet2)) {
                $billingDetails->addChild('address2', $paymentObject->BillToStreet2);
            }
            $billingDetails->addChild('city', $paymentObject->BillToCity);
            $billingDetails->addChild('postalCode', $paymentObject->BillToPostalCode);
            $billingDetails->addChild('country', $paymentObject->BillToCountryISO);
            if (!empty($paymentObject->BillToPhone)) {
                $billToPhone = str_replace("+", "00", $paymentObject->BillToPhone);
                $billToPhone = preg_replace('/\D+/', '', $billToPhone);
                $billingDetails->addChild('phone', $billToPhone);
            }
            if (!empty($paymentObject->BillToEmail)) {
                $billingDetails->addChild('email', $paymentObject->BillToEmail);
            }
        }

        if (!empty($paymentObject->ShipToName) &&
            !empty($paymentObject->ShipToStreet1) &&
            !empty($paymentObject->ShipToCity) &&
            !empty($paymentObject->ShipToPostalCode) &&
            !empty($paymentObject->ShipToCountryISO)
        ) {
            $shippingDetails = $cardholderInfo->addChild('shippingDetails');
            $shippingDetails->addChild('name', $paymentObject->ShipToName);
            $shippingDetails->addChild('address1', $paymentObject->ShipToStreet1);
            if (!empty($paymentObject->BillToStreet2)) {
                $shippingDetails->addChild('address2', $paymentObject->ShipToStreet2);
            }
            $shippingDetails->addChild('city', $paymentObject->ShipToCity);
            $shippingDetails->addChild('postalCode', $paymentObject->ShipToPostalCode);
            $shippingDetails->addChild('country', $paymentObject->ShipToCountryISO);
            if (!empty($paymentObject->ShipToPhone)) {
                $shipToPhone = str_replace("+", "00", $paymentObject->ShipToPhone);
                $shipToPhone = preg_replace('/\D+/', '', $shipToPhone);
                $shippingDetails->addChild('phone', $shipToPhone);
            }
            if (!empty($paymentObject->ShipToEmail)) {
                $shippingDetails->addChild('email', $paymentObject->ShipToEmail);
            }
            if (!empty($paymentObject->ShipToMethod)) {
                $shippingDetails->addChild('method', $paymentObject->ShipToMethod);
            }
        }

        $result = str_replace("<?xml version=\"1.0\"?>\n", '', $sxe->asXML());
        return trim($result);

    }
}
