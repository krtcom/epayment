<?php

namespace EPayment;

use Exception;

abstract class EPayment3DesSignedMessage extends EPaymentMessage {
    public function computeSign($sharedSecret) {
        $signature = null;
        if (!$this->isValid) {
            throw new EPaymentException(__METHOD__ . ": Message was not validated.");
        }

        try {
            $bytesHash = sha1($this->GetSignatureBase(), true);
            while (strlen($bytesHash) < 24)
                $bytesHash .= chr(0xFF);

            $ssBytes = base64_decode($sharedSecret);
            $key = $ssBytes . substr($ssBytes, 0, 8);

            $iv = chr(0x00);
            $iv .= $iv; // 2
            $iv .= $iv; // 4
            $iv .= $iv; // 8

            $signatureBytes = mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $bytesHash, MCRYPT_MODE_CBC, $iv);
            return base64_encode($signatureBytes);
        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }
}