<?php 
namespace ATDev\Viva\Transaction;

/**
 * An abstract class which handles requests to
 */
abstract class Transaction extends Request {

	/** @const If the transaction is pre-auth */
	//const PRE_AUTH = null;

	/** @const string Request method */
	const METHOD = "POST";

	/** @var \ATDev\Viva\Transaction\Customer Customer data */
	private $customer;

	/** @var string The token of the card to be charged */
	private $chargeToken;

	/** @var int Tip Amount */
	private $tipAmount = 0;

	/** @var int Maximum installments */
	private $installments = 0;

	/** @var string Merchant transaction reference */
	private $merchantTrns;

	/** @var string Description that the customer sees */
	private $customerTrns;

	/** @var boolean the transaction is pre-auth */
	private $preAuth;

	/** @var int currencyCode the currency of the transaction */
	private $currencyCode = 978; //Eur


	/**
	 * Sets customer
	 *
	 * @param \ATDev\Viva\Transaction\Customer $customer Customer
	 *
	 * @return \ATDev\Viva\Transaction\Transaction
	 */
	public function setCustomer($customer) {

		if (!($customer instanceof Customer)) {

			return false;
		}

		$this->customer = $customer;

		return $this;
	}

	/**
	 * Gets customer
	 *
	 * @return string
	 */
	public function getCustomer() {

		return $this->customer;
	}

	/**
	 * Sets charge token
	 *
	 * @param string $chargeToken Charge Token
	 *
	 * @return \ATDev\Viva\Transaction\Transaction
	 */
	public function setChargeToken($chargeToken) {

		if (!is_string($chargeToken)) {

			return false;
		}

		$this->chargeToken = $chargeToken;

		return $this;
	}

	/**
	 * Gets charge token
	 *
	 * @return string
	 */
	public function getChargeToken() {

		return $this->chargeToken;
	}


	/**
	 * Sets charge pre-auth
	 *
	 * @param boolean preAuth
	 *
	 * @return \ATDev\Viva\Transaction\Transaction
	 */
	public function setPreAuth($preAuth) {



		$this->preAuth = $preAuth;

		return $this;
	}

	/**
	 * Gets pre-auth
	 *
	 * @return boolean
	 */
	public function getPreAuth() {
		return $this->preAuth;
	}



	/**
	 * Sets charge tip
	 *
	 * @param string $chargeToken Charge Token
	 *
	 * @return \ATDev\Viva\Transaction\Transaction
	 */
	public function setTipAmount($tipAmount) {

		if (!is_int($tipAmount)) {

			return 0;
		}

		$this->tipAmount = $tipAmount;

		return $this;
	}

	/**
	 * Gets charge token
	 *
	 * @return string
	 */
	public function getTipAmount() {

		return $this->tipAmount;
	}

	/**
	 * Sets Currency Code
	 *
	 * @param int $currencyCode
	 *
	 * @return \ATDev\Viva\Transaction\Transaction
	 */
	public function setCurrencyCode($currencyCode) {

		if (!is_int($currencyCode)) {

			return 978; //Eur
		}

		$this->currencyCode = $currencyCode;

		return $this;
	}

	/**
	 * Gets currencyCode
	 *
	 * @return int
	 */
	public function getCurrencyCode() {

		return $this->currencyCode;
	}

	/**
	 * Sets maximum installments
	 *
	 * @param int $installments Maximum installments
	 *
	 * @return \ATDev\Viva\Transaction\Transaction
	 */
	public function setInstallments($installments) {

		if (!is_int($installments)) {

			return false;
		}

		$this->installments = $installments;

		return $this;
	}

	/**
	 * Gets maximum installments
	 *
	 * @return int
	 */
	public function getInstallments() {

		return $this->installments;
	}

	/**
	 * Sets merchant transaction reference
	 *
	 * @param string $merchantTrns Merchant transaction reference
	 *
	 * @return \ATDev\Viva\Transaction\Transaction
	 */
	public function setMerchantTrns($merchantTrns) {

		if (!is_string($merchantTrns)) {

			return false;
		}

		$this->merchantTrns = $merchantTrns;

		return $this;
	}

	/**
	 * Gets merchant transaction reference
	 *
	 * @return string
	 */
	public function getMerchantTrns() {

		return $this->merchantTrns;
	}

	/**
	 * Sets description that the customer sees
	 *
	 * @param string $customerTrns Description that the customer sees
	 *
	 * @return \ATDev\Viva\Transaction\Transaction
	 */
	public function setCustomerTrns($customerTrns) {

		if (!is_string($customerTrns)) {

			return false;
		}

		$this->customerTrns = $customerTrns;

		return $this;
	}

	/**
	 * Gets description that the customer sees
	 *
	 * @return string
	 */
	public function getCustomerTrns() {

		return $this->customerTrns;
	}

	/**
	 * Specifies what has to be returned on serialization to json
	 *
	 * @return array Data to serialize
	 */
	public function jsonSerialize() {

		$result = [
			"amount" => $this->getAmount(),
			"preauth" => $this->getPreAuth(), //static::PRE_AUTH,
			"sourceCode" => $this->getSourceCode(),
			"chargeToken" => $this->getChargeToken(),
			"currencyCode" => $this->getCurrencyCode()
		];

		if (!empty($this->getInstallments())) {
			$result['installments'] = $this->getInstallments();
		}

		if (!empty($this->getMerchantTrns())) {
			$result['merchantTrns'] = $this->getMerchantTrns();
		}

		if (!empty($this->getCustomerTrns())) {
			$result['customerTrns'] = $this->getCustomerTrns();
		}

		$customer = $this->getCustomer();
		if ((!empty($customer)) && (!$customer->isEmpty())) {
			$result['customer'] = $customer;
		}

		return $result;
	}
}