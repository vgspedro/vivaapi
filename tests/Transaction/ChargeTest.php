<?php namespace ATDev\Viva\Tests\Transaction;

use \PHPUnit\Framework\TestCase;
use \AspectMock\Test as test;

use \ATDev\Viva\Transaction\Charge;
use \ATDev\Viva\Transaction\Customer;
use \ATDev\Viva\Tests\Fixture;

class ChargeTest extends TestCase {

	public function testClientId() {

		$charge = new Charge();

		$result = $charge->setClientId(123);
		$this->assertFalse($result);

		$result = $charge->setClientId("asd");
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame("asd", $result->getClientId());

		return $result;
	}

	/**
	 * @depends testClientId
	 */
	public function testClientSecret($charge) {

		$result = $charge->setClientSecret(123);
		$this->assertFalse($result);

		$result = $charge->setClientSecret("zxc");
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame("zxc", $result->getClientSecret());

		return $result;
	}

	/**
	 * @depends testClientSecret
	 */
	public function testTestMode($charge) {

		$result = $charge->setTestMode(123);
		$this->assertFalse($result);

		$result = $charge->setTestMode(true);
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertTrue($result->getTestMode());

		return $result;
	}

	/**
	 * @depends testTestMode
	 */
	public function testSourceCode($charge) {

		// Is not int or string
		$result = $charge->setSourceCode(new \stdClass());
		$this->assertFalse($result);

		// String
		$result = $charge->setSourceCode("1234");
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame("1234", $result->getSourceCode());

		// Int
		$result = $charge->setSourceCode(4321);
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame("4321", $result->getSourceCode());

		return $result;
	}

	/**
	 * @depends testSourceCode
	 */
	public function testAmount($charge) {

		$result = $charge->setAmount("1230");
		$this->assertFalse($result);

		$result = $charge->setAmount(1230);
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame(1230, $result->getAmount());

		return $result;
	}

	/**
	 * @depends testAmount
	 */
	public function testCustomer($charge) {

		$result = $charge->setCustomer("1230");
		$this->assertFalse($result);

		$customer = new Customer();
		$result = $charge->setCustomer($customer);
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame($customer, $result->getcustomer());

		return $result;
	}

	/**
	 * @depends testCustomer
	 */
	public function testChargeToken($charge) {

		$result = $charge->setChargeToken(1230);
		$this->assertFalse($result);

		$result = $charge->setChargeToken("1230");
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame("1230", $result->getChargeToken());

		return $result;
	}

	/**
	 * @depends testChargeToken
	 */
	public function testInstallments($charge) {

		$result = $charge->setInstallments("5");
		$this->assertFalse($result);

		$result = $charge->setInstallments(5);
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame(5, $result->getInstallments());

		return $result;
	}

	/**
	 * @depends testInstallments
	 */
	public function testMerchantTrns($charge) {

		$result = $charge->setMerchantTrns(1230);
		$this->assertFalse($result);

		$result = $charge->setMerchantTrns("1230");
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame("1230", $result->getMerchantTrns());

		return $result;
	}

	/**
	 * @depends testMerchantTrns
	 */
	public function testCustomerTrns($charge) {

		$result = $charge->setCustomerTrns(1230);
		$this->assertFalse($result);

		$result = $charge->setCustomerTrns("1230");
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame("1230", $result->getCustomerTrns());

		return $result;
	}

	/**
	 * @depends testCustomerTrns
	 */
	public function testAccessToken($charge) {

		$result = $charge->setAccessToken(1230);
		$this->assertFalse($result);

		$result = $charge->setAccessToken("1230");
		$this->assertInstanceOf(Charge::class, $result);
		$this->assertSame("1230", $result->getAccessToken());

		return $result;
	}

	public function testGetAccessToken() {

		// Verify getting with error
		$charge = new Charge();
		$charge->setClientId("asd");
		$charge->setClientSecret("zxc");
		$charge->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => null, "getError" => "An error occured"]);

		$result = $charge->getAccessToken();

		$this->assertEmpty($result);
		$this->assertSame("An error occured", $charge->getError());

		$auth->verifyInvokedOnce("setClientId", ["asd"]);
		$auth->verifyInvokedOnce("setClientSecret", ["zxc"]);
		$auth->verifyInvokedOnce("setTestMode", [true]);
		$auth->verifyInvokedOnce("getAccessToken");
		$auth->verifyInvokedOnce("getError");

		// Verify success
		$charge2 = new Charge();
		$charge2->setClientId("asd");
		$charge2->setClientSecret("zxc");
		$charge2->setTestMode(true);

		$auth = test::double("\ATDev\Viva\Account\Authorization", ["getAccessToken" => "the_token", "getError" => null]);

		$result = $charge2->getAccessToken();

		$this->assertSame("the_token", $result);
		$this->assertEmpty($charge2->getError());
	}

	public function testJson() {

		$cust = test::double("\ATDev\Viva\Transaction\Customer", ["jsonSerialize" => [], "isEmpty" => true]);

		$customer = new Customer();
		$charge = (new Charge())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setSourceCode("1414")
			->setAmount(1230)
			->setChargeToken("qwe")
			->setCustomer($customer);

		$this->assertSame('{"amount":1230,"preauth":false,"sourceCode":"1414","chargeToken":"qwe"}', json_encode($charge));

		$cust = test::double("\ATDev\Viva\Transaction\Customer", ["jsonSerialize" => [], "isEmpty" => false]);

		$charge->setInstallments(10);
		$charge->setMerchantTrns("yui");
		$charge->setCustomerTrns("rty");

		$this->assertSame('{"amount":1230,"preauth":false,"sourceCode":"1414","chargeToken":"qwe","installments":10,"merchantTrns":"yui","customerTrns":"rty","customer":[]}', json_encode($charge));
	}

	public function testSend() {

		// Test no access token, error occured while getting it
		$charge = (new Charge())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setSourceCode("1414")
			->setAmount(1230)
			->setChargeToken("qwe");

		$stub = test::double($charge, ["getAccessToken" => null, "getError" => "Some error"]);

		$result = $charge->send();

		$stub->verifyInvokedOnce("getAccessToken");
		$stub->verifyInvokedOnce("getError");

		$this->assertEmpty($result);
		$this->assertSame("Some error", $charge->getError());

		// test successful execution with production env
		$charge = (new Charge())
			->setClientId("asd")
			->setClientSecret("zxc")
			->setTestMode(true)
			->setSourceCode("1414")
			->setAmount(1230)
			->setChargeToken("qwe");

		$stub = test::double($charge, ["getAccessToken" => "access_token"]);
		$url = test::double("\ATDev\Viva\Transaction\Url", ["getUrl" => "some-url"]);

		$response = new Fixture();
		$response->setStatusCode(200);
		$response->setContents('{"transactionId":"this_is_transaction_id"}');

		$client = test::double("\GuzzleHttp\Client", ["request" => $response]);

		$result = $charge->send();

		$expected = new \stdClass();
		$expected->transactionId = "this_is_transaction_id";
		$this->assertNull($charge->getError()); // No error
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
				"json" => $charge
			]
		]);

		// test error status code with demo env but no error
		$charge->setTestMode(false);

		$response->setStatusCode(400);
		$response->setContents('{"some_text":"text"}');

		$result = $charge->send();

		$this->assertSame('{"some_text":"text"}', $charge->getError());
		$this->assertNull($result);

		$url->verifyInvokedOnce("getUrl", [false]);

		// test error status code with error
		$response->setStatusCode(100);
		$response->setContents('{"message":"this is error text"}');

		$result = $charge->send();

		$this->assertSame("this is error text", $charge->getError());
		$this->assertNull($result);

		// test error status code with empty response
		$response->setStatusCode(310);
		$response->setContents('');

		$result = $charge->send();

		$this->assertSame("An unknown error occured", $charge->getError());
		$this->assertNull($result);

		// test success status code but no transaction id
		$response->setStatusCode(200);
		$response->setContents('{"no_transaction_id":"it_has_to_be_here"}');

		$result = $charge->send();

		$this->assertSame("Transaction id is absent in response", $charge->getError());
		$this->assertNull($result);

		// test success status code but unparsable response
		$response->setStatusCode(200);
		$response->setContents('{"ololol"ere"}');

		$result = $charge->send();

		$this->assertSame("Transaction id is absent in response", $charge->getError());
		$this->assertNull($result);
	}

	protected function tearDown(): void {

		test::clean(); // remove all registered test doubles
	}
}