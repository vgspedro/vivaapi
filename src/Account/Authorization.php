<?php namespace ATDev\Viva\Account;

/**
 * Authorization class
 */
class Authorization {

	use \ATDev\Viva\Request;

	/** @const string Uri to required api */
	const URI = "/connect/token";

	/** @const string Request method */
	const METHOD = "POST";

	/**
	 * Gets access token
	 *
	 * @return null|string
	 */
	public function getAccessToken() {

		$headers = [
			"Authorization" => "Basic " . $this->getAuthToken(),
			"Accept" => "application/json",
			"Content-Type" => "application/x-www-form-urlencoded",
		];

		$request = [
			"form_params" => ["grant_type" => "client_credentials"],
			"timeout" => 60,
			"connect_timeout" => 60,
			"exceptions" => false,
			'headers' => $headers
		];

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

			if ((isset($result->error)) && (!empty(trim($result->error))) ) {

				$this->setError($result->error);
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

		if (empty($result->access_token)) {

			$this->setError("Access token is absent in response");
		}

		if (!empty($this->getError())) {

			return null;
		}

		return $result->access_token;
	}

	/**
	 * Creates the token to obtain access token
	 *
	 * @return string type
	 */
	private function getAuthToken() {

		return base64_encode($this->getClientId() . ":" . $this->getClientSecret());
	}

	/**
	 * Gets full api url for the request
	 *
	 * @return string
	 */
	private function getApiUrl() {

		return Url::getUrl($this->getTestMode()) . static::URI;
	}
}