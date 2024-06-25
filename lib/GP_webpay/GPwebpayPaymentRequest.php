<?php

namespace EPayment\GP_webpay;

use DOMDocument;
use EPayment\EPaymentException;
use EPayment\EPaymentMessage;
use EPayment\Interfaces\IEPaymentHttpRedirectPaymentRequest;

class GPwebpayPaymentRequest extends EPaymentMessage implements IEPaymentHttpRedirectPaymentRequest
{

    const GPwebpay_EPayment_URL_Base = "https://3dsecure.gpwebpay.com/pgw/order.do";

    protected $redirectUrlBase = self::GPwebpay_EPayment_URL_Base;
    private $privateKeyFile;
    private $privateKeyPass;

    public function setRedirectUrlBase($url)
    {
        $this->redirectUrlBase = $url;
    }

    public function __construct()
    {
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

    protected function validateData()
    {

        if (empty($this->ADDINFO)) {
            return true;
        }

        $doc = new DOMDocument();
        $doc->loadXML($this->ADDINFO);

        if (!$doc->schemaValidate(__DIR__ . '/GPwebpayAdditionalInfoRequest-v5.0.xsd')) {
            throw new EPaymentException("Invalid AdditionalInfoRequest");
        }

        return true;
    }

    protected function getSignatureBase()
    {
        $signFields = array_filter($this->fields);
        unset($signFields["LANG"]);
        return implode("|", $signFields);
    }

    /**
     * @param $sharedSecret
     * @throws EPaymentException
     */
    public function signMessage($sharedSecret)
    {

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
        $this->fields["DIGEST"] = $signature;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrlBase . '?' . http_build_query(array_filter($this->fields));
    }

    public function getSubmitForm()
    {
        $result = '<form action="' . $this->redirectUrlBase . '" id="opsubmitform">';
        foreach ($this->fields as $field => $value) {
            $result .= '<input type="hidden" name="' . $field . '" value="' . htmlspecialchars($value) . '" />';
        }
        $result .= '</form><script>document.getElementById("opsubmitform").submit();</script>';

        return $result;
    }

    public function computeSign($sharedSecret)
    {
    }
}
