<?php

namespace EPayment\VUB_eCard;

use EPayment\EPaymentException;
use EPayment\Interfaces\IEPaymentHttpPostPaymentRequest;
use EPayments\PaymentObject;
use Exception;

/**
 * @property string clientid ID obchodníka pridelené bankou
 * @property string storetype Platobný model obchodníka
 * @property string trantype Typ transakcie
 * @property double amount Suma transakcie
 * @property int currency Kód ISO pre menu transakcie
 * @property string oid Jedinečný identifikátor príkazu, číslo objednávky, orderID
 * @property string okUrl Návratová adresa URL, na ktorú systém Nestpay presmeruje zákazníka, ak sa transakcia úspešne dokončí.
 * @property string failUrl Návratová adresa URL, na ktorú systém Nestpay presmeruje zákazníka, ak sa transakcia nedokončí úspešne.
 * @property string lang Jazyk platobnej brány Nestpay
 * @property string rnd Náhodný reťazec, použije sa na porovnanie hodnoty hash
 * @property string hash Hodnota hash na overenie klienta
 * @property string hashAlgorithm Verzia hash
 * @property string encoding Kódovanie platobnej brány.
 *
 * Nepovinne parametre
 * @property string description Popis transakcie do MPI. Popis platby (zobrazuje sa v Merchant centre v poli „description“)
 * @property string comments Popis transakcie
 * @property string INVOICENUMBER Číslo objednávky pre výpis
 * @property string email Emailová adresa zákazníka
 * @property string tel Telefón zákazníka
 */
class VUBeCardPaymentRequest extends VUB_eCardSignedMessage implements IEPaymentHttpPostPaymentRequest
{
    const URL_BASE = 'https://vub.eway2pay.com/fim/est3dgate';
    const URL_BASE_TEST = 'https://testsecurepay.eway2pay.com/fim/est3dgate';
    const VALID_LANGUAGES = ['sk', 'en'];

    private $urlBase = self::URL_BASE;

    protected $requiredFields = ['clientid', 'storetype', 'trantype', 'amount', 'currency', 'oid', 'okUrl', 'failUrl', 'lang', 'rnd', 'hashAlgorithm', 'encoding'];
    protected $optionalFields = [
        'description', 'comments', 'INVOICENUMBER', 'tel', 'email',
        'BillToCompany', 'BillToName', 'BillToStreet1', 'BillToStreet2', 'BillToCity', 'BillToState', 'BillToPostalCode', 'BillToCountry',
        'ShipToCompany', 'ShipToName', 'ShipToStreet1', 'ShipToStreet2', 'ShipToCity', 'ShipToStateProv', 'ShipToPostalCode', 'ShipToCountry'
    ];

    protected $fields = [
        'storetype' => '3d_pay_hosting',
        'trantype' => 'Auth',
        'currency' => 978,
        'lang' => self::VALID_LANGUAGES[0],
        'hashAlgorithm' => 'Ver2',
        'encoding' => 'utf-8'
    ];

    public function __construct()
    {

    }

    /**
     * @throws EPaymentException
     */
    public function signMessage($sharedSecret)
    {
        $this->fields['hash'] = $this->computeSign($sharedSecret);
    }

    public function setRedirectUrlBase($url)
    {
        $this->urlBase = $url;
    }

    /**
     * @throws EPaymentException
     */
    public function validateData()
    {
        if (!is_string($this->amount)) {
            $this->amount = sprintf("%01.2F", $this->amount);
        }

        try {
            if (empty($this->clientid)) {
                throw new Exception('Merchant ID is empty');
            }
            if (strlen($this->clientid) > 15) {
                throw new Exception('Merchant ID is in wrong format');
            }

            if (strlen($this->amount) > 13) {
                throw new Exception('Amount is in wrong format');
            }

            if (!in_array($this->lang, self::VALID_LANGUAGES)) {
                throw new Exception('Unknown language, valid languages are: ' . implode(',', self::VALID_LANGUAGES));
            }

            if (empty($this->oid)) {
                throw new Exception('Order ID is empty');
            }

            if (empty($this->okUrl) || empty($this->failUrl)) {
                throw new Exception('Return URL is in wrong format');
            }

            if (!empty($this->description)) {
                if (strlen($this->description) > 255) {
                    throw new Exception('Description field is too long');
                }
            }

            if (!empty($this->comments)) {
                if (strlen($this->comments) > 255) {
                    throw new Exception('Comments field is too long');
                }
            }

            if (!empty($this->email)) {
                if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Incorrect e-mail address');
                }
            }

        } catch (Exception $e) {
            throw new EPaymentException($e->getMessage());
        }
    }

    public function getPaymentRequestFields()
    {
        $res = array(
            'clientid' => $this->clientid,
            'storetype' => $this->storetype,
            'trantype' => $this->trantype,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'oid' => $this->oid,
            'okUrl' => $this->okUrl,
            'failUrl' => $this->failUrl,
            'lang' => $this->lang,
            'rnd' => $this->rnd,
            'hash' => $this->hash,
            'hashAlgorithm' => $this->hashAlgorithm,
            'encoding' => $this->encoding,
        );

        foreach ($this->optionalFields as $optionalField) {
            if (!empty($this->$optionalField)) {
                $res[$optionalField] = $this->$optionalField;
            }
        }

        return $res;
    }

    public function getUrlBase()
    {
        return $this->urlBase;
    }

    public function setUrlBase($url)
    {
        $this->urlBase = $url;
    }

    public function setOptionalFields(PaymentObject $paymentObject)
    {
        foreach ($this->optionalFields as $field) {
            if (!empty($paymentObject->$field)) {
                $this->$field = $paymentObject->$field;
            }
        }
    }

    protected function getSignatureBase()
    {
        return implode("|", [
            $this->clientid,
            $this->escape($this->oid),
            $this->amount,
            $this->escape($this->okUrl),
            $this->escape($this->failUrl),
            $this->trantype,
            '',
            $this->rnd,
            '',
            '',
            '',
            $this->currency
        ]);
    }


}
