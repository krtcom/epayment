<?php

namespace EPayment\VUB_EPlatba2_HMAC;

use EPayment\EPaymentException;
use EPayment\EPaymentHmacSignedMessage;
use EPayment\Interfaces\IEPaymentHttpPostPaymentRequest;
use EvInOrder;
use Exception;

class EPlatbaPaymentRequest extends EPaymentHmacSignedMessage implements IEPaymentHttpPostPaymentRequest
{
    const URL_BASE = "https://ib.vub.sk/e-platbyeuro.aspx";
    private $urlBase = self::URL_BASE;

    public function __construct()
    {
        $this->requiredFields = array('MID', 'AMT', 'VS', 'CS', 'RURL');
        $this->optionalFields = array('SS', 'DESC', 'REM', 'RSMS');
    }

    public function signMessage($password)
    {
        $this->fields['SIGN'] = $this->computeSign($password);
    }

    public function setRedirectUrlBase($url)
    {
        $this->urlBase = $url;
    }

    /**
     * @throws EPaymentException
     */
    public function validateData()
    {
        if (!is_string($this->AMT)) {
            $this->AMT = sprintf("%01.2F", $this->AMT);
        }

        try {
            if (empty($this->MID)) {
                throw new Exception('Merchant ID is empty');
            }
            if (strlen($this->MID) > 20) {
                throw new Exception('Merchant ID is in wrong format');
            }

            if (strlen((string)($this->AMT)) > 13) {
                throw new Exception('Amount is in wrong format');
            }
            if (strpos(',', (string)($this->AMT)) !== false) {
                throw new Exception('Amount is in wrong format');
            }

            if (empty($this->VS)) {
                throw new Exception('Variable symbol is empty');
            }
            if (strlen($this->VS) > 10) {
                throw new Exception('Variable symbol is in wrong format');
            }
            if (!preg_match('/^[0-9]+$/', $this->VS)) {
                throw new Exception('Variable symbol is in wrong format');
            }

            if (strlen($this->CS) > 10) {
                throw new Exception('Constant symbol is in wrong format');
            }
            if (!preg_match('/^[0-9]+$/', $this->CS)) {
                throw new Exception('Constant symbol is in wrong format');
            }

            if (empty($this->RURL)) {
                throw new Exception('Return URL is in wrong format');
            }

            if (!empty($this->SS)) {
                if (strlen($this->SS) > 10) {
                    throw new Exception('Specific symbol is in wrong format');
                }
                if (!preg_match('/^[0-9]+$/', $this->SS)) {
                    throw new Exception('Specific symbol is in wrong format');
                }
            }

            if (!empty($this->DESC)) {
                if (strlen($this->DESC) > 35) {
                    throw new Exception('Description is too long');
                }
            }
        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }

    public function getPaymentRequestFields()
    {
        $res = array(
            'MID' => $this->MID,
            'AMT' => $this->AMT,
            'VS' => $this->VS,
            'CS' => $this->CS,
            'RURL' => $this->RURL,
            'SIGN' => $this->SIGN
        );
        if (!empty($this->SS)) {
            $res['SS'] = $this->SS;
        }
        if (!empty($this->DESC)) {
            $res['DESC'] = $this->DESC;
        }
        if (!empty($this->REM)) {
            $res['REM'] = $this->REM;
        }
        if (!empty($this->RSMS)) {
            $res['RSMS'] = $this->RSMS;
        }
        return $res;
    }

    public function getUrlBase()
    {
        return $this->urlBase;
    }

    public function setUrlBase($url)
    {
        $this->urlBase = $url;
    }

    protected function getSignatureBase()
    {
        return $this->MID . $this->AMT . $this->VS . $this->SS . $this->CS . $this->RURL;
    }
}
