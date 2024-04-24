<?php

namespace EPayment\VUB_eCard;

use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPaymentResponse;

/**
 * @property string clientid
 * @property string oid
 *
 * @property string Response "Approved", “Declined” alebo “Error”
 * @property string AuthCode Autorizačný kód transakcie
 * @property string HostRefNum Referenčné číslo hostiteľa
 * @property string ProcReturnCode Kód stavu transakcie
 * @property string TransId Jedinečné ID transakcie
 * @property string ErrMsg Text chyby (ak má parameter Response hodnotu ”Declined” alebo “Error”)
 * @property string ClientIp Adresa IP zákazníka
 * @property string ReturnOid Vrátené ID príkazu, musí byť rovnaké ako vstupné oid.
 * @property string PaymentMethod Spôsob platby transakcie
 * @property string rnd Náhodný reťazec, použije sa na porovnanie hodnoty hash
 * @property string HASHPARAMS Obsahuje názvy polí použitých na výpočet hodnoty hash. Názvy polí budú pripojené znakom ':'
 * @property string HASHPARAMSVAL Obsahuje spojené hodnoty polí hash na výpočet hodnoty hash. Hodnoty polí sú spojené v rovnakom poradí ako v poli HASHPARAMS
 * @property string HASH Hodnota hash poľa HASHPARAMSVAL a poľa s heslom obchodníka
 *
 * Parametre odpovede MPI
 * @property string mdStatus Kód stavu transakcie 3D
 * @property string txstatus Stav 3D na archiváciu
 * @property string eci Indikátor elektronického obchodu
 * @property string cavv Hodnota overenia držiteľa karty, určená ACS.
 * @property string mdErrorMsg Chybové hlásenie z MPI (ak nejaké je)
 * @property string xid Jedinečné ID internetovej transakcie
 */
class VUBeCardPaymentResponse extends VUB_eCardSignedMessage implements IEPaymentHttpPaymentResponse
{
    protected $isVerified = false;

    /**
     * EPlatbaPaymentHttpResponse constructor.
     * @param null $fields
     */
    public function __construct($fields = null)
    {

        $this->readOnlyFields = [
            'clientid', 'oid',
            'Response', 'AuthCode', 'HostRefNum', 'ProcReturnCode', 'TransId', 'ErrMsg', 'ClientIp', 'ReturnOid', 'PaymentMethod', 'rnd', 'HASHPARAMS', 'HASHPARAMSVAL', 'HASH',
            'mdStatus', 'txstatus', 'eci', 'cavv', 'md', 'mdErrorMsg', 'xid'
        ];

        if ($fields == null) {
            $fields = $_POST;
        }

        foreach ($fields as $key => $value) {
            if (in_array($key, $this->readOnlyFields)) {
                $this->fields[$key] = $value;
            }
        }

    }

    /**
     * @param $password
     * @throws EPaymentException
     */
    public function verifySignature($password)
    {
        if ($this->HASH != $this->computeSign($password)) {
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

        if ($this->Response == "Approved") {
            return IEPaymentHttpPaymentResponse::RESPONSE_SUCCESS;
        }

        return IEPaymentHttpPaymentResponse::RESPONSE_FAIL;
    }

    /**
     * @throws EPaymentException
     */
    protected function validateData()
    {
        if (empty($this->clientid)) {
            throw new EPaymentException('clientid is empty');
        }
        if (empty($this->oid)) {
            throw new EPaymentException('oid is empty');
        }
        if (empty($this->Response)) {
            throw new EPaymentException('Response is empty');
        }

        if (!in_array($this->Response, ['Approved', 'Error', 'Declined'])) {
            throw new EPaymentException('Unknown result code');
        }
    }

    protected function getSignatureBase()
    {
        $hashParamValues = [];
        $parsedHashParams = explode("|", $this->HASHPARAMS);
        foreach ($parsedHashParams as $parsedHashParam) {
            $hashParamValues[] = $this->escape($this->fields[$parsedHashParam] ?: '');
        }

        return implode("|", $hashParamValues);
    }
}