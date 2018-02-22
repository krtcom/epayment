<?php

namespace EPayment\SK_24Pay;

use EPayment\EPaymentAesSignedMessage;
use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;
use Exception;

class SK24PayPaymentNotification extends EPaymentAesSignedMessage implements IEPaymentHttpPaymentResponse
{

    protected $isVerified = false;

    /**
     * SK24PayPaymentNotification constructor.
     * @param null $fields
     * @throws EPaymentException
     */
    public function __construct($fields = null)
    {

        $this->requiredFields = ['Mid', 'Amount', 'Currency', 'PspTxnId', 'MsTxnId', 'Timestamp', 'Result'];

        $this->readOnlyFields = ['MsTxnId', 'Amount', 'Currency', 'Email', 'Phone', 'Street', 'Zip', 'City', 'Country', 'FirstName', 'FamilyName', 'Timestamp', 'Result', 'Reason', 'ReasonCode', 'PSPCategory'];

        if ($fields == null) {
            $fields = $_GET;
        }

        try {
            $ResponseXML = simplexml_load_string($fields['params']);

            $this->fields = [
                'Sign'          => (string)$ResponseXML['sign'][0],
                'MsTxnId'       => (string)$ResponseXML->Transaction->Identification->MsTxnId[0],
                'PspTxnId'      => (string)$ResponseXML->Transaction->Identification->PspTxnId[0],

                'Amount'        => (string)$ResponseXML->Transaction->Presentation->Amount[0],
                'Currency'      => (string)$ResponseXML->Transaction->Presentation->Currency[0],

                'Email'         => (string)$ResponseXML->Transaction->Customer->Contact->Email[0],
                'Phone'         => (string)$ResponseXML->Transaction->Customer->Contact->Phone[0],
                'Street'        => (string)$ResponseXML->Transaction->Customer->Address->Street[0],
                'Zip'           => (string)$ResponseXML->Transaction->Customer->Address->Zip[0],
                'City'          => (string)$ResponseXML->Transaction->Customer->Address->City[0],
                'Country'       => (string)$ResponseXML->Transaction->Customer->Address->Country[0],
                'FirstName'     => (string)$ResponseXML->Transaction->Customer->Name->Given[0],
                'FamilyName'    => (string)$ResponseXML->Transaction->Customer->Name->Family[0],

                'Timestamp'     => (string)$ResponseXML->Transaction->Processing->Timestamp[0],
                'Result'        => (string)$ResponseXML->Transaction->Processing->Result[0],
                'Reason'        => (string)$ResponseXML->Transaction->Processing->Reason[0],
                'ReasonCode'    => (string)$ResponseXML->Transaction->Processing->Reason[0]['code'][0],
                'PSPCategory'   => (string)$ResponseXML->Transaction->Processing->PSPCategory[0]
            ];
        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }

    /**
     * @param $password
     * @throws EPaymentException
     */
    public function verifySignature($password)
    {
        if ($this->Sign != $this->computeSign($password)) {
            throw new EPaymentException('Response signature is invalid');
        }

        $this->isVerified = true;
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

        if ($this->Result == "OK") {
            return IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS;
        }

        if ($this->Result == "PENDING") {
            return IEPaymentHttpPaymentResponse::RESPONSE_PENDING;
        }

        if ($this->Result == "AUTHORIZED") {
            return IEPaymentHttpPaymentResponse::RESPONSE_AUTHORIZED;
        }

        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
    }

    public function getIv()
    {

        if (empty($this->Mid)) {
            throw new EPaymentException('Mid is empty . Cannot create IV');
        }

        return $this->Mid . strrev($this->Mid);
    }

    /**
     * @throws EPaymentException
     */
    protected function validateData()
    {
        if (!in_array($this->Result, ["OK", "FAIL", "PENDING", "AUTHORIZED"])) {
            throw new EPaymentException('Unknown result code');
        }

    }

    protected function getSignatureBase()
    {
        return $this->Mid . $this->Amount . $this->Currency . $this->PspTxnId . $this->MsTxnId . $this->Timestamp . $this->Result;
    }
}