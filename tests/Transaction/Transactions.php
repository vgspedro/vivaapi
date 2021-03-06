<?php 
namespace VgsPedro\Viva\Transaction;

/**
 * A class which creates get transactions request
 */
class Transactions extends Request {

	/** @const string Uri to required api */
	const URI = "/nativecheckout/v2/transactions";

	/** @const string Request method */
	const METHOD = "GET";

	/** @var string Card number */
	private $transactionId;

	/**
	 * Sets card number
	 *
	 * @param string $transactionId Card Number
	 *
	 * @return Transaction\Transaction
	 */
	public function setTransactionId($transactionId) {

		if (!is_string($transactionId)) {

			return false;
		}

		$this->transactionId = (string) $transactionId;

		return $this;
	}

	/**
	 * Gets Transaction Id
	 *
	 * @return string
	 */
	public function getTransactionId() {

		return $this->transactionId;
	}

	/**
	 * Sends request to api
	 *
	 * @return stdClass
	 */
	public function send() {

		$this->setHeaders(["transactionId" => $this->getTransactionId()]);

		return parent::send();
	}
}