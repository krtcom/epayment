<?php

namespace EPayment\GP_webpay;

use EPayment\EPaymentException;
use EPayment\EPaymentMessage;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;

class GPwebpayPaymentResponse extends EPaymentMessage implements IEPaymentHttpPaymentResponse
{

    protected $isVerified = false;
    protected $publicKeyFile;

    public function __construct($fields = null)
    {

        $this->readOnlyFields = array(
            'OPERATION',
            'ORDERNUMBER',
            'MERORDERNUM',
            'MD',
            'PRCODE',
            'SRCODE',
            'RESULTTEXT',
            'USERPARAM1',
            'ADDINFO',
            'TOKEN',
            'EXPIRY',
            'ACSRES',
            'ACCODE',
            'PANPATTERN',
            'DAYTOCAPTURE',
            'TOKENREGSTATUS',
            'ACRC',
            'RRN',
            'PAR',
            'TRACEID',
            'DIGEST',
            'DIGEST1'
        );

        if ($fields == null) {
            $fields = $_GET;
        }

        foreach ($this->readOnlyFields as $key) {
            if (isset($fields[$key])) {
                $this->fields[$key] = $fields[$key];
            }
        }
    }

    public function verifySignature($merchantNumber = null)
    {
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
    private function verifySignKey($merchantNumber)
    {
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

    protected function getSignatureBase()
    {
        $signFields = array_filter($this->fields, function ($item) {
            return $item != null;
        });
        unset($signFields["DIGEST"]);
        unset($signFields["DIGEST1"]);
        return implode("|", $signFields);
    }

    protected function getSignatureBase1($merchantNumber)
    {
        $signFields = array_filter($this->fields, function ($item) {
            return $item != null;
        });
        unset($signFields["DIGEST"]);
        unset($signFields["DIGEST1"]);
        $signFields[] = $merchantNumber;
        return implode("|", $signFields);
    }

    public function getPaymentResponse()
    {
        if (!$this->isVerified) {
            throw new EPaymentException(__METHOD__ . ": Message was not verified yet.");
        }

        if ($this->PRCODE == 0 && $this->SRCODE == 0) {
            return IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS;
        }

        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
    }

    public function computeSign($sharedSecret)
    {
    }

    public function getPublicKeyFile()
    {
        return $this->publicKeyFile;
    }

    public function setPublicKeyFile($publicKeyFile)
    {
        $this->publicKeyFile = $publicKeyFile;
    }

    protected function validateData()
    {
        return true;
    }
}