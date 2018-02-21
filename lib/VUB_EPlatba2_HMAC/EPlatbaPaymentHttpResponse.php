<?php

namespace EPayment\VUB_EPlatba2_HMAC;

use EPayment\EPaymentException;
use EPayment\EPaymentHmacSignedMessage;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;

class EPlatbaPaymentHttpResponse extends EPaymentHmacSignedMessage implements IEPaymentHttpPaymentResponse
{
    protected $isVerified = false;

    /**
     * EPlatbaPaymentHttpResponse constructor.
     * @param null $fields
     */
    public function __construct($fields = null)
    {
        $this->readOnlyFields = array('SS', 'VS', 'RES', 'SIGN');

        if ($fields == null) {
            $fields = $_GET;
        }

        if (isset($fields['SS'])) {
            $this->fields['SS'] = $fields['SS'];
        }
        $this->fields['VS'] = isset($fields['VS']) ? $fields['VS'] : null;
        $this->fields['RES'] = isset($fields['RES']) ? $fields['RES'] : null;
        $this->fields['SIGN'] = isset($fields['SIGN']) ? $fields['SIGN'] : null;

    }

    /**
     * @param $password
     * @throws EPaymentException
     */
    public function verifySignature($password)
    {
        if ($this->SIGN != $this->computeSign($password)) {
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

        if ($this->RES == "OK") {
            return IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS;
        }

        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
    }

    /**
     * @throws EPaymentException
     */
    protected function validateData()
    {
        if (empty($this->VS)) {
            throw new EPaymentException('VS is empty');
        }
        if (!($this->RES == "FAIL" || $this->RES == "OK")) {
            throw new EPaymentException('Unknown result code');
        }
    }

    protected function getSignatureBase()
    {
        return "{$this->VS}{$this->SS}{$this->RES}";
    }
}