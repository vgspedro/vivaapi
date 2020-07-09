<?php namespace ATDev\Viva;

/**
 * Basic functionality to set/get data to make a requests
 */
trait Request {

	/** @var string Client id, provided by wallet */
	private $clientId;

	/** @var string Client secret, provided by wallet */
	private $clientSecret;

	/** @var bool Test mode */
	private $testMode = false;

	/** @var string|null Error message, empty if no error, some text if any */
	private $error;

	/**
	 * Set client id
	 *
	 * @param string $clientId
	 *
	 * @return \ATDev\Viva\Request
	 */
	public function setClientId($clientId) {

		if (!is_string($clientId)) {

			return false;
		}

		$this->clientId = $clientId;

		return $this;
	}

	/**
	 * Gets client id
	 *
	 * @return string
	 */
	public function getClientId() {

		return $this->clientId;
	}

	/**
	 * Set client secret
	 *
	 * @param string $clientSecret
	 *
	 * @return \ATDev\Viva\Request
	 */
	public function setClientSecret($clientSecret) {

		if (!is_string($clientSecret)) {

			return false;
		}

		$this->clientSecret = $clientSecret;

		return $this;
	}

	/**
	 * Gets client secret
	 *
	 * @return string
	 */
	public function getClientSecret() {

		return $this->clientSecret;
	}

	/**
	 * Sets test mode
	 *
	 * @param bool $testMode
	 *
	 * @return \ATDev\Viva\Request
	 */
	public function setTestMode($testMode) {

		if (!is_bool($testMode)) {

			return false;
		}

		$this->testMode = $testMode;

		return $this;
	}

	/**
	 * Gets test mode
	 *
	 * @return bool
	 */
	public function getTestMode() {

		return $this->testMode;
	}

	/**
	 * Gets error
	 *
	 * @return string
	 */
	public function getError() {

		return $this->error;
	}

	/**
	 * Sets error
	 *
	 * @param string $error
	 *
	 * @return \ATDev\Viva\Request
	 */
	private function setError($error) {

		$this->error = $error;

		return $this;
	}
}