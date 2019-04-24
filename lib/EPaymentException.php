<?php

namespace EPayment;

use Exception;
use Throwable;

class EPaymentException extends Exception {

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        EPaymentLog::log("EPaymentException (code ". $code ."):\n" . $message);
    }
}