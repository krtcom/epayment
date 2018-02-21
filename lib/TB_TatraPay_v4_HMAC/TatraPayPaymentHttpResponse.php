<?php

namespace EPayment\TB_TatraPay_v4_HMAC;

use EPayment\EPaymentException;
use EPayment\EPaymentHmacSignedMessage;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;
use Exception;

class TatraPayPaymentHttpResponse extends EPaymentHmacSignedMessage implements IEPaymentHttpPaymentResponse
{
    protected $isVerified = false;

    const ECDSA_KEY_LIST_URL = "http://moja.tatrabanka.sk/e-commerce/ecdsa_keys.txt";

    /**
     * TatraPayPaymentHttpResponse constructor.
     * @param null $fields
     */
    public function __construct($fields = null)
    {
        $this->readOnlyFields = ["AMT", "CURR", "REF", "RES", "TID", "TIMESTAMP", "HMAC", "ECDSA_KEY", "ECDSA"];

        if ($fields == null) {
            $fields = $_GET;
        }

        foreach ($this->readOnlyFields as $key) {
            if (isset($fields[$key])) {
                $this->fields[$key] = $fields[$key];
            }
        }
    }

    /**
     * @param $password
     * @throws EPaymentException
     */
    public function verifySignature($password)
    {

        if ($this->HMAC != $this->computeSign($password)) {
            throw new EPaymentException('Response signature is invalid');
        }

        try {
            $publicKey = $this->get_ECDSA_KEY($this->ECDSA_KEY);
            $stringToVerify = $this->getECDSABase();
            $verified = openssl_verify($stringToVerify, pack("H*", $this->ECDSA), $publicKey, "sha256");

            if ($verified !== 1) {
                throw new EPaymentException('Response ECDSA signature is invalid');
            }
        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }

        $this->isVerified = true;
    }

    private function get_ECDSA_KEY($ID)
    {

        if (defined('EPAYMENT_DEBUG') && EPAYMENT_DEBUG) {
            return "-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEozvFM1FJP4igUQ6kP8ofnY7ydIWksMDk1IKXyr/T
RDoX4sTMmmdiIrpmCZD4CLDtP0j2LfD7saSIc8kZUwfILg==
-----END PUBLIC KEY-----";
        }

        $keyData = file_get_contents(self::ECDSA_KEY_LIST_URL);

        $regex = "/KEY_ID: $ID\RSTATUS: VALID\R(-----BEGIN PUBLIC KEY-----.+-----END PUBLIC KEY-----)/sU";
        if (preg_match($regex, $keyData, $match)) {
            return $match[1];
        }

        throw new Exception('Requested ECDSA key not found');

    }

    protected function getECDSABase()
    {
        return "{$this->AMT}{$this->CURR}{$this->REF}{$this->RES}{$this->TID}{$this->TIMESTAMP}{$this->HMAC}";
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

        if ($this->RES == "TOUT") {
            return IEPaymentHttpPaymentResponse::RESPONSE_UNKNOWN;
        }

        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
    }

    /**
     * @throws EPaymentException
     */
    protected function validateData()
    {
        if (empty($this->REF)) {
            throw new EPaymentException('REF is undefined');
        }

        if (!($this->RES == "FAIL" || $this->RES == "OK" || $this->RES == "TOUT")) {
            throw new EPaymentException('Result code is missing from response');
        }
    }

    protected function getSignatureBase()
    {
        return "{$this->AMT}{$this->CURR}{$this->REF}{$this->RES}{$this->TID}{$this->TIMESTAMP}";
    }
}