<?php

namespace EPayments;


use EPayment\Ecomm\EcommMerchant;
use EPayment\EPaymentException;
use EPayment\EPaymentLog;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;

class PB_iTerminal extends Payment
{
    private $ecommServerUrl;
    private $ecommClientUrl;
    private $keystore;
    private $keystorepassword;
    private $verbose = 0;

    function __construct()
    {

        if (!defined('EPAYMENT_ECOMM_MERCHANT_SERVER_URL')) {
            throw new EPaymentException('EPAYMENT_ECOMM_MERCHANT_SERVER_URL is not defined');
        }

        if (!defined('EPAYMENT_ECOMM_MERCHANT_CLIENT_URL')) {
            throw new EPaymentException('EPAYMENT_ECOMM_MERCHANT_CLIENT_URL is not defined');
        }

        if (!defined('EPAYMENT_ECOMM_MERCHANT_KEYSTORE')) {
            throw new EPaymentException('EPAYMENT_ECOMM_MERCHANT_KEYSTORE is not defined');
        }

        if (!defined('EPAYMENT_ECOMM_MERCHANT_KEYSTOREPASSWORD')) {
            throw new EPaymentException('EPAYMENT_ECOMM_MERCHANT_KEYSTOREPASSWORD is not defined');
        }

        $this->ecommServerUrl = EPAYMENT_ECOMM_MERCHANT_SERVER_URL;
        $this->ecommClientUrl = EPAYMENT_ECOMM_MERCHANT_CLIENT_URL;
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
        EPaymentLog::log("REQUEST Payment object:\n" . print_r($paymentObject, true));

        $merchant = new EcommMerchant($this->ecommServerUrl, $this->keystore, $this->keystorepassword, $this->verbose);

        $client_ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null;

        $responseObject = $merchant->startSMSTrans((int)round($paymentObject->amount * 100), 978, $client_ip, $paymentObject->name, $paymentObject->language, $paymentObject->variableSymbol);

        if (empty($responseObject->TRANSACTION_ID)) {
            $error = "Response error: Unknown TRANSACTION_ID.";
            if (defined("EPAYMENT_DEBUG") && EPAYMENT_DEBUG) {
                $error .= " [". print_r($responseObject, true)."]";
            }
            throw new EPaymentException($error);
        }

        $this->transactionId = $responseObject->TRANSACTION_ID;

        $trans_id = urlencode($responseObject->TRANSACTION_ID);
        return $this->ecommClientUrl . "?trans_id=$trans_id";
    }

    /**
     * @param null $fields
     * @return int
     * @throws EPaymentException
     */
    function response($fields = null)
    {

        if ($fields == null) {
            $fields = $_POST;
        }

        $trans_id = $fields['trans_id'];

        $client_ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null;

        $merchant = new EcommMerchant($this->ecommServerUrl, $this->keystore, $this->keystorepassword, $this->verbose);
        $responseObject = $merchant->getTransResult(urlencode($trans_id), $client_ip);

        if (defined("EPAYMENT_DEBUG") && EPAYMENT_DEBUG) {
            var_dump($fields, $responseObject); exit;
        }

        EPaymentLog::log("RESPONSE POST Fields:\n" . print_r($fields, true) . "\nResponse object:\n" . print_r($responseObject, true));

        switch($responseObject->RESULT) {
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
        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
    }
}