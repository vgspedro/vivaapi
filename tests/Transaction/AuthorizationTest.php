<?php namespace ATDev\Viva\Tests\Transaction;

use \PHPUnit\Framework\TestCase;
use \AspectMock\Test as test;

use \ATDev\Viva\Transaction\Authorization;
use \ATDev\Viva\Transaction\Customer;
use \ATDev\Viva\Tests\Fixture;

class AuthorizationTest extends TestCase {

	public function testClientId() {

		$preAuth = new Authorization();

		$result = $preAuth->setClientId(123);
		$this->assertFalse($result);

		$result = $preAuth->setClientId("asd");
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("asd", $result->getClientId());

		return $result;
	}

	/**
	 * @depends testClientId
	 */
	public function testClientSecret($preAuth) {

		$result = $preAuth->setClientSecret(123);
		$this->assertFalse($result);

		$result = $preAuth->setClientSecret("zxc");
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("zxc", $result->getClientSecret());

		return $result;
	}

	/**
	 * @depends testClientSecret
	 */
	public function testTestMode($preAuth) {

		$result = $preAuth->setTestMode(123);
		$this->assertFalse($result);

		$result = $preAuth->setTestMode(true);
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertTrue($result->getTestMode());

		return $result;
	}

	/**
	 * @depends testTestMode
	 */
	public function testSourceCode($preAuth) {

		// Is not int or string
		$result = $preAuth->setSourceCode(new \stdClass());
		$this->assertFalse($result);

		// String
		$result = $preAuth->setSourceCode("1234");
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("1234", $result->getSourceCode());

		// Int
		$result = $preAuth->setSourceCode(4321);
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("4321", $result->getSourceCode());

		return $result;
	}

	/**
	 * @depends testSourceCode
	 */
	public function testAmount($preAuth) {

		$result = $preAuth->setAmount("1230");
		$this->assertFalse($result);

		$result = $preAuth->setAmount(1230);
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame(1230, $result->getAmount());

		return $result;
	}

	/**
	 * @depends testAmount
	 */
	public function testCustomer($preAuth) {

		$result = $preAuth->setCustomer("1230");
		$this->assertFalse($result);

		$customer = new Customer();
		$result = $preAuth->setCustomer($customer);
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame($customer, $result->getcustomer());

		return $result;
	}

	/**
	 * @depends testCustomer
	 */
	public function testChargeToken($preAuth) {

		$result = $preAuth->setChargeToken(1230);
		$this->assertFalse($result);

		$result = $preAuth->setChargeToken("1230");
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("1230", $result->getChargeToken());

		return $result;
	}

	/**
	 * @depends testChargeToken
	 */
	public function testInstallments($preAuth) {

		$result = $preAuth->setInstallments("5");
		$this->assertFalse($result);

		$result = $preAuth->setInstallments(5);
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame(5, $result->getInstallments());

		return $result;
	}

	/**
	 * @depends testInstallments
	 */
	public function testMerchantTrns($preAuth) {

		$result = $preAuth->setMerchantTrns(1230);
		$this->assertFalse($result);

		$result = $preAuth->setMerchantTrns("1230");
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("1230", $result->getMerchantTrns());

		return $result;
	}

	/**
	 * @depends testMerchantTrns
	 */
	public function testCustomerTrns($preAuth) {

		$result = $preAuth->setCustomerTrns(1230);
		$this->assertFalse($result);

		$result = $preAuth->setCustomerTrns("1230");
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("1230", $result->getCustomerTrns());

		return $result;
	}

	/**
	 * @depends testCustomerTrns
	 */
	public function testAccessToken($preAuth) {

		$result = $preAuth->setAccessToken(1230);
		$this->assertFalse($result);

		$result = $preAuth->setAccessToken("1230");
		$this->assertInstanceOf(Authorization::class, $result);
		$this->assertSame("1230", $result->getAccessToken());

		return $result;
	}

	public function testGetAccessToken() {

		// Verify getting with error
		$preAuth = new Authorization();
		$preAuth->setClientId("asd");
		$preAuth->setClientSecret("zxc");
		$preAuth->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => null, "getError" => "An error occured"]);

		$result = $preAuth->getAccessToken();

		$this->assertEmpty($result);
		$this->assertSame("An error occured", $preAuth->getError());

		$auth->verifyInvokedOnce("setClientId", ["asd"]);
		$auth->verifyInvokedOnce("setClientSecret", ["zxc"]);
		$auth->verifyInvokedOnce("setTestMode", [true]);
		$auth->verifyInvokedOnce("getAccessToken");
		$auth->verifyInvokedOnce("getError");

		// Verify success
		$preAuth2 = new Authorization();
		$preAuth2->setClientId("asd");
		$preAuth2->setClientSecret("zxc");
		$preAuth2->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => "the_token", "getError" => null]);

		$result = $preAuth2->getAccessToken();

		$this->assertSame("the_token", $result);
		$this->assertEmpty($preAuth2->getError());
	}

	public function testJson() {

		$cust = test::double("\ATDev\Viva\Transaction\Customer", ["jsonSerialize" => [], "isEmpty" => true]);

		$customer = new Customer();
		$preAuth = (new Authorization())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setSourceCode("1414")
			->setAmount(1230)
			->setChargeToken("qwe")
			->setCustomer($customer);

		$this->assertSame('{"amount":1230,"preauth":true,"sourceCode":"1414","chargeToken":"qwe"}', json_encode($preAuth));

		$cust = test::double("\ATDev\Viva\Transaction\Customer", ["jsonSerialize" => [], "isEmpty" => false]);

		$preAuth->setInstallments(10);
		$preAuth->setMerchantTrns("yui");
		$preAuth->setCustomerTrns("rty");

		$this->assertSame('{"amount":1230,"preauth":true,"sourceCode":"1414","chargeToken":"qwe","installments":10,"merchantTrns":"yui","customerTrns":"rty","customer":[]}', json_encode($preAuth));
	}

	public function testSend() {

		// Test no access token, error occured while getting it
		$preAuth = (new Authorization())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setSourceCode("1414")
			->setAmount(1230)
			->setChargeToken("qwe");

		$stub = test::double($preAuth, ["getAccessToken" => null, "getError" => "Some error"]);

		$result = $preAuth->send();

		$stub->verifyInvokedOnce("getAccessToken");
		$stub->verifyInvokedOnce("getError");

		$this->assertEmpty($result);
		$this->assertSame("Some error", $preAuth->getError());

		// test successful execution with production env
		$preAuth = (new Authorization())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setSourceCode("1414")
			->setAmount(1230)
			->setChargeToken("qwe");

		$stub = test::double($preAuth, ["getAccessToken" => "access_token"]);
		$url = test::double("\ATDev\Viva\Transaction\Url", ["getUrl" => "some-url"]);

		$response = new Fixture();
		$response->setStatusCode(200);
		$response->setContents('{"transactionId":"this_is_transaction_id"}');

		$client = test::double("\GuzzleHttp\Client", ["request" => $response]);

		$result = $preAuth->send();

		$expected = new \stdClass();
		$expected->transactionId = "this_is_transaction_id";
		$this->assertNull($preAuth->getError()); // No error
		$this->assertEquals($result, $expected); // Array with transaction id

		// Check the url is taken for production environment
		$url->verifyInvokedOnce("getUrl", [true]);
		$url->verifyNeverInvoked("getUrl", [false]);

		// Check access token and get when needed
		$stub->verifyInvokedMultipleTimes("getAccessToken", 2);

		// Check request paramenters
		$client->verifyInvokedOnce("request", [
			"POST",
			"some-url/nativecheckout/v2/transactions",
			[
				"timeout" => 60,
				"connect_timeout" => 60,
				"exceptions" => false,
				'headers' => [
					"Authorization" => "Bearer access_token",
					"Accept" => "application/json"
				],
				"json" => $preAuth
			]
		]);

		// test error status code with demo env but no error
		$preAuth->setTestMode(false);

		$response->setStatusCode(400);
		$response->setContents('{"some_text":"text"}');

		$result = $preAuth->send();

		$this->assertSame('{"some_text":"text"}', $preAuth->getError());
		$this->assertNull($result);

		$url->verifyInvokedOnce("getUrl", [false]);

		// test error status code with error
		$response->setStatusCode(100);
		$response->setContents('{"message":"this is error text"}');

		$result = $preAuth->send();

		$this->assertSame("this is error text", $preAuth->getError());
		$this->assertNull($result);

		// test error status code with empty response
		$response->setStatusCode(310);
		$response->setContents('');

		$result = $preAuth->send();

		$this->assertSame("An unknown error occured", $preAuth->getError());
		$this->assertNull($result);

		// test success status code but no transaction id
		$response->setStatusCode(200);
		$response->setContents('{"no_transaction_id":"it_has_to_be_here"}');

		$result = $preAuth->send();

		$this->assertSame("Transaction id is absent in response", $preAuth->getError());
		$this->assertNull($result);

		// test success status code but unparsable response
		$response->setStatusCode(200);
		$response->setContents('{"ololol"ere"}');

		$result = $preAuth->send();

		$this->assertSame("Transaction id is absent in response", $preAuth->getError());
		$this->assertNull($result);
	}

	protected function tearDown(): void {

		test::clean(); // remove all registered test doubles
	}
}