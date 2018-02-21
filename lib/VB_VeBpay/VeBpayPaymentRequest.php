<?php

namespace EPaymentment\VB_VeBpay;

use EPayment\EPaymentDesSignedMessage;
use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpRedirectPaymentRequest;
use Exception;

class VeBpayPaymentRequest extends EPaymentDesSignedMessage implements IEPaymentHttpRedirectPaymentRequest
{
    const URL_BASE = "https://ibs.luba.sk/vebpay/";
    private $redirectUrlBase = self::URL_BASE;

    public function __construct()
    {
        $this->readOnlyFields = array('SIGN');
        $this->requiredFields = array('MID', 'AMT', 'VS', 'CS', 'RURL');
        $this->optionalFields = array('DESC');
    }

    public function signMessage($password)
    {
        $this->fields['SIGN'] = $this->computeSign($password);
    }

    public function setRedirectUrlBase($url)
    {
        $this->redirectUrlBase = $url;
    }

    public function getRedirectUrl()
    {
        $url = $this->redirectUrlBase;

        $url .= "?MID={$this->MID}";
        $url .= "&AMT={$this->AMT}";
        $url .= "&VS={$this->VS}";
        $url .= "&CS={$this->CS}";
        $url .= "&RURL=" . urlencode($this->RURL);

        if (!empty($this->DESC)) {
            $url .= "&DESC=" . urlencode($this->DESC);
        }

        $url .= "&SIGN={$this->SIGN}";

        return $url;
    }

    protected function getSignatureBase()
    {
        $sb = "{$this->MID}{$this->AMT}{$this->VS}{$this->CS}{$this->RURL}";
        return $sb;
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
            if (empty($this->MID)) {
                throw new Exception('Merchant ID is empty');
            }
            if (!preg_match('/^[0-9]+(\\.[0-9]+)?$/', $this->AMT)) {
                throw new Exception('Amount is in wrong format');
            }
            if (strlen($this->VS) > 10) {
                throw new Exception('Variable Symbol is in wrong format');
            }
            if (!preg_match('/^[0-9]+$/', $this->VS)) {
                throw new Exception('Variable Symbol is in wrong format');
            }
            if (strlen($this->CS) > 10) {
                throw new Exception('Constant Symbol is in wrong format');
            }
            if (!preg_match('/^[0-9]+$/', $this->CS)) {
                throw new Exception('Constant Symbol is in wrong format');
            }
            if (empty($this->RURL)) {
                throw new Exception('Return URL is in wrong format');
            }

            $urlRestrictedChars = array('&', '?', ';', '=', '+', '%');
            foreach ($urlRestrictedChars as $char) {
                if (false !== strpos($this->RURL, $char)) {
                    throw new Exception('Return URL contains restricted character: "' . $char . '"');
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
}