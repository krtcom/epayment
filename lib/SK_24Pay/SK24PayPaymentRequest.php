<?php

namespace EPayment\SK_24Pay;

use EPayment\EPaymentAesSignedMessage;
use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPostPaymentRequest;
use Exception;

class SK24PayPaymentRequest extends EPaymentAesSignedMessage implements IEPaymentHttpPostPaymentRequest
{
    const URL_BASE = "https://admin.24-pay.eu/pay_gate/paygt";

    private $redirectUrlBase = self::URL_BASE;

    public function __construct()
    {

        $this->requiredFields = array('Mid', 'EshopId', 'Amount', 'CurrAlphaCode', 'ClientId', 'MsTxnId', 'FirstName', 'FamilyName', 'Email', 'Country', 'Timestamp');
        $this->optionalFields = array('LangCode', 'RURL', 'NURL', 'NotifyEmail', 'RedirectSign', 'PreAuthProvided', 'Phone', 'Street', 'City', 'Zip');
        $this->readOnlyFields = array('Sign');

    }

    public function signMessage($password)
    {
        $this->fields['Sign'] = $this->computeSign($password);
    }

    public function setRedirectUrlBase($url)
    {
        $this->redirectUrlBase = $url;
    }

    public function setUrlBase($url)
    {
        $this->redirectUrlBase = $url;
    }

    public function getPaymentRequestFields()
    {
        $result = [];

        foreach ($this->requiredFields as $requiredField) {
            $result[$requiredField] = $this->fields[$requiredField];
        }

        foreach ($this->optionalFields as $optionalField) {
            if (!empty($this->fields[$optionalField])) {
                $result[$optionalField] = $this->fields[$optionalField];
            }
        }

        foreach ($this->readOnlyFields as $readOnlyField) {
            $result[$readOnlyField] = $this->fields[$readOnlyField];
        }

        return $result;
    }

    public function getUrlBase()
    {
        return $this->redirectUrlBase;
    }

    public function getIv()
    {
        return $this->Mid . strrev($this->Mid);
    }

    /**
     * @throws EPaymentException
     */
    protected function validateData()
    {
        if (!is_string($this->Amount)) {
            $this->Amount = sprintf("%01.2F", $this->Amount);
        }

        try {

            if (!preg_match('/^[0-9a-zA-Z]{8}$/', $this->Mid)) {
                throw new Exception('Merchant ID is in wrong format');
            }

            if (!preg_match('/^[0-9]{1,10}$/', $this->EshopId)) {
                throw new Exception('EshopId is in wrong format');
            }

            if (!preg_match('/^[0-9]+(\\.[0-9]+)?$/', $this->Amount)) {
                throw new Exception('Amount is in wrong format');
            }

            if (!preg_match('/^[A-Z]{3}$/', $this->CurrAlphaCode)) {
                throw new Exception('Currency code is in wrong format');
            }

            if (!preg_match('/^[0-9a-zA-Z]{3,10}$/', $this->ClientId)) {
                throw new Exception('ClientId is in wrong format');
            }

            if (strlen($this->MsTxnId) > 10) {
                throw new Exception('Variable Symbol is in wrong format');
            }

            if (!preg_match('/^[0-9]+$/', $this->MsTxnId)) {
                throw new Exception('Variable Symbol is in wrong format');
            }

            if (!preg_match('/^[a-zA-Z]{2,50}$/', $this->FirstName)) {
                throw new Exception('FirstName is in wrong format');
            }

            if (!preg_match('/^[a-zA-Z]{2,50}$/', $this->FamilyName)) {
                throw new Exception('FamilyName is in wrong format');
            }

            if (filter_var($this->Email, FILTER_VALIDATE_EMAIL) === false) {
                throw new Exception('Email is in wrong format');
            }

            if (!preg_match('/^[A-Z]{3}$/', $this->Country)) {
                throw new Exception('Country code is in wrong format');
            }

        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }

    protected function getSignatureBase()
    {
        return $this->Mid . $this->Amount . $this->CurrAlphaCode . $this->MsTxnId . $this->FirstName . $this->FamilyName . $this->Timestamp;
    }
}