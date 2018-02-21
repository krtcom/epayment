<?php

namespace EPayment\SLSP_SporoPay;

use EPayment\EPayment3DesSignedMessage;
use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;

class SporoPayPaymentHttpResponse extends EPayment3DesSignedMessage implements IEPaymentHttpPaymentResponse
{

    protected $isVerified = false;

    /**
     * SporoPayPaymentHttpResponse constructor.
     * @param null $fields
     */
    public function __construct($fields = null)
    {

        $this->readOnlyFields = ['u_predcislo', 'u_cislo', 'u_kbanky', 'pu_predcislo', 'pu_cislo', 'pu_kbanky', 'suma', 'mena', 'vs', 'ss', 'url', 'param', 'result', 'real', 'SIGN2'];

        if ($fields == null) {
            $fields = $_GET;
        }

        $this->fields['u_predcislo'] = isset($fields['u_predcislo']) ? $fields['u_predcislo'] : null;
        $this->fields['u_cislo'] = isset($fields['u_cislo']) ? $fields['u_cislo'] : null;
        $this->fields['u_kbanky'] = isset($fields['u_kbanky']) ? $fields['u_kbanky'] : null;
        $this->fields['pu_predcislo'] = isset($fields['pu_predcislo']) ? $fields['pu_predcislo'] : null;
        $this->fields['pu_cislo'] = isset($fields['pu_cislo']) ? $fields['pu_cislo'] : null;
        $this->fields['pu_kbanky'] = isset($fields['pu_kbanky']) ? $fields['pu_kbanky'] : null;
        $this->fields['suma'] = isset($fields['suma']) ? $fields['suma'] : null;
        $this->fields['mena'] = isset($fields['mena']) ? $fields['mena'] : null;
        $this->fields['vs'] = isset($fields['vs']) ? $fields['vs'] : null;
        $this->fields['ss'] = isset($fields['ss']) ? $fields['ss'] : null;
        $this->fields['url'] = isset($fields['url']) ? $fields['url'] : null;
        $this->fields['param'] = isset($fields['param']) ? $fields['param'] : null;
        $this->fields['result'] = isset($fields['result']) ? $fields['result'] : null;
        $this->fields['real'] = isset($fields['real']) ? $fields['real'] : null;
        $this->fields['SIGN2'] = isset($fields['SIGN2']) ? $fields['SIGN2'] : null;
    }

    /**
     * @param $password
     * @throws EPaymentException
     */
    public function verifySignature($password)
    {
        if ($this->SIGN2 != $this->computeSign($password)) {
            throw new EPaymentException('Response signature is invalid');
        }

        $this->isVerified = true;
    }

    public function getPaymentResponse()
    {
        if (!$this->isVerified) {
            throw new EPaymentException(__METHOD__ . ": Message was not verified yet.");
        }

        if ($this->result == 'OK' && $this->real == 'OK') {
            return IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS;
        }
        if ($this->result == 'OK' && $this->real != 'OK') {
            return IEPaymentHttpPaymentResponse::RESPONSE_TIMEOUT;
        }

        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;

    }

    /**
     * @throws EPaymentException
     */
    protected function validateData()
    {
        if (!preg_match('/^[0-9]*$/', $this->u_predcislo)) {
            throw new EPaymentException('u_predcislo is in wrong format');
        }
        if (!preg_match('/^[0-9]+$/', $this->u_cislo)) {
            throw new EPaymentException('u_cislo is in wrong format');
        }
        if (!preg_match('/^[0-9]+$/', $this->u_kbanky)) {
            throw new EPaymentException('u_kbanky is in wrong format');
        }

        if (!preg_match('/^[0-9]*$/', $this->pu_predcislo)) {
            throw new EPaymentException('pu_predcislo is in wrong format');
        }
        if (!preg_match('/^[0-9]+$/', $this->pu_cislo)) {
            throw new EPaymentException('pu_cislo is in wrong format');
        }
        if ($this->pu_kbanky != '0900') {
            throw new EPaymentException('pu_kbanky is in wrong format');
        }

        if (!preg_match('/^([0-9]+|[0-9]*\\.[0-9]{0,2})$/', $this->suma)) {
            throw new EPaymentException('suma is in wrong format');
        }
        if ($this->mena != 'EUR') {
            throw new EPaymentException('mena is in wrong format');
        }
        if (!preg_match('/^[0-9]{10}$/', $this->vs)) {
            throw new EPaymentException('vs is in wrong format');
        }
        if (!preg_match('/^[0-9]{10}$/', $this->ss)) {
            throw new EPaymentException('ss is in wrong format');
        }
        if (preg_match('/[\\;\\?\\&]/', $this->url)) {
            throw new EPaymentException('url is in wrong format');
        }

        $results = array('OK', 'NOK');
        if (!in_array($this->result, $results)) {
            throw new EPaymentException('result code is in wrong');
        }
        if (!in_array($this->real, $results)) {
            throw new EPaymentException('result code is in wrong');
        }
    }

    protected function getSignatureBase()
    {
        return "{$this->u_predcislo};{$this->u_cislo};{$this->u_kbanky};{$this->pu_predcislo};{$this->pu_cislo};{$this->pu_kbanky};{$this->suma};{$this->mena};{$this->vs};{$this->ss};{$this->url};{$this->param};{$this->result};{$this->real}";
    }
}