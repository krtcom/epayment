<?php

namespace EPayment\UCB_UniPlatba;

use EPayment\EPaymentDesSignedMessage;
use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpRedirectPaymentRequest;
use Exception;

class UniPlatbaPaymentRequest extends EPaymentDesSignedMessage implements IEPaymentHttpRedirectPaymentRequest
{

    const URL_BASE = "https://sk.unicreditbanking.net/disp?restart=true&link=login.tplogin.system_login";
    const VALID_LANGUAGES = ['SK', 'EN'];
    private $redirectUrlBase = self::URL_BASE;

    public function __construct()
    {
        $this->readOnlyFields = array('SIGN');
        $this->requiredFields = array('MID', 'LNG', 'AMT', 'VS', 'CS');
        $this->optionalFields = array('SS', 'DESC');
    }

    public function signMessage($password)
    {
        $this->fields['SIGN'] = $this->computeSign($password);
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

        $url = $this->redirectUrlBase;

        if (strpos($url, '?') !== false) {
            $url .= '&';
        } else {
            $url .= '?';
        }

        return $url . http_build_query($this->fields);

    }

    protected function getSignatureBase()
    {
        $sb = "{$this->MID}{$this->LNG}{$this->AMT}{$this->VS}{$this->CS}{$this->SS}{$this->DESC}";
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

            if (!preg_match('/^[0-9]{1,13}\\.[0-9]{2}$/', $this->AMT)) {
                throw new Exception('Amount must be a decimal number with 2 digits after period delimiter.');
            }

            if (!preg_match('/^[0-9]{1,13}(\\.[0-9]{1,2})?$/', $this->AMT)) {
                throw new Exception('Amount is in wrong format');
            }

            if (!preg_match('/^[0-9]{1,10}$/', $this->MID)) {
                throw new Exception('Merchant ID is in wrong format');
            }

            if (!in_array($this->LNG, self::VALID_LANGUAGES)) {
                throw new Exception('Unknown language, valid languages are: ' . implode(',', self::VALID_LANGUAGES));
            }

            if (strlen($this->VS) > 10) {
                throw new Exception('Variable Symbol is in wrong format');
            }

            if (!preg_match('/^[0-9]+$/', $this->VS)) {
                throw new Exception('Variable Symbol is in wrong format');
            }

            if (strlen($this->CS) != 4) {
                throw new Exception('Constant Symbol must be 4 digits long');
            }

            if (!preg_match('/^[0-9]+$/', $this->CS)) {
                throw new Exception('Constant Symbol is in wrong format');
            }

            // nepovinne
            if (!empty($this->SS)) {
                if (strlen($this->SS) > 10) {
                    throw new Exception('Specific Symbol is in wrong format');
                }
                if (!preg_match('/^[0-9]+$/', $this->SS)) {
                    throw new Exception('Specific Symbol is in wrong format');
                }
            }
            if (!empty($this->DESC)) {
                if (strlen($this->DESC) > 35) {
                    throw new Exception('Description is too long');
                }
                if (strpos($this->DESC, ' ') !== false) {
                    throw new Exception('Description contains whitespace characters');
                }
            }

        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }
}