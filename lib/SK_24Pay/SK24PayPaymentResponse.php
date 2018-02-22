<?php

namespace EPayment\SK_24Pay;

use EPayment\EPaymentAesSignedMessage;
use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;

class SK24PayPaymentResponse extends EPaymentAesSignedMessage implements IEPaymentHttpPaymentResponse
{

    protected $isVerified = false;

    /**
     * EPlatbaPaymentHttpResponse constructor.
     * @param null $fields
     */
    public function __construct($fields = null)
    {
        $this->requiredFields = array('Mid', 'MsTxnId', 'Amount', 'CurrCode', 'Result');
        $this->readOnlyFields = array('MsTxnId', 'Amount', 'CurrCode', 'Result', 'Sign');

        if ($fields == null) {
            $fields = $_GET;
        }

        foreach ($this->readOnlyFields as $readOnlyField) {
            if (isset($fields[$readOnlyField])) {
                $this->fields[$readOnlyField] = $fields[$readOnlyField];
            }
        }

    }

    /**
     * @param $password
     * @throws EPaymentException
     */
    public function verifySignature($password)
    {
        if (!empty($this->Sign) && $this->Sign != $this->computeSign($password)) {
            throw new EPaymentException('Response signature is invalid');
        }

        $this->isVerified = true;
    }

    /**
     * @return int
     * @throws EPaymentException
     */
    public function getPaymentResponse()
    {
        if (!$this->isVerified) {
            throw new EPaymentException(__METHOD__ . ": Message was not verified yet.");
        }

        if ($this->Result == "OK") {
            return IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS;
        }

        if ($this->Result == "PENDING") {
            return IEPaymentHttpPaymentResponse::RESPONSE_PENDING;
        }

        if ($this->Result == "AUTHORIZED") {
            return IEPaymentHttpPaymentResponse::RESPONSE_AUTHORIZED;
        }

        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
    }

    /**
     * @throws EPaymentException
     */
    protected function validateData()
    {

        if (!in_array($this->Result, ["OK", "FAIL", "PENDING", "AUTHORIZED"])) {
            throw new EPaymentException('Unknown result code');
        }

    }

    protected function getSignatureBase()
    {
        return $this->MsTxnId . $this->Amount . $this->CurrCode . $this->Result;
    }

    public function getIv()
    {

        if (empty($this->Mid)) {
            throw new EPaymentException('Mid is empty . Cannot create IV');
        }

        return $this->Mid . strrev($this->Mid);
    }
}