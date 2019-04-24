<?php

namespace EPayment;


class EPaymentLog {

    public static function log($text){
        if (defined("EPAYMENT_LOG")) {
            try {
                file_put_contents(EPAYMENT_LOG, "[". date("Y-m-d H:i:s") ."] " . $text . "\n", FILE_APPEND);
            } catch (\Exception $e) {}
        }
    }
}