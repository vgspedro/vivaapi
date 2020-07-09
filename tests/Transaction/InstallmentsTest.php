<?php namespace ATDev\Viva\Tests\Transaction;

use \PHPUnit\Framework\TestCase;
use \AspectMock\Test as test;

use \ATDev\Viva\Transaction\Installments;
use \ATDev\Viva\Tests\Fixture;

class InstallmentsTest extends TestCase {

	public function testClientId() {

		$installments = new Installments();

		$result = $installments->setClientId(123);
		$this->assertFalse($result);

		$result = $installments->setClientId("asd");
		$this->assertInstanceOf(Installments::class, $result);
		$this->assertSame("asd", $result->getClientId());

		return $result;
	}

	/**
	 * @depends testClientId
	 */
	public function testClientSecret($installments) {

		$result = $installments->setClientSecret(123);
		$this->assertFalse($result);

		$result = $installments->setClientSecret("zxc");
		$this->assertInstanceOf(Installments::class, $result);
		$this->assertSame("zxc", $result->getClientSecret());

		return $result;
	}

	/**
	 * @depends testClientSecret
	 */
	public function testTestMode($installments) {

		$result = $installments->setTestMode(123);
		$this->assertFalse($result);

		$result = $installments->setTestMode(true);
		$this->assertInstanceOf(Installments::class, $result);
		$this->assertTrue($result->getTestMode());

		return $result;
	}

	/**
	 * @depends testTestMode
	 */
	public function testNumber($installments) {

		// Is not int or string
		$result = $installments->setNumber(new \stdClass());
		$this->assertFalse($result);

		// String
		$result = $installments->setNumber("1234123412341234");
		$this->assertInstanceOf(Installments::class, $result);
		$this->assertSame("1234123412341234", $result->getNumber());

		// Int
		$result = $installments->setNumber(4321432143214321);
		$this->assertInstanceOf(Installments::class, $result);
		$this->assertSame("4321432143214321", $result->getNumber());

		return $result;
	}

	/**
	 * @depends testNumber
	 */
	public function testAccessToken($installments) {

		$result = $installments->setAccessToken(1230);
		$this->assertFalse($result);

		$result = $installments->setAccessToken("1230");
		$this->assertInstanceOf(Installments::class, $result);
		$this->assertSame("1230", $result->getAccessToken());

		return $result;
	}

	public function testGetAccessToken() {

		// Verify getting with error
		$installments = new Installments();
		$installments->setClientId("asd");
		$installments->setClientSecret("zxc");
		$installments->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => null, "getError" => "An error occured"]);

		$result = $installments->getAccessToken();

		$this->assertEmpty($result);
		$this->assertSame("An error occured", $installments->getError());

		$auth->verifyInvokedOnce("setClientId", ["asd"]);
		$auth->verifyInvokedOnce("setClientSecret", ["zxc"]);
		$auth->verifyInvokedOnce("setTestMode", [true]);
		$auth->verifyInvokedOnce("getAccessToken");
		$auth->verifyInvokedOnce("getError");

		// Verify success
		$installments2 = new Installments();
		$installments2->setClientId("asd");
		$installments2->setClientSecret("zxc");
		$installments2->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => "the_token", "getError" => null]);

		$result = $installments2->getAccessToken();

		$this->assertSame("the_token", $result);
		$this->assertEmpty($installments2->getError());
	}

	/**
	 * @depends testAccessToken
	 */
	public function testExpectedResult($installments) {

		$result = $installments->setExpectedResult(1230);
		$this->assertFalse($result);

		$result = $installments->setExpectedResult(["key" => "description"]);
		$this->assertInstanceOf(Installments::class, $result);
		$this->assertSame(["key" => "description"], $result->getExpectedResult());

		return $result;
	}

	/**
	 * @depends testExpectedResult
	 */
	public function testHeaders($installments) {

		$result = $installments->setHeaders(1230);
		$this->assertFalse($result);

		$result = $installments->setHeaders(["header" => "value"]);
		$this->assertInstanceOf(Installments::class, $result);
		$this->assertSame(["header" => "value"], $result->getHeaders());

		return $result;
	}

	/**
	 * @depends testHeaders
	 */
	public function testJson($installments) {

		$this->assertSame('[]', json_encode($installments));
	}

	public function testSend() {

		// Test no access token, error occured while getting it
		$installments = (new Installments())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setNumber(4111111111111111);

		$stub = test::double($installments, ["getAccessToken" => null, "getError" => "Some error"]);

		$result = $installments->send();

		$stub->verifyInvokedOnce("getAccessToken");
		$stub->verifyInvokedOnce("getError");

		$this->assertEmpty($result);
		$this->assertSame("Some error", $installments->getError());

		// test successful execution with production env
		$installments = (new Installments())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setNumber(4111111111111111);

		$stub = test::double($installments, ["getAccessToken" => "access_token"]);
		$url = test::double("\ATDev\Viva\Transaction\Url", ["getUrl" => "some-url"]);

		$response = new Fixture();
		$response->setStatusCode(200);
		$response->setContents('{"maxInstallments":"this_is_max_installments"}');

		$client = test::double("\GuzzleHttp\Client", ["request" => $response]);

		$result = $installments->send();

		$expected = new \stdClass();
		$expected->maxInstallments = "this_is_max_installments";
		$this->assertNull($installments->getError()); // No error
		$this->assertEquals($result, $expected); // Array with transaction id

		// Check the url is taken for production environment
		$url->verifyInvokedOnce("getUrl", [true]);
		$url->verifyNeverInvoked("getUrl", [false]);

		// Check access token and get when needed
		$stub->verifyInvokedMultipleTimes("getAccessToken", 2);

		// Check request paramenters
		$client->verifyInvokedOnce("request", [
			"GET",
			"some-url/nativecheckout/v2/installments",
			[
				"timeout" => 60,
				"connect_timeout" => 60,
				"exceptions" => false,
				'headers' => [
					"Authorization" => "Bearer access_token",
					"Accept" => "application/json",
					"cardNumber" => "4111111111111111"
				]
			]
		]);

		// test error status code with demo env but no error
		$installments->setTestMode(false);

		$response->setStatusCode(400);
		$response->setContents('{"some_text":"text"}');

		$result = $installments->send();

		$this->assertSame('{"some_text":"text"}', $installments->getError());
		$this->assertNull($result);

		$url->verifyInvokedOnce("getUrl", [false]);

		// test error status code with error
		$response->setStatusCode(100);
		$response->setContents('{"message":"this is error text"}');

		$result = $installments->send();

		$this->assertSame("this is error text", $installments->getError());
		$this->assertNull($result);

		// test error status code with empty response
		$response->setStatusCode(310);
		$response->setContents('');

		$result = $installments->send();

		$this->assertSame("An unknown error occured", $installments->getError());
		$this->assertNull($result);

		// test success status code but no charge token
		$response->setStatusCode(200);
		$response->setContents('{"no_maxInstallments":"it_has_to_be_here"}');

		$result = $installments->send();

		$this->assertSame("Max installments is absent in response", $installments->getError());
		$this->assertNull($result);

		// test success status code but unparsable response
		$response->setStatusCode(200);
		$response->setContents('{"ololol"ere"}');

		$result = $installments->send();

		$this->assertSame("Max installments is absent in response", $installments->getError());
		$this->assertNull($result);
	}

	protected function tearDown(): void {

		test::clean(); // remove all registered test doubles
	}
}