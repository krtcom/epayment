<?php

namespace EPayment\TB_TatraPay_v4_HMAC;

use EPayment\EPaymentException;
use EPayment\EPaymentHmacSignedMessage;
use EPayment\Interfaces\IEPaymentHttpRedirectPaymentRequest;
use Exception;

class TatraPayPaymentRequest extends EPaymentHmacSignedMessage implements IEPaymentHttpRedirectPaymentRequest
{

    const URL_BASE = "https://moja.tatrabanka.sk/cgi-bin/e-commerce/start/tatrapay";

    const VALID_LANGUAGES = ["sk", "en", "de", "hu", "cz", "es", "fr", "it", "pl"];

    const VALID_CURRENCIES = ['978', '203', '840', '826', '348', '985', '756', '208'];

    private $redirectUrlBase = self::URL_BASE;

    public function __construct()
    {
        $this->readOnlyFields = array('HMAC');
        $this->requiredFields = array('MID', 'AMT', 'CURR', 'REF', 'RURL', 'TIMESTAMP');
        $this->optionalFields = array('REM', 'AREDIR', 'LANG');
    }

    public function signMessage($password)
    {
        $this->fields['HMAC'] = $this->computeSign($password);
    }

    public function computeSign($password)
    {
        $keyBytes = pack("H*", $password);
        return hash_hmac("sha256", $this->getSignatureBase(), $keyBytes);
    }

    protected function getSignatureBase()
    {
        return "{$this->MID}{$this->AMT}{$this->CURR}{$this->REF}{$this->RURL}{$this->REM}{$this->TIMESTAMP}";
    }

    public function setRedirectUrlBase($url)
    {
        $this->redirectUrlBase = $url;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrlBase . "?" . http_build_query($this->fields);
    }

    /**
     * @throws EPaymentException
     */
    protected function validateData()
    {
        if (!is_string($this->AMT)) {
            $this->AMT = sprintf("%01.2F", $this->AMT);
        }

        try {
            if (!preg_match('/^[0-9a-z]{3,4}$/', $this->MID)) {
                throw new Exception('Merchant ID is in wrong format');
            }

            if (!preg_match('/^[0-9]+(\\.[0-9]+)?$/', $this->AMT)) {
                throw new Exception('Amount is in wrong format');
            }

            if (empty($this->RURL)) {
                throw new Exception('Return URL is in wrong format');
            }

            if (!in_array($this->CURR, self::VALID_CURRENCIES)) {
                throw new Exception('Unknown currency, known languages are: ' . implode(',', self::VALID_CURRENCIES));
            }

            if (!empty($this->REM)) {
                if (filter_var($this->REM, FILTER_VALIDATE_EMAIL) === false) {
                    throw new Exception('Return e-mail address in wrong format');
                }
            }

            if (!empty($this->LANG)) {
                if (!in_array($this->LANG, self::VALID_LANGUAGES)) {
                    throw new Exception('Unknown language, known languages are: ' . implode(',', self::VALID_LANGUAGES));
                }
            }

        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }
}