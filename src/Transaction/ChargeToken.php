<?php namespace ATDev\Viva\Transaction;

/**
 * A class which creates charge token request
 */
class ChargeToken extends Request {

	/** @const string Uri to required api */
	const URI = "/nativecheckout/v2/chargetokens";

	/** @const string Request method */
	const METHOD = "POST";

	/** @var string Cvc code */
	private $cvc;

	/** @var string Card number */
	private $number;

	/** @var string Card holder name */
	private $holderName;

	/** @var string Card expiration year */
	private $expirationYear;

	/** @var string Card expiration month */
	private $expirationMonth;

	/** @var string Session redirect url */
	private $sessionRedirectUrl;

	/**
	 * Sets cvc code
	 *
	 * @param string $cvc Cvc code
	 *
	 * @return \ATDev\Viva\Transaction\ChargeToken
	 */
	public function setCvc($cvc) {

		if (!is_string($cvc) && !is_int($cvc)) {

			return false;
		}

		$this->cvc = (string) $cvc;

		return $this;
	}

	/**
	 * Gets cvc code
	 *
	 * @return string
	 */
	public function getCvc() {

		return $this->cvc;
	}

	/**
	 * Sets card number
	 *
	 * @param string $number Card Number
	 *
	 * @return \ATDev\Viva\Transaction\ChargeToken
	 */
	public function setNumber($number) {

		if (!is_string($number) && !is_int($number)) {

			return false;
		}

		$this->number = (string) $number;

		return $this;
	}

	/**
	 * Gets card number
	 *
	 * @return string
	 */
	public function getNumber() {

		return $this->number;
	}

	/**
	 * Sets card holder name
	 *
	 * @param string $holderName Card holder name
	 *
	 * @return \ATDev\Viva\Transaction\ChargeToken
	 */
	public function setHolderName($holderName) {

		if (!is_string($holderName)) {

			return false;
		}

		$this->holderName = $holderName;

		return $this;
	}

	/**
	 * Gets card holder name
	 *
	 * @return string
	 */
	public function getHolderName() {

		return $this->holderName;
	}

	/**
	 * Sets card expiration year
	 *
	 * @param string $expirationYear Card expiration year
	 *
	 * @return \ATDev\Viva\Transaction\ChargeToken
	 */
	public function setExpirationYear($expirationYear) {

		if (!is_int($expirationYear)) {

			return false;
		}

		$this->expirationYear = $expirationYear;

		return $this;
	}

	/**
	 * Gets card expiration year
	 *
	 * @return int
	 */
	public function getExpirationYear() {

		return $this->expirationYear;
	}

	/**
	 * Sets card expiration month
	 *
	 * @param string $expirationMonth Card expiration month
	 *
	 * @return \ATDev\Viva\Transaction\ChargeToken
	 */
	public function setExpirationMonth($expirationMonth) {

		if (!is_int($expirationMonth)) {

			return false;
		}

		$this->expirationMonth = $expirationMonth;

		return $this;
	}

	/**
	 * Gets card expiration month
	 *
	 * @return string
	 */
	public function getExpirationMonth() {

		return $this->expirationMonth;
	}

	/**
	 * Sets session redirect url
	 *
	 * @param string $sessionRedirectUrl Session Redirect url
	 *
	 * @return \ATDev\Viva\Transaction\ChargeToken
	 */
	public function setSessionRedirectUrl($sessionRedirectUrl) {

		if (!is_string($sessionRedirectUrl)) {

			return false;
		}

		$this->sessionRedirectUrl = $sessionRedirectUrl;

		return $this;
	}

	/**
	 * Gets session redirect url
	 *
	 * @return string
	 */
	public function getSessionRedirectUrl() {

		return $this->sessionRedirectUrl;
	}

	/**
	 * Sends request to api
	 *
	 * @return stdClass
	 */
	public function send() {

		$this->setExpectedResult(["chargeToken" => "Charge Token", "redirectToACSForm" => "HTML to render"]);

		return parent::send();
	}

	/**
	 * Specifies what has to be returned on serialization to json
	 *
	 * @return array Data to serialize
	 */
	public function jsonSerialize() {

		$result = [
			"amount" => $this->getAmount(),
			"cvc" => $this->getCvc(),
			"number" => $this->getNumber(),
			"holderName" => $this->getHolderName(),
			"expirationYear" => $this->getExpirationYear(),
			"expirationMonth" => $this->getExpirationMonth(),
			"sessionRedirectUrl" => $this->getSessionRedirectUrl()
		];

		return $result;
	}
}