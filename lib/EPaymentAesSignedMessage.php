<?php

namespace EPayment;

use Exception;

abstract class EPaymentAesSignedMessage extends EPaymentMessage
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

            $hash = hash("sha1", $this->getSignatureBase(), true);
            $iv = $this->getIv();

            $key = pack('H*', $sharedSecret);

            if (PHP_VERSION_ID >= 50303 && extension_loaded('openssl')) {
                $crypted = openssl_encrypt($hash, 'AES-256-CBC', $key, 1, $iv);
            } else {
                $crypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $hash, MCRYPT_MODE_CBC, $iv);
            }

            return strtoupper(bin2hex(substr($crypted, 0, 16)));

        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }

    abstract public function getIv();
}