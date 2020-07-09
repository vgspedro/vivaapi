<?php
namespace VgsPedro\VivaApi\Transaction;

/**
 * A class which creates get installments request
 */
class Installments extends Request {

	/** @const string Uri to required api */
	const URI = "/nativecheckout/v2/installments";

	/** @const string Request method */
	const METHOD = "GET";

	/** @var string Card number */
	private $number;

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
	 * Sends request to api
	 *
	 * @return stdClass
	 */
	public function send() {

		$this->setHeaders(["cardNumber" => $this->getNumber()]);
		$this->setExpectedResult(["maxInstallments" => "Max installments"]);

		return parent::send();
	}
}