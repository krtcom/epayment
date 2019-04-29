<?php

namespace EPayment\Ecomm;


class EcommMerchant
{
    /**
     * Variable $url
     * @access private
     * @var string
     */
    private $url;

    /**
     * Variable $keystore
     * @access private
     * @var string
     */
    private $keystore;

    /**
     * Variable $keystorepassword
     * @access private
     * @var string
     */
    private $keystorepassword;

    /**
     * Variable $verbose
     * @access private
     * @var boolean
     */
    private $verbose = false;

    function __construct($url, $keystore, $keystore_password, $verbose = 0)
    {
        $this->url = $url;
        $this->keystore = $keystore;
        $this->keystorepassword = $keystore_password;
        $this->verbose = $verbose;
    }

    /**
     * Registering of DMS authorisation
     * @param int $amount transaction amount in minor units, mandatory
     * @param int $currency transaction currency code, mandatory
     * @param string $ip client’s IP address, mandatory
     * @param string $desc description of transaction, optional
     * @param string $language authorization language identificator, optional
     * @return \stdClass TRANSACTION_ID
     */

    function startDMSAuth($amount, $currency, $ip, $desc, $language)
    {

        $params = array(
            'command' => 'a',
            'msg_type' => 'DMS',
            'amount' => $amount,
            'currency' => $currency,
            'client_ip_addr' => $ip,
            'description' => $desc,

        );
        return $this->sentPost($params);
    }

    /**
     * Send parameters
     *
     * @param array post parameters
     * @return \stdClass $resultObject
     */
    function sentPost($params)
    {
        $resultObject = new \stdClass();

        if (!file_exists($this->keystore)) {
            error_log($resultObject->error);
            $resultObject->error = "file " . $this->keystore . " not exists";
            return $resultObject;
        }

        $post = "";

        foreach ($params as $key => $value) {
            $post .= "$key=$value&";
        }


        $curl = curl_init();
        if ($this->verbose) {
            curl_setopt($curl, CURLOPT_VERBOSE, true);
        }
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->keystore);
        curl_setopt($curl, CURLOPT_CAINFO, $this->keystore);
        curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $this->keystorepassword);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);

        if (curl_error($curl)) {
            $result = curl_error($curl);
            error_log($result);
        }
        curl_close($curl);

        $resultObject = new \stdClass();
        $responseRows = preg_split("/\r\n|\n|\r/", $result);
        foreach ($responseRows as $responseRow) {
            list($key, $value) = array_map('trim', explode(":", $responseRow));
            if (!empty($key)) {
                $resultObject->$key = $value;
            }
        }

        return $resultObject;
    }

    /**
     * Making of DMS transaction
     * @param int $auth_id id of previously made successeful authorisation
     * @param int $amount transaction amount in minor units, mandatory
     * @param int $currency transaction currency code, mandatory
     * @param string $ip client’s IP address, mandatory
     * @param string $desc description of transaction, optional
     * @return \stdClass RESULT, RESULT_CODE, RRN, APPROVAL_CODE
     */

    function makeDMSTrans($auth_id, $amount, $currency, $ip, $desc, $language)
    {

        $params = array(
            'command' => 't',
            'msg_type' => 'DMS',
            'trans_id' => $auth_id,
            'amount' => $amount,
            'currency' => $currency,
            'client_ip_addr' => $ip,
            'description' => $desc,
            'language' => $language,
        );

        $str = $this->sentPost($params);
        return $str;
    }

    /**
     * Transaction result
     * @param int $trans_id transaction identifier, mandatory
     * @param string $ip client’s IP address, mandatory
     * @return \stdClass RESULT, RESULT_CODE, 3DSECURE, AAV, RRN, APPROVAL_CODE
     */

    function getTransResult($trans_id, $ip)
    {

        $params = array(
            'command' => 'c',
            'trans_id' => $trans_id,
            'client_ip_addr' => $ip
        );

        $str = $this->sentPost($params);
        return $str;
    }

    /**
     * Transaction reversal
     * @param int $trans_id transaction identifier, mandatory
     * @param int $amount transaction amount in minor units, mandatory
     * @return \stdClass RESULT, RESULT_CODE
     */

    function reverse($trans_id, $amount)
    {

        $params = array(
            'command' => 'r',
            'trans_id' => $trans_id,
            'amount' => $amount
        );

        $str = $this->sentPost($params);
        return $str;
    }

    /**
     * Closing of business day
     * @return \stdClass RESULT, RESULT_CODE, FLD_075, FLD_076, FLD_087, FLD_088
     */

    function closeDay()
    {

        $params = array(
            'command' => 'b',
        );

        $str = $this->sentPost($params);
        return $str;

    }

    /**
     * Registering of SMS transaction
     * @param int $amount transaction amount in minor units, mandatory
     * @param int $currency transaction currency code, mandatory
     * @param string $ip client’s IP address, mandatory
     * @param string $desc description of transaction, optional
     * @param string $language authorization language identificator, optional
     * @return \stdClass TRANSACTION_ID
     */
    function startSMSTrans($amount, $currency, $ip, $desc, $language, $account)
    {

        $params = array(
            'command' => 'v',
            'amount' => $amount,
            'currency' => $currency,
            'client_ip_addr' => $ip,
            'description' => $desc,
            'language' => $language,
            'account' => $account
        );
        return $this->sentPost($params);
    }
}