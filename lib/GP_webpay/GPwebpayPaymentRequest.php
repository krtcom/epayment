<?php

namespace EPayment\GP_webpay;

use EPayment\EPaymentException;
use EPayment\EPaymentMessage;
use EPayment\Interfaces\IEPaymentHttpRedirectPaymentRequest;

class GPwebpayPaymentRequest extends EPaymentMessage implements IEPaymentHttpRedirectPaymentRequest {
    
    const GPwebpay_EPayment_URL_Base = "https://3dsecure.gpwebpay.com/pgw/order.do";

    protected $redirectUrlBase = self::GPwebpay_EPayment_URL_Base;
    private $privateKeyFile;
    private $privateKeyPass;

    public function setRedirectUrlBase($url) {
        $this->redirectUrlBase = $url;
    }

    public function __construct() {
        $this->requiredFields = array('MERCHANTNUMBER', 'OPERATION', 'ORDERNUMBER', 'AMOUNT', 'CURRENCY', 'DEPOSITFLAG', 'URL');
        $this->optionalFields = array('MERORDERNUM', 'DESCRIPTION', 'MD', 'USERPARAM1', 'FASTPAYID', 'PAYMETHOD', 'DISABLEPAYMETHOD', 'PAYMETHODS', 'EMAIL', 'REFERENCENUMBER', 'ADDINFO', 'LANG');
    }

    public function getPrivateKeyFile()
    {
        return $this->privateKeyFile;
    }

    public function setPrivateKeyFile($privateKeyFile)
    {
        $this->privateKeyFile = $privateKeyFile;
    }

    public function getPrivateKeyPass()
    {
        return $this->privateKeyPass;
    }

    public function setPrivateKeyPass($privateKeyPass)
    {
        $this->privateKeyPass = $privateKeyPass;
    }

    protected function validateData() {
        return true;
    }

    protected function getSignatureBase() {
        return implode("|", array_filter($this->fields));
    }

    /**
     * @param $sharedSecret
     * @throws EPaymentException
     */
    public function signMessage($sharedSecret) {

        if (!$this->isValid) {
            throw new EPaymentException(__METHOD__ . ": Message was not validated.");
        }

        $pkeyid = openssl_get_privatekey("file://" . $this->privateKeyFile, $this->privateKeyPass);
        if ($pkeyid === false) {
            throw new EPaymentException(__METHOD__ . ": Private key error.");
        }
        openssl_sign($this->getSignatureBase(), $signature, $pkeyid);
        $signature = base64_encode($signature);
        openssl_free_key($pkeyid);
        $this->fields["DIGEST"] =  $signature;
    }

    public function getRedirectUrl() {
        return $this->redirectUrlBase . '?' . http_build_query(array_filter($this->fields));
    }

    public function computeSign($sharedSecret) {}
}
