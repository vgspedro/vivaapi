<?php namespace ATDev\Viva\Transaction;

/**
 * A class which creates get transactions request
 */
class Transactions extends Request {

	/** @const string Uri to required api */
	const URI = "/nativecheckout/v2/transactions";

	/** @const string Request method */
	const METHOD = "GET";

	/** @var string Transaction Id */
	private $transactionId;

	/**
	 * Sets card nu
	 *
	 * @param string $transactionId Transaction Id
	 *
	 * @return \ATDev\Viva\Transaction\Transactions
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
//		$this->setExpectedResult(["maxInstallments" => "Max installments"]);

		return parent::send();
	}
}