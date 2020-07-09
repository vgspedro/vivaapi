<?php namespace ATDev\Viva\Tests\Transaction;

use \PHPUnit\Framework\TestCase;
use \AspectMock\Test as test;

use \ATDev\Viva\Transaction\Cancel;
use \ATDev\Viva\Tests\Fixture;

class CancelTest extends TestCase {

	public function testClientId() {

		$cancel = new Cancel();

		$result = $cancel->setClientId(123);
		$this->assertFalse($result);

		$result = $cancel->setClientId("asd");
		$this->assertInstanceOf(Cancel::class, $result);
		$this->assertSame("asd", $result->getClientId());

		return $result;
	}

	/**
	 * @depends testClientId
	 */
	public function testClientSecret($cancel) {

		$result = $cancel->setClientSecret(123);
		$this->assertFalse($result);

		$result = $cancel->setClientSecret("zxc");
		$this->assertInstanceOf(Cancel::class, $result);
		$this->assertSame("zxc", $result->getClientSecret());

		return $result;
	}

	/**
	 * @depends testClientSecret
	 */
	public function testTestMode($cancel) {

		$result = $cancel->setTestMode(123);
		$this->assertFalse($result);

		$result = $cancel->setTestMode(true);
		$this->assertInstanceOf(Cancel::class, $result);
		$this->assertTrue($result->getTestMode());

		return $result;
	}

	/**
	 * @depends testTestMode
	 */
	public function testSourceCode($cancel) {

		// Is not int or string
		$result = $cancel->setSourceCode(new \stdClass());
		$this->assertFalse($result);

		// String
		$result = $cancel->setSourceCode("1234");
		$this->assertInstanceOf(Cancel::class, $result);
		$this->assertSame("1234", $result->getSourceCode());

		// Int
		$result = $cancel->setSourceCode(4321);
		$this->assertInstanceOf(Cancel::class, $result);
		$this->assertSame("4321", $result->getSourceCode());

		return $result;
	}

	/**
	 * @depends testSourceCode
	 */
	public function testAmount($cancel) {

		$result = $cancel->setAmount("1230");
		$this->assertFalse($result);

		$result = $cancel->setAmount(1230);
		$this->assertInstanceOf(Cancel::class, $result);
		$this->assertSame(1230, $result->getAmount());

		return $result;
	}

	/**
	 * @depends testAmount
	 */
	public function testTransactionId($cancel) {

		$result = $cancel->setTransactionId(1230);
		$this->assertFalse($result);

		$result = $cancel->setTransactionId("1230-rr-345");
		$this->assertInstanceOf(Cancel::class, $result);
		$this->assertSame("1230-rr-345", $result->getTransactionId());

		return $result;
	}

	/**
	 * @depends testTransactionId
	 */
	public function testAccessToken($cancel) {

		$result = $cancel->setAccessToken(1230);
		$this->assertFalse($result);

		$result = $cancel->setAccessToken("1230");
		$this->assertInstanceOf(Cancel::class, $result);
		$this->assertSame("1230", $result->getAccessToken());

		return $result;
	}

	public function testGetAccessToken() {

		// Verify getting with error
		$cancel = new Cancel();
		$cancel->setClientId("asd");
		$cancel->setClientSecret("zxc");
		$cancel->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => null, "getError" => "An error occured"]);

		$result = $cancel->getAccessToken();

		$this->assertEmpty($result);
		$this->assertSame("An error occured", $cancel->getError());

		$auth->verifyInvokedOnce("setClientId", ["asd"]);
		$auth->verifyInvokedOnce("setClientSecret", ["zxc"]);
		$auth->verifyInvokedOnce("setTestMode", [true]);
		$auth->verifyInvokedOnce("getAccessToken");
		$auth->verifyInvokedOnce("getError");

		// Verify success
		$cancel2 = new Cancel();
		$cancel2->setClientId("asd");
		$cancel2->setClientSecret("zxc");
		$cancel2->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => "the_token", "getError" => null]);

		$result = $cancel2->getAccessToken();

		$this->assertSame("the_token", $result);
		$this->assertEmpty($cancel2->getError());
	}

	/**
	 * @depends testAccessToken
	 */
	public function testJson($cancel) {

		$this->assertSame('[]', json_encode($cancel));
	}

	public function testSend() {

		// Test no access token, error occured while getting it
		$cancel = (new Cancel())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setSourceCode("1414")
			->setAmount(1230)
			->setTransactionId("123-xx-123");

		$stub = test::double($cancel, ["getAccessToken" => null, "getError" => "Some error"]);

		$result = $cancel->send();

		$stub->verifyInvokedOnce("getAccessToken");
		$stub->verifyInvokedOnce("getError");

		$this->assertEmpty($result);
		$this->assertSame("Some error", $cancel->getError());

		// test successful execution with production env, no amount and source code
		$cancel = (new Cancel())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setTransactionId("123-xx-123");

		$stub = test::double($cancel, ["getAccessToken" => "access_token"]);
		$url = test::double("\ATDev\Viva\Transaction\Url", ["getUrl" => "some-url"]);

		$response = new Fixture();
		$response->setStatusCode(200);
		$response->setContents('{"transactionId":"this_is_transaction_id"}');

		$client = test::double("\GuzzleHttp\Client", ["request" => $response]);

		$result = $cancel->send();

		$expected = new \stdClass();
		$expected->transactionId = "this_is_transaction_id";
		$this->assertNull($cancel->getError()); // No error
		$this->assertEquals($result, $expected); // Array with transaction id

		// Check the url is taken for production environment
		$url->verifyInvokedOnce("getUrl", [true]);
		$url->verifyNeverInvoked("getUrl", [false]);

		// Check access token and get when needed
		$stub->verifyInvokedMultipleTimes("getAccessToken", 2);

		// Check request paramenters
		$client->verifyInvokedOnce("request", [
			"DELETE",
			"some-url/nativecheckout/v2/transactions/123-xx-123",
			[
				"timeout" => 60,
				"connect_timeout" => 60,
				"exceptions" => false,
				'headers' => [
					"Authorization" => "Bearer access_token",
					"Accept" => "application/json"
				]
			]
		]);

		// test error status code with demo env but no error, with source code
		$cancel->setTestMode(false);
		$cancel->setSourceCode("2000");

		$response->setStatusCode(400);
		$response->setContents('{"some_text":"text"}');

		$result = $cancel->send();

		$this->assertSame('{"some_text":"text"}', $cancel->getError());
		$this->assertNull($result);

		$url->verifyInvokedOnce("getUrl", [false]);
		$client->verifyInvokedOnce("request", [
			"DELETE",
			"some-url/nativecheckout/v2/transactions/123-xx-123?sourceCode=2000",
			[
				"timeout" => 60,
				"connect_timeout" => 60,
				"exceptions" => false,
				'headers' => [
					"Authorization" => "Bearer access_token",
					"Accept" => "application/json"
				]
			]
		]);

		// test error status code with error, with amount
		$cancel->setSourceCode("");
		$cancel->setAmount(3000);

		$response->setStatusCode(100);
		$response->setContents('{"message":"this is error text"}');

		$result = $cancel->send();

		$this->assertSame("this is error text", $cancel->getError());
		$this->assertNull($result);

		$client->verifyInvokedOnce("request", [
			"DELETE",
			"some-url/nativecheckout/v2/transactions/123-xx-123?amount=3000",
			[
				"timeout" => 60,
				"connect_timeout" => 60,
				"exceptions" => false,
				'headers' => [
					"Authorization" => "Bearer access_token",
					"Accept" => "application/json"
				]
			]
		]);

		// test error status code with empty response, with amount and source code
		$cancel->setSourceCode("2000");

		$response->setStatusCode(310);
		$response->setContents('');

		$result = $cancel->send();

		$this->assertSame("An unknown error occured", $cancel->getError());
		$this->assertNull($result);

		$client->verifyInvokedOnce("request", [
			"DELETE",
			"some-url/nativecheckout/v2/transactions/123-xx-123?amount=3000&sourceCode=2000",
			[
				"timeout" => 60,
				"connect_timeout" => 60,
				"exceptions" => false,
				'headers' => [
					"Authorization" => "Bearer access_token",
					"Accept" => "application/json"
				]
			]
		]);

		// test success status code but no transaction id
		$response->setStatusCode(200);
		$response->setContents('{"no_transaction_id":"it_has_to_be_here"}');

		$result = $cancel->send();

		$this->assertSame("Transaction id is absent in response", $cancel->getError());
		$this->assertNull($result);

		// test success status code but unparsable response
		$response->setStatusCode(200);
		$response->setContents('{"ololol"ere"}');

		$result = $cancel->send();

		$this->assertSame("Transaction id is absent in response", $cancel->getError());
		$this->assertNull($result);
	}

	protected function tearDown(): void {

		test::clean(); // remove all registered test doubles
	}
}