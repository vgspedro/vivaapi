<?php 
namespace VgsPedro\VivaApi\Transaction;

/**
 * A class which creates capture request
 */
class Capture extends Request {

	/** @const string Request method */
	const METHOD = "POST";

	/** @var string Transaction id to capture */
	private $transactionId;

	/**
	 * Sets transaction id to capture
	 *
	 * @param string $transactionId Transaction id to capture
	 *
	 * @return \ATDev\Viva\Transaction\Capture
	 */
	public function setTransactionId($transactionId) {

		if (!is_string($transactionId)) {

			return false;
		}

		$this->transactionId = $transactionId;

		return $this;
	}

	/**
	 * Gets transaction id to capture
	 *
	 * @return string
	 */
	public function getTransactionId() {

		return $this->transactionId;
	}

	/**
	 * Gets full api url for the request
	 *
	 * @return string
	 */
	protected function getApiUrl() {

		$url = parent::getApiUrl();

		$url = $url . "/" . $this->getTransactionId();

		return $url;
	}

	/**
	 * Specifies what has to be returned on serialization to json
	 *
	 * @return array Data to serialize
	 */
	public function jsonSerialize() {

		$result = [
			"amount" => $this->getAmount()
		];

		return $result;
	}
}