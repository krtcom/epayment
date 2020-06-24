<?php

namespace EPayment\GP_webpay;

use EPayment\EPaymentException;
use EPayment\EPaymentMessage;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;

class GPwebpayPaymentResponse extends EPaymentMessage implements IEPaymentHttpPaymentResponse {

    protected $isVerified = false;
    protected $publicKeyFile;

    public function __construct($fields = null) {

        $this->readOnlyFields = array('OPERATION', 'ORDERNUMBER', 'MERORDERNUM', 'MD', 'PRCODE', 'SRCODE', 'RESULTTEXT', 'USERPARAM1', 'ADDINFO', 'DIGEST', 'DIGEST1');

        if ($fields == null) {
            $fields = $_GET;
        }

        $this->fields['OPERATION'] = isset($fields['OPERATION']) ? $fields['OPERATION'] : null;
        $this->fields['ORDERNUMBER'] = isset($fields['ORDERNUMBER']) ? $fields['ORDERNUMBER'] : null;
        $this->fields['MERORDERNUM'] = isset($fields['MERORDERNUM']) ? $fields['MERORDERNUM'] : null;
        $this->fields['MD'] = isset($fields['MD']) ? $fields['MD'] : null;
        $this->fields['PRCODE'] = isset($fields['PRCODE']) ? $fields['PRCODE'] : null;
        $this->fields['SRCODE'] = isset($fields['SRCODE']) ? $fields['SRCODE'] : null;
        $this->fields['RESULTTEXT'] = isset($fields['RESULTTEXT']) ? $fields['RESULTTEXT'] : null;
        $this->fields['USERPARAM1'] = isset($fields['USERPARAM1']) ? $fields['USERPARAM1'] : null;
        $this->fields['ADDINFO'] = isset($fields['ADDINFO']) ? $fields['ADDINFO'] : null;
        $this->fields['DIGEST'] = isset($fields['DIGEST']) ? $fields['DIGEST'] : null;
        $this->fields['DIGEST1'] = isset($fields['DIGEST1']) ? $fields['DIGEST1'] : null;
    }

    public function verifySignature($merchantNumber = null) {
        if ($this->verifySignKey($merchantNumber)) {
            $this->isVerified = true;
            return true;
        }
        return false;
    }

    /**
     * @param $merchantNumber
     * @return bool
     * @throws EPaymentException
     */
    private function verifySignKey($merchantNumber) {
        if (!$this->isValid) {
            throw new EPaymentException(__METHOD__ . ": Message was not validated.");
        }

        $pubkeyid = openssl_get_publickey("file://" . $this->publicKeyFile);
        if ($pubkeyid === false) {
            throw new EPaymentException(__METHOD__ . ": Public key error.");
        }

        $result = openssl_verify($this->getSignatureBase(), base64_decode($this->DIGEST), $pubkeyid);
        $result2 = openssl_verify($this->getSignatureBase1($merchantNumber), base64_decode($this->DIGEST1), $pubkeyid);

        openssl_free_key($pubkeyid);

        return ($result == 1 && $result2 == 1);

    }

    protected function getSignatureBase() {
        $signFields = array_filter($this->fields, function ($item) {
            return $item != null;
        });
        unset($signFields["DIGEST"]);
        unset($signFields["DIGEST1"]);
        return implode("|", $signFields);
    }

    protected function getSignatureBase1($merchantNumber) {
        $signFields = array_filter($this->fields, function ($item) {
            return $item != null;
        });
        unset($signFields["DIGEST"]);
        unset($signFields["DIGEST1"]);
        $signFields[] = $merchantNumber;
        return implode("|", $signFields);
    }

    public function getPaymentResponse() {
        if (!$this->isVerified) {
            throw new EPaymentException(__METHOD__ . ": Message was not verified yet.");
        }

        if ($this->PRCODE == 0 && $this->SRCODE == 0) {
            return IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS;
        }

        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
    }

    public function computeSign($sharedSecret) {
    }

    public function getPublicKeyFile()
    {
        return $this->publicKeyFile;
    }

    public function setPublicKeyFile($publicKeyFile)
    {
        $this->publicKeyFile = $publicKeyFile;
    }

    protected function validateData() {
        return true;
    }
}