<?php

namespace EPayment\SLSP_SporoPay;

use EPayment\EPayment3DesSignedMessage;
use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpRedirectPaymentRequest;
use Exception;

class SporoPayPaymentRequest extends EPayment3DesSignedMessage implements IEPaymentHttpRedirectPaymentRequest
{
    const URL_BASE = "https://ib.slsp.sk/epayment/epayment/epayment.xml";
    private $redirectUrlBase = self::URL_BASE;

    public function __construct()
    {

        $this->requiredFields = array('pu_predcislo', 'pu_cislo', 'pu_kbanky', 'suma', 'mena', 'vs', 'ss', 'url', 'param');
        $this->optionalFields = array('acc_prefix', 'acc_number', 'mail_notify_att', 'email_adr', 'client_login', 'auth_tool_type');
        $this->readOnlyFields = array('pu_kbanky', 'mena');

        $this->fields['pu_kbanky'] = '0900';
        $this->fields['mena'] = 'EUR';

    }

    public function signMessage($password)
    {
        $this->fields['sign1'] = $this->computeSign($password);
    }

    public function setRedirectUrlBase($url)
    {
        $this->redirectUrlBase = $url;
    }

    public function getRedirectUrl()
    {
        $url = $this->redirectUrlBase . '?';
        $url .= "pu_predcislo={$this->pu_predcislo}";
        $url .= "&pu_cislo={$this->pu_cislo}";
        $url .= "&pu_kbanky={$this->pu_kbanky}";
        $url .= "&suma={$this->suma}";
        $url .= "&mena={$this->mena}";
        $url .= "&vs={$this->vs}";
        $url .= "&ss={$this->ss}";
        $url .= "&url=" . urlencode($this->url);
        $url .= "&param=" . urlencode($this->param);

        if (!empty($this->acc_prefix)) {
            $url .= "&acc_prefix={$this->acc_prefix}";
        }
        if (!empty($this->acc_number)) {
            $url .= "&acc_number={$this->acc_number}";
        }
        if (!empty($this->mail_notify_att)) {
            $url .= "&mail_notify_att={$this->mail_notify_att}";
        }
        if (!empty($this->email_adr)) {
            $url .= "&email_adr=" . urlencode($this->email_adr);
        }
        if (!empty($this->client_login)) {
            $url .= "&clien_login={$this->client_login}";
        }
        if (!empty($this->auth_tool_type)) {
            $url .= "&auth_tool_type={$this->auth_tool_type}";
        }

        $url .= "&sign1=" . urlencode($this->sign1);

        return $url;
    }

    /**
     * @throws EPaymentException
     */
    protected function validateData()
    {
        if (!is_string($this->suma)) {
            $this->suma = sprintf("%01.2F", $this->suma);
        }

        try {
            if (!preg_match('/^[0-9]*$/', $this->pu_predcislo)) {
                throw new Exception('pu_predcislo is in wrong format');
            }
            if (!preg_match('/^[0-9]+$/', $this->pu_cislo)) {
                throw new Exception('pu_cislo is in wrong format');
            }
            // kbanky - konstanta
            if (!preg_match('/^([0-9]+|[0-9]*\\.[0-9]{0,2})$/', $this->suma)) {
                throw new Exception('suma is in wrong format');
            }
            // mena - konstanta
            if (!preg_match('/^[0-9]{10}$/', $this->vs)) {
                throw new Exception('vs is in wrong format');
            }
            if (!preg_match('/^[0-9]{10}$/', $this->ss)) {
                throw new Exception('ss is in wrong format');
            }
            if (preg_match('/[\\;\\?\\&]/', $this->url)) {
                throw new Exception('url is in wrong format');
            }
            if (preg_match('/[\\;\\?\\&]/', $this->param)) {
                throw new Exception('param is in wrong format');
            }
        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }

    protected function getSignatureBase()
    {
        return "{$this->pu_predcislo};{$this->pu_cislo};{$this->pu_kbanky};{$this->suma};{$this->mena};{$this->vs};{$this->ss};{$this->url};{$this->param}";
    }
}
