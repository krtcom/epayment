<?php

namespace EPayments;


abstract class Payment
{
    protected $amount;
    protected $variableSymbol;
    protected $specificSymbol;
    protected $returnUrl;
    protected $rem;
    protected $name;
    protected $language;

    /**
     * Payment constructor.
     * @param null $amount
     * @param null $variableSymbol
     * @param null $returnUrl
     * @param null $name
     */
    public function __construct($amount, $variableSymbol, $returnUrl = null, $name = null, $language = null)
    {
        $this->amount = $amount;
        $this->variableSymbol = $variableSymbol;
        $this->returnUrl = $returnUrl;
        $this->name = $name;
        $this->language = $language;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function setVariableSymbol($variableSymbol)
    {
        $this->variableSymbol = $variableSymbol;
    }

    public function setSpecificSymbol($specificSymbol)
    {
        $this->specificSymbol = $specificSymbol;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function setRem($rem)
    {
        $this->rem = $rem;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string | array
     */
    abstract function request();

    /**
     * @return int
     */
    abstract function response();

}