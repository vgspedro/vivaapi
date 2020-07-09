<?php namespace ATDev\Viva\Tests\Transaction;

use \PHPUnit\Framework\TestCase;
use \AspectMock\Test as test;

use \ATDev\Viva\Transaction\ChargeToken;
use \ATDev\Viva\Tests\Fixture;

class ChargeTokenTest extends TestCase {

	public function testClientId() {

		$chargeToken = new ChargeToken();

		$result = $chargeToken->setClientId(123);
		$this->assertFalse($result);

		$result = $chargeToken->setClientId("asd");
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame("asd", $result->getClientId());

		return $result;
	}

	/**
	 * @depends testClientId
	 */
	public function testClientSecret($chargeToken) {

		$result = $chargeToken->setClientSecret(123);
		$this->assertFalse($result);

		$result = $chargeToken->setClientSecret("zxc");
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame("zxc", $result->getClientSecret());

		return $result;
	}

	/**
	 * @depends testClientSecret
	 */
	public function testTestMode($chargeToken) {

		$result = $chargeToken->setTestMode(123);
		$this->assertFalse($result);

		$result = $chargeToken->setTestMode(true);
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertTrue($result->getTestMode());

		return $result;
	}

	/**
	 * @depends testTestMode
	 */
	public function testAmount($chargeToken) {

		$result = $chargeToken->setAmount("1230");
		$this->assertFalse($result);

		$result = $chargeToken->setAmount(1230);
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame(1230, $result->getAmount());

		return $result;
	}

	/**
	 * @depends testAmount
	 */
	public function testCvc($chargeToken) {

		// Is not int or string
		$result = $chargeToken->setCvc(new \stdClass());
		$this->assertFalse($result);

		// String
		$result = $chargeToken->setCvc("1234");
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame("1234", $result->getCvc());

		// Int
		$result = $chargeToken->setCvc(4321);
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame("4321", $result->getCvc());

		return $result;
	}

	/**
	 * @depends testCvc
	 */
	public function testNumber($chargeToken) {

		// Is not int or string
		$result = $chargeToken->setNumber(new \stdClass());
		$this->assertFalse($result);

		// String
		$result = $chargeToken->setNumber("1234123412341234");
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame("1234123412341234", $result->getNumber());

		// Int
		$result = $chargeToken->setNumber(4321432143214321);
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame("4321432143214321", $result->getNumber());

		return $result;
	}

	/**
	 * @depends testNumber
	 */
	public function testHolderName($chargeToken) {

		$result = $chargeToken->setHolderName(123);
		$this->assertFalse($result);

		$result = $chargeToken->setHolderName("John Doe");
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame("John Doe", $result->getHolderName());

		return $result;
	}

	/**
	 * @depends testHolderName
	 */
	public function testExpirationYear($chargeToken) {

		$result = $chargeToken->setExpirationYear("2023");
		$this->assertFalse($result);

		$result = $chargeToken->setExpirationYear(2022);
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame(2022, $result->getExpirationYear());

		return $result;
	}

	/**
	 * @depends testExpirationYear
	 */
	public function testExpirationMonth($chargeToken) {

		$result = $chargeToken->setExpirationMonth("11");
		$this->assertFalse($result);

		$result = $chargeToken->setExpirationMonth(10);
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame(10, $result->getExpirationMonth());

		return $result;
	}

	/**
	 * @depends testExpirationMonth
	 */
	public function testSessionRedirectUrl($chargeToken) {

		$result = $chargeToken->setSessionRedirectUrl(123);
		$this->assertFalse($result);

		$result = $chargeToken->setSessionRedirectUrl("https://example.com");
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame("https://example.com", $result->getSessionRedirectUrl());

		return $result;
	}

	/**
	 * @depends testSessionRedirectUrl
	 */
	public function testAccessToken($chargeToken) {

		$result = $chargeToken->setAccessToken(1230);
		$this->assertFalse($result);

		$result = $chargeToken->setAccessToken("1230");
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame("1230", $result->getAccessToken());

		return $result;
	}

	public function testGetAccessToken() {

		// Verify getting with error
		$chargeToken = new ChargeToken();
		$chargeToken->setClientId("asd");
		$chargeToken->setClientSecret("zxc");
		$chargeToken->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => null, "getError" => "An error occured"]);

		$result = $chargeToken->getAccessToken();

		$this->assertEmpty($result);
		$this->assertSame("An error occured", $chargeToken->getError());

		$auth->verifyInvokedOnce("setClientId", ["asd"]);
		$auth->verifyInvokedOnce("setClientSecret", ["zxc"]);
		$auth->verifyInvokedOnce("setTestMode", [true]);
		$auth->verifyInvokedOnce("getAccessToken");
		$auth->verifyInvokedOnce("getError");

		// Verify success
		$chargeToken2 = new ChargeToken();
		$chargeToken2->setClientId("asd");
		$chargeToken2->setClientSecret("zxc");
		$chargeToken2->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => "the_token", "getError" => null]);

		$result = $chargeToken2->getAccessToken();

		$this->assertSame("the_token", $result);
		$this->assertEmpty($chargeToken2->getError());
	}

	/**
	 * @depends testAccessToken
	 */
	public function testExpectedResult($chargeToken) {

		$result = $chargeToken->setExpectedResult(1230);
		$this->assertFalse($result);

		$result = $chargeToken->setExpectedResult(["key" => "description"]);
		$this->assertInstanceOf(ChargeToken::class, $result);
		$this->assertSame(["key" => "description"], $result->getExpectedResult());

		return $result;
	}

	/**
	 * @depends testExpectedResult
	 */
	public function testJson($chargeToken) {

		$this->assertSame('{"amount":1230,"cvc":"4321","number":"4321432143214321","holderName":"John Doe","expirationYear":2022,"expirationMonth":10,"sessionRedirectUrl":"https:\/\/example.com"}', json_encode($chargeToken));
	}

	public function testSend() {

		// Test no access token, error occured while getting it
		$chargeToken = (new ChargeToken())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setAmount(3000)
			->setCvc(123)
			->setNumber(4111111111111111)
			->setHolderName("John Doe")
			->setExpirationYear(2022)
			->setExpirationMonth(12)
			->setSessionRedirectUrl("https://example.com");

		$stub = test::double($chargeToken, ["getAccessToken" => null, "getError" => "Some error"]);

		$result = $chargeToken->send();

		$stub->verifyInvokedOnce("getAccessToken");
		$stub->verifyInvokedOnce("getError");

		$this->assertEmpty($result);
		$this->assertSame("Some error", $chargeToken->getError());

		// test successful execution with production env
		$chargeToken = (new ChargeToken())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setAmount(3000)
			->setCvc(123)
			->setNumber(4111111111111111)
			->setHolderName("John Doe")
			->setExpirationYear(2022)
			->setExpirationMonth(12)
			->setSessionRedirectUrl("https://example.com");

		$stub = test::double($chargeToken, ["getAccessToken" => "access_token"]);
		$url = test::double("\ATDev\Viva\Transaction\Url", ["getUrl" => "some-url"]);

		$response = new Fixture();
		$response->setStatusCode(200);
		$response->setContents('{"chargeToken":"this_is_charge_token","redirectToACSForm":"this_is_html"}');

		$client = test::double("\GuzzleHttp\Client", ["request" => $response]);

		$result = $chargeToken->send();

		$expected = new \stdClass();
		$expected->chargeToken = "this_is_charge_token";
		$expected->redirectToACSForm = "this_is_html";
		$this->assertNull($chargeToken->getError()); // No error
		$this->assertEquals($result, $expected); // Array with transaction id

		// Check the url is taken for production environment
		$url->verifyInvokedOnce("getUrl", [true]);
		$url->verifyNeverInvoked("getUrl", [false]);

		// Check access token and get when needed
		$stub->verifyInvokedMultipleTimes("getAccessToken", 2);

		// Check request paramenters
		$client->verifyInvokedOnce("request", [
			"POST",
			"some-url/nativecheckout/v2/chargetokens",
			[
				"timeout" => 60,
				"connect_timeout" => 60,
				"exceptions" => false,
				'headers' => [
					"Authorization" => "Bearer access_token",
					"Accept" => "application/json"
				],
				"json" => $chargeToken
			]
		]);

		// test error status code with demo env but no error
		$chargeToken->setTestMode(false);

		$response->setStatusCode(400);
		$response->setContents('{"some_text":"text"}');

		$result = $chargeToken->send();

		$this->assertSame('{"some_text":"text"}', $chargeToken->getError());
		$this->assertNull($result);

		$url->verifyInvokedOnce("getUrl", [false]);

		// test error status code with error
		$response->setStatusCode(100);
		$response->setContents('{"message":"this is error text"}');

		$result = $chargeToken->send();

		$this->assertSame("this is error text", $chargeToken->getError());
		$this->assertNull($result);

		// test error status code with empty response
		$response->setStatusCode(310);
		$response->setContents('');

		$result = $chargeToken->send();

		$this->assertSame("An unknown error occured", $chargeToken->getError());
		$this->assertNull($result);

		// test success status code but no charge token
		$response->setStatusCode(200);
		$response->setContents('{"no_chargeToken":"it_has_to_be_here"}');

		$result = $chargeToken->send();

		$this->assertSame("Charge Token is absent in response", $chargeToken->getError());
		$this->assertNull($result);

		// test success status code but no html to render
		$response->setStatusCode(200);
		$response->setContents('{"chargeToken":"it_is_here","no_redirectToACSForm":"it_has_to_be_here"}');

		$result = $chargeToken->send();

		$this->assertSame("HTML to render is absent in response", $chargeToken->getError());
		$this->assertNull($result);

		// test success status code but unparsable response
		$response->setStatusCode(200);
		$response->setContents('{"ololol"ere"}');

		$result = $chargeToken->send();

		$this->assertSame("Charge Token is absent in response", $chargeToken->getError());
		$this->assertNull($result);
	}

	protected function tearDown(): void {

		test::clean(); // remove all registered test doubles
	}
}