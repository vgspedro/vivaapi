<?php namespace ATDev\Viva\Transaction;

use \ATDev\Viva\Account\Authorization as AccountAuthorization;

/**
 * An abstract class which handles all requests to transactions api
 */
abstract class Request implements \JsonSerializable {

	use \ATDev\Viva\Request;

	/** @const string Uri to required api */
	const URI = "/nativecheckout/v2/transactions";

	/** @const string Request method, should be overridden in child classes */
	const METHOD = "";

	/** @var string Source code, provided by wallet */
	private $sourceCode;

	/** @var int The amount to auth, charge, capture, refund */
	private $amount;

	/** @var string Access token to interact with transactions api */
	private $accessToken;

	/** @var string Additional headers to be sent */
	private $headers = [];

	/** @var string Expected result to be returned by api */
	private $expectedResult = ["transactionId" => "Transaction id"];


	/**
	 * Set additional headers to be sent
	 *
	 * @param array $headers
	 *
	 * @return \ATDev\Viva\Transaction\Request
	 */
	public function setHeaders($headers) {

		if (!is_array($headers)) {

			return false;
		}

		$this->headers = $headers;

		return $this;
	}

	/**
	 * Gets headers
	 *
	 * @return array
	 */
	public function getHeaders() {

		return $this->headers;
	}

	/**
	 * Set expected result
	 *
	 * @param array $expectedResult
	 *
	 * @return \ATDev\Viva\Transaction\Request
	 */
	public function setExpectedResult($expectedResult) {

		if (!is_array($expectedResult)) {

			return false;
		}

		$this->expectedResult = $expectedResult;

		return $this;
	}

	/**
	 * Gets expected result
	 *
	 * @return array
	 */
	public function getExpectedResult() {

		return $this->expectedResult;
	}

	/**
	 * Set source code
	 *
	 * @param string $sourceCode
	 *
	 * @return \ATDev\Viva\Transaction\Request
	 */
	public function setSourceCode($sourceCode) {

		if (!is_string($sourceCode) && !is_int($sourceCode)) {

			return false;
		}

		$this->sourceCode = (string) $sourceCode; // Just in case if int is passed

		return $this;
	}

	/**
	 * Gets source code
	 *
	 * @return string
	 */
	public function getSourceCode() {

		return $this->sourceCode;
	}

	/**
	 * Sets amount
	 *
	 * @param int $amount Amount
	 *
	 * @return \ATDev\Viva\Transaction\Request
	 */
	public function setAmount($amount) {

		if (!is_int($amount)) {

			return false;
		}

		$this->amount = $amount;

		return $this;
	}

	/**
	 * Gets amount
	 *
	 * @return int
	 */
	public function getAmount() {

		return $this->amount;
	}

	/**
	 * Sets access token
	 *
	 * @param int $accessToken Access Token
	 *
	 * @return \ATDev\Viva\Transaction\Request
	 */
	public function setAccessToken($accessToken) {

		if (!is_string($accessToken)) {

			return false;
		}

		$this->accessToken = $accessToken;

		return $this;
	}

	/**
	 * Gets access token
	 *
	 * @return string
	 */
	public function getAccessToken() {

		if (empty($this->accessToken)) {

			$auth = (new AccountAuthorization())
				->setClientId($this->getClientId())
				->setClientSecret($this->getClientSecret())
				->setTestMode($this->getTestMode());

			$accessToken = $auth->getAccessToken();
			$error = $auth->getError();

			if (empty($error)) {

				$this->setAccessToken($accessToken);
			} else {

				$this->setError($error);
			}
		}

		return $this->accessToken;
	}

	/**
	 * Sends request to api
	 *
	 * @return stdClass
	 */
	public function send() {

		// Check if access token available
		if (empty($this->getAccessToken())) {
			if (!empty($this->getError())) {

				return null;
			}
		}

		$headers = [
			"Authorization" => "Bearer " . $this->getAccessToken(),
			"Accept" => "application/json"
		];

		if (!empty($this->getHeaders())) {
			$headers = array_merge($headers, $this->getHeaders());
		}

		$request = [
			"timeout" => 60,
			"connect_timeout" => 60,
			"exceptions" => false,
			'headers' => $headers
		];

		if (!in_array(static::METHOD, ["DELETE", "GET"])) {
			$request["json"] = $this;
		}

		$client = new \GuzzleHttp\Client();
		$res = $client->request(
			static::METHOD,
			$this->getApiUrl(),
			$request
		);

		$code = $res->getStatusCode();
		$body = $res->getBody()->getContents();

		$result = @json_decode($body);

		if (($code < 200) || ($code >= 300)) {

			$this->setError($body);

			if ((isset($result->message)) && (!empty(trim($result->message))) ) {

				$this->setError($result->message);
			}

			if (empty($this->getError())) {

				$this->setError("An unknown error occured");
			}
		} else {

			$this->setError(null);
		}

		if (!empty($this->getError())) {

			return null;
		}

		foreach ($this->getExpectedResult() as $key => $value) {

			if (!is_object($result) || !property_exists($result, $key)) {

				$this->setError($value . " is absent in response");
				break;
			}
		}

		if (!empty($this->getError())) {

			return null;
		}

		return $result;
	}

	/**
	 * Specifies what has to be returned on serialization to json
	 *
	 * @return array Data to serialize
	 */
	public function jsonSerialize() {

		return [];
	}

	/**
	 * Gets api url for the request
	 *
	 * @return string
	 */
	protected function getApiUrl() {

		return Url::getUrl($this->getTestMode()) . static::URI;
	}
}