<?php

namespace EPayment;

use Exception;

abstract class EPaymentHmacSignedMessage extends EPaymentMessage
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
            $signatureBase = $this->getSignatureBase();
            $rawSignatureBase = pack('A*', $signatureBase);
            $rawSharedSecret = $this->getRawSharedSecret($sharedSecret);
            return hash_hmac('sha256', $rawSignatureBase, $rawSharedSecret, false);
        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }

    /**
     * @param $sharedSecret
     * @return string
     * @throws EPaymentException
     */
    protected function getRawSharedSecret($sharedSecret)
    {
        if (strlen($sharedSecret) == 64) {
            return pack('A*', $sharedSecret);
        } elseif (strlen($sharedSecret) == 128) {
            return pack('A*', pack('H*', $sharedSecret));
        } else {
            throw new EPaymentException(__METHOD__ . ": Invalid shared secret format.");
        }
    }
}