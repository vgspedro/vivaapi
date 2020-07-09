<?php namespace ATDev\Viva\Tests\Account;

use \PHPUnit\Framework\TestCase;
use \AspectMock\Test as test;

use \ATDev\Viva\Account\Authorization;
use \ATDev\Viva\Tests\Fixture;

class AuthorizationTest extends TestCase {

	public function testClientId() {

		$auth = new Authorization();

		$result = $auth->setClientId(123);
		$this->assertFalse($result);

		$result = $auth->setClientId("asd");
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("asd", $result->getClientId());

		return $result;
	}

	/**
	 * @depends testClientId
	 */
	public function testClientSecret($auth) {

		$result = $auth->setClientSecret(123);
		$this->assertFalse($result);

		$result = $auth->setClientSecret("zxc");
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("zxc", $result->getClientSecret());

		return $result;
	}

	/**
	 * @depends testClientSecret
	 */
	public function testTestMode($auth) {

		$result = $auth->setTestMode(123);
		$this->assertFalse($result);

		$result = $auth->setTestMode(true);
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertTrue($result->getTestMode());

		return $result;
	}

	/**
	 * @depends testTestMode
	 */
	public function testGetAccessToken($auth) {

		// test successful executions with production env
		$url = test::double("\ATDev\Viva\Account\Url", ["getUrl" => "some-url"]);

		$response = new Fixture();
		$response->setStatusCode(200);
		$response->setContents('{"access_token":"this_is_access_token"}');

		$client = test::double("\GuzzleHttp\Client", ["request" => $response]);

		$accessToken = $auth->getAccessToken();

		$this->assertNull($auth->getError()); // No error
		$this->assertSame("this_is_access_token", $accessToken); // Access token returned

		// Check the url is taken for production environment
		$url->verifyInvokedOnce("getUrl", [true]);
		$url->verifyNeverInvoked("getUrl", [false]);

		// Check request paramenters
		$client->verifyInvokedOnce("request", [
			"POST",
			"some-url/connect/token",
			[
				"form_params" => ["grant_type" => "client_credentials"],
				"timeout" => 60,
				"connect_timeout" => 60,
				"exceptions" => false,
				'headers' => [
					"Authorization" => "Basic " . base64_encode("asd" . ":" . "zxc"),
					"Accept" => "application/json",
					"Content-Type" => "application/x-www-form-urlencoded"
				]
			]
		]);

		// test error status code with demo env but no error
		$auth->setTestMode(false);

		$response->setStatusCode(400);
		$response->setContents('{"some_text":"text"}');

		$accessToken = $auth->getAccessToken();

		$this->assertSame('{"some_text":"text"}', $auth->getError());
		$this->assertNull($accessToken);

		$url->verifyInvokedOnce("getUrl", [false]);

		// test error status code with error
		$response->setStatusCode(100);
		$response->setContents('{"error":"this is error text"}');

		$accessToken = $auth->getAccessToken();

		$this->assertSame("this is error text", $auth->getError());
		$this->assertNull($accessToken);

		// test error status code with empty response
		$response->setStatusCode(310);
		$response->setContents('');

		$accessToken = $auth->getAccessToken();

		$this->assertSame("An unknown error occured", $auth->getError());
		$this->assertNull($accessToken);

		// test success status code but no access token
		$response->setStatusCode(200);
		$response->setContents('{"no_access_token":"it_has_to_be_here"}');

		$accessToken = $auth->getAccessToken();

		$this->assertSame("Access token is absent in response", $auth->getError());
		$this->assertNull($accessToken);

		// test success status code but unparsable response
		$response->setStatusCode(200);
		$response->setContents('{"ololol"ere"}');

		$accessToken = $auth->getAccessToken();

		$this->assertSame("Access token is absent in response", $auth->getError());
		$this->assertNull($accessToken);
	}

	protected function tearDown(): void {

		test::clean(); // remove all registered test doubles
	}
}