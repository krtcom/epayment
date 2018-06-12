<?php

namespace EPayments;


use EPayment\Ecomm\EcommMerchant;
use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;

class PB_iTerminal extends Payment
{
    private $url;
    private $keystore;
    private $keystorepassword;
    private $verbose = 0;

    function __construct()
    {

        if (!defined('EPAYMENT_ECOMM_MERCHANT_URL')) {
            throw new EPaymentException('EPAYMENT_ECOMM_MERCHANT_URL is not defined');
        }

        if (!defined('EPAYMENT_ECOMM_MERCHANT_KEYSTORE')) {
            throw new EPaymentException('EPAYMENT_ECOMM_MERCHANT_KEYSTORE is not defined');
        }

        if (!defined('EPAYMENT_ECOMM_MERCHANT_KEYSTOREPASSWORD')) {
            throw new EPaymentException('EPAYMENT_ECOMM_MERCHANT_KEYSTOREPASSWORD is not defined');
        }

        $this->url = EPAYMENT_ECOMM_MERCHANT_URL;
        $this->keystore = EPAYMENT_ECOMM_MERCHANT_KEYSTORE;
        $this->keystorepassword = EPAYMENT_ECOMM_MERCHANT_KEYSTOREPASSWORD;

        if (defined('EPAYMENT_ECOMM_MERCHANT_VERBOSE')) {
            $this->verbose = EPAYMENT_ECOMM_MERCHANT_VERBOSE;
        }
    }

    /**
     * @param PaymentObject $paymentObject
     * @param null $endpoint
     * @return mixed
     * @throws EPaymentException
     */
    function request(PaymentObject $paymentObject, $endpoint = null)
    {

        $merchant = new EcommMerchant($this->url . '/ecomm/MerchantHandler', $this->keystore, $this->keystorepassword, $this->verbose);

        $client_ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null;

        $resp = $merchant->startSMSTrans((int)($paymentObject->amount * 100), 987, $client_ip, $paymentObject->name, $paymentObject->language, $paymentObject->variableSymbol);

        if (substr($resp, 0, 14) != "TRANSACTION_ID") {
            $error = "Response error: Unknown TRANSACTION_ID.";
            if (defined("EPAYMENT_DEBUG") && EPAYMENT_DEBUG) {
                $error .= " [$resp]";
            }
            throw new EPaymentException($error);
        }

        $trans_id = urlencode(substr($resp, 16, 28));
        return $this->url . "/ecomm/ClientHandler?trans_id=$trans_id";
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function response($fields = null)
    {

        $trans_id = $fields['trans_id'];

        $client_ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null;

        $merchant = new EcommMerchant($this->url . '/ecomm/MerchantHandler', $this->keystore, $this->keystorepassword, $this->verbose);
        $resp = $merchant->getTransResult($trans_id, $client_ip);

        $responseRows = preg_split("/\r\n|\n|\r/", $resp);
        foreach ($responseRows as $responseRow) {
            list($key, $value) = array_map('trim', explode(":", $responseRow));
            if ($key == 'RESULT') {
                switch($value) {
                    case 'OK':
                        return IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS;
                        break;
                    case 'FAILED':
                    case 'DECLINED':
                    case 'REVERSED':
                        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
                    break;
                    case 'TIMEOUT':
                        return IEPaymentHttpPaymentResponse::RESPONSE_TIMEOUT;
                    case 'PENDING':
                    return IEPaymentHttpPaymentResponse::RESPONSE_PENDING;
                        break;
                }
            }
        }
        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
    }
}