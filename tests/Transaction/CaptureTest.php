<?php namespace ATDev\Viva\Tests\Transaction;

use \PHPUnit\Framework\TestCase;
use \AspectMock\Test as test;

use \ATDev\Viva\Transaction\Capture;
use \ATDev\Viva\Tests\Fixture;

class CaptureTest extends TestCase {

	public function testClientId() {

		$capture = new Capture();

		$result = $capture->setClientId(123);
		$this->assertFalse($result);

		$result = $capture->setClientId("asd");
		$this->assertInstanceOf(Capture::class, $result);
		$this->assertSame("asd", $result->getClientId());

		return $result;
	}

	/**
	 * @depends testClientId
	 */
	public function testClientSecret($capture) {

		$result = $capture->setClientSecret(123);
		$this->assertFalse($result);

		$result = $capture->setClientSecret("zxc");
		$this->assertInstanceOf(Capture::class, $result);
		$this->assertSame("zxc", $result->getClientSecret());

		return $result;
	}

	/**
	 * @depends testClientSecret
	 */
	public function testTestMode($capture) {

		$result = $capture->setTestMode(123);
		$this->assertFalse($result);

		$result = $capture->setTestMode(true);
		$this->assertInstanceOf(Capture::class, $result);
		$this->assertTrue($result->getTestMode());

		return $result;
	}

	/**
	 * @depends testTestMode
	 */
	public function testSourceCode($capture) {

		// Is not int or string
		$result = $capture->setSourceCode(new \stdClass());
		$this->assertFalse($result);

		// String
		$result = $capture->setSourceCode("1234");
		$this->assertInstanceOf(Capture::class, $result);
		$this->assertSame("1234", $result->getSourceCode());

		// Int
		$result = $capture->setSourceCode(4321);
		$this->assertInstanceOf(Capture::class, $result);
		$this->assertSame("4321", $result->getSourceCode());

		return $result;
	}

	/**
	 * @depends testSourceCode
	 */
	public function testAmount($capture) {

		$result = $capture->setAmount("1230");
		$this->assertFalse($result);

		$result = $capture->setAmount(1230);
		$this->assertInstanceOf(Capture::class, $result);
		$this->assertSame(1230, $result->getAmount());

		return $result;
	}

	/**
	 * @depends testAmount
	 */
	public function testTransactionId($capture) {

		$result = $capture->setTransactionId(1230);
		$this->assertFalse($result);

		$result = $capture->setTransactionId("1230-rr-345");
		$this->assertInstanceOf(Capture::class, $result);
		$this->assertSame("1230-rr-345", $result->getTransactionId());

		return $result;
	}

	/**
	 * @depends testTransactionId
	 */
	public function testAccessToken($capture) {

		$result = $capture->setAccessToken(1230);
		$this->assertFalse($result);

		$result = $capture->setAccessToken("1230");
		$this->assertInstanceOf(Capture::class, $result);
		$this->assertSame("1230", $result->getAccessToken());

		return $result;
	}

	public function testGetAccessToken() {

		// Verify getting with error
		$capture = new Capture();
		$capture->setClientId("asd");
		$capture->setClientSecret("zxc");
		$capture->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => null, "getError" => "An error occured"]);

		$result = $capture->getAccessToken();

		$this->assertEmpty($result);
		$this->assertSame("An error occured", $capture->getError());

		$auth->verifyInvokedOnce("setClientId", ["asd"]);
		$auth->verifyInvokedOnce("setClientSecret", ["zxc"]);
		$auth->verifyInvokedOnce("setTestMode", [true]);
		$auth->verifyInvokedOnce("getAccessToken");
		$auth->verifyInvokedOnce("getError");

		// Verify success
		$capture2 = new Capture();
		$capture2->setClientId("asd");
		$capture2->setClientSecret("zxc");
		$capture2->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => "the_token", "getError" => null]);

		$result = $capture2->getAccessToken();

		$this->assertSame("the_token", $result);
		$this->assertEmpty($capture2->getError());
	}

	/**
	 * @depends testAccessToken
	 */
	public function testJson($capture) {

		$this->assertSame('{"amount":1230}', json_encode($capture));
	}

	public function testSend() {

		// Test no access token, error occured while getting it
		$capture = (new Capture())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setSourceCode("1414")
			->setAmount(1230)
			->setTransactionId("123-xx-123");

		$stub = test::double($capture, ["getAccessToken" => null, "getError" => "Some error"]);

		$result = $capture->send();

		$stub->verifyInvokedOnce("getAccessToken");
		$stub->verifyInvokedOnce("getError");

		$this->assertEmpty($result);
		$this->assertSame("Some error", $capture->getError());

		// test successful execution with production env
		$capture = (new Capture())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setSourceCode("1414")
			->setAmount(1230)
			->setTransactionId("123-xx-123");

		$stub = test::double($capture, ["getAccessToken" => "access_token"]);
		$url = test::double("\ATDev\Viva\Transaction\Url", ["getUrl" => "some-url"]);

		$response = new Fixture();
		$response->setStatusCode(200);
		$response->setContents('{"transactionId":"this_is_transaction_id"}');

		$client = test::double("\GuzzleHttp\Client", ["request" => $response]);

		$result = $capture->send();

		$expected = new \stdClass();
		$expected->transactionId = "this_is_transaction_id";
		$this->assertNull($capture->getError()); // No error
		$this->assertEquals($result, $expected); // Array with transaction id

		// Check the url is taken for production environment
		$url->verifyInvokedOnce("getUrl", [true]);
		$url->verifyNeverInvoked("getUrl", [false]);

		// Check access token and get when needed
		$stub->verifyInvokedMultipleTimes("getAccessToken", 2);

		// Check request paramenters
		$client->verifyInvokedOnce("request", [
			"POST",
			"some-url/nativecheckout/v2/transactions/123-xx-123",
			[
				"timeout" => 60,
				"connect_timeout" => 60,
				"exceptions" => false,
				'headers' => [
					"Authorization" => "Bearer access_token",
					"Accept" => "application/json"
				],
				"json" => $capture
			]
		]);

		// test error status code with demo env but no error
		$capture->setTestMode(false);

		$response->setStatusCode(400);
		$response->setContents('{"some_text":"text"}');

		$result = $capture->send();

		$this->assertSame('{"some_text":"text"}', $capture->getError());
		$this->assertNull($result);

		$url->verifyInvokedOnce("getUrl", [false]);

		// test error status code with error
		$response->setStatusCode(100);
		$response->setContents('{"message":"this is error text"}');

		$result = $capture->send();

		$this->assertSame("this is error text", $capture->getError());
		$this->assertNull($result);

		// test error status code with empty response
		$response->setStatusCode(310);
		$response->setContents('');

		$result = $capture->send();

		$this->assertSame("An unknown error occured", $capture->getError());
		$this->assertNull($result);

		// test success status code but no transaction id
		$response->setStatusCode(200);
		$response->setContents('{"no_transaction_id":"it_has_to_be_here"}');

		$result = $capture->send();

		$this->assertSame("Transaction id is absent in response", $capture->getError());
		$this->assertNull($result);

		// test success status code but unparsable response
		$response->setStatusCode(200);
		$response->setContents('{"ololol"ere"}');

		$result = $capture->send();

		$this->assertSame("Transaction id is absent in response", $capture->getError());
		$this->assertNull($result);
	}

	protected function tearDown(): void {

		test::clean(); // remove all registered test doubles
	}
}