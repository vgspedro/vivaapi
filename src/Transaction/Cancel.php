<?php 
namespace VgsPedro\Viva\Transaction;

/**
 * A class which creates cancel request
 */
class Cancel extends Request {

	/** @const string Request method */
	const METHOD = "DELETE";

	/** @var string Transaction id to cancel */
	protected $transactionId;

	/**
	 * Sets transaction id to cancel
	 *
	 * @param string $transactionId Transaction id to cancel
	 *
	 * @return \ATDev\Viva\Transaction\Cancel
	 */
	public function setTransactionId($transactionId) {

		if (!is_string($transactionId)) {

			return false;
		}

		$this->transactionId = $transactionId;

		return $this;
	}

	/**
	 * Gets transaction id to cancel
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

		$get = [];

		if (!empty($this->getAmount())) {
			$get[] = "amount=" . $this->getAmount();
		}

		if (!empty($this->getSourceCode())) {
			$get[] = "sourceCode=" . $this->getSourceCode();
		}

		if (!empty($get)) {
			$url = $url . "?" . implode("&", $get);
		}

		return $url;
	}
}