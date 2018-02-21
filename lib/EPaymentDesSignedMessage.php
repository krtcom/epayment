<?php

namespace EPayment;

use Exception;

abstract class EPaymentDesSignedMessage extends EPaymentMessage
{
    public function computeSign($sharedSecret)
    {
        if (!$this->isValid) {
            throw new EPaymentException(__METHOD__ . ": Message was not validated.");
        }

        try {
            $signatureBase = $this->GetSignatureBase();
            $bytesHash = sha1($signatureBase, true);

            // uprava pre PHP < 5.0
            if (strlen($bytesHash) != 20) {
                $bytes = "";
                for ($i = 0; $i < strlen($bytesHash); $i += 2) {
                    $bytes .= chr(hexdec(substr($signatureBase, $i, 2)));
                }
                $bytesHash = $bytes;
            }

            $des = mcrypt_module_open(MCRYPT_DES, "", MCRYPT_MODE_ECB, "");

            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($des), MCRYPT_RAND);
            mcrypt_generic_init($des, $sharedSecret, $iv);

            $bytesSign = mcrypt_generic($des, substr($bytesHash, 0, 8));

            mcrypt_generic_deinit($des);
            mcrypt_module_close($des);

            return strtoupper(bin2hex($bytesSign));
        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }
}