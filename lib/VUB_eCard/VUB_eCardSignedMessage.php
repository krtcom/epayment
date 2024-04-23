<?php


namespace EPayment\VUB_eCard;

use EPayment\EPaymentException;
use EPayment\EPaymentMessage;
use Exception;

abstract class VUB_eCardSignedMessage extends EPaymentMessage
{

    /**
     * @param $sharedSecret
     * @return string
     * @throws EPaymentException
     */
    public function computeSign($sharedSecret)
    {
        if (!$this->isValid) {
            throw new EPaymentException(__METHOD__ . ": Message was not validated.");
        }

        try {
            $signatureBase = $this->getSignatureBase() . '|' . $sharedSecret;
            $hashValue = hash('sha512', $signatureBase);
            return base64_encode(pack('H*', $hashValue));

        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }

    protected function escape($str)
    {
        return str_replace("|", "\\|", str_replace("\\", "\\\\", $str));
    }
}