<?php

namespace EPayment;

abstract class EPaymentMessage
{
    protected $fields         = array();
    protected $readOnlyFields = array();
    protected $requiredFields = array();
    protected $optionalFields = array();
    protected $isValid        = false;

    public function __get($name)
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->readOnlyFields)) {
            throw new EPaymentException("Trying to change a read only field '$name'.");
        }

        if (!in_array($name, $this->requiredFields) && !in_array($name, $this->optionalFields)) {
            throw new EPaymentException("Trying to set unknown field '$name'.");
        }

        $this->fields[$name] = $value;

        $this->isValid = false;
    }

    public function __isset($key)
    {
        if (isset($this->fields[$key])) {
            return (false === empty($this->fields[$key]));
        } else {
            return null;
        }
    }

    /**
     * @throws EPaymentException
     */
    public abstract function computeSign($sharedSecret);

    /**
     * @throws EPaymentException
     */
    public function validate()
    {

        foreach ($this->requiredFields as $requiredField) {
            if (!isset($this->fields[$requiredField])) {
                throw new EPaymentException("Required field " . $requiredField . " is missing.");
            }
        }

        $this->validateData();

        $this->isValid = true;

    }

    /**
     * @throws EPaymentException
     */
    protected abstract function validateData();

    protected abstract function getSignatureBase();
}