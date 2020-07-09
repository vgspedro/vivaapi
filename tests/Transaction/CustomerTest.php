<?php namespace ATDev\Viva\Tests\Transaction;

use \PHPUnit\Framework\TestCase;
use \AspectMock\Test as test;

use \ATDev\Viva\Transaction\Customer;

class CustomerTest extends TestCase {

	public function testEmail() {

		$customer = new Customer();

		// Check non-string parameter
		$result = $customer->setEmail(new \stdClass());
		$this->assertFalse($result);

		// Check invalid email
		$validator = test::double("\Egulias\EmailValidator\EmailValidator", ["isValid" => false]);
		$validation = test::double("\Egulias\EmailValidator\Validation\RFCValidation");

		$result = $customer->setEmail("kjhdfg");

		$this->assertFalse($result);
		$validator->verifyInvokedOnce("isValid", ["kjhdfg"]);

		// Check valid email
		$validator = test::double("\Egulias\EmailValidator\EmailValidator", ["isValid" => true]);

		$result = $customer->setEmail("test@test.com");
		$this->assertInstanceOf(Customer::class, $result);
		$this->assertSame("test@test.com", $result->getEmail());
		$validator->verifyInvokedOnce("isValid", ["test@test.com"]);

		return $result;
	}

	/**
	 * @depends testEmail
	 */
	public function testPhone($customer) {

		// Is not int or string
		$result = $customer->setPhone(new \stdClass());
		$this->assertFalse($result);

		// String
		$result = $customer->setPhone("123-123-123");
		$this->assertInstanceOf(Customer::class, $result);
		$this->assertSame("123-123-123", $result->getPhone());

		// Int
		$result = $customer->setPhone(123123123);
		$this->assertInstanceOf(Customer::class, $result);
		$this->assertSame("123123123", $result->getPhone());

		return $result;
	}

	/**
	 * @depends testPhone
	 */
	public function testFullName($customer) {

		$result = $customer->setFullName(123);
		$this->assertFalse($result);

		$result = $customer->setFullName("Full Name");
		$this->assertInstanceOf(Customer::class, $result);
		$this->assertSame("Full Name", $result->getFullName());

		return $result;
	}

	/**
	 * @depends testFullName
	 */
	public function testRequestLang($customer) {

		$result = $customer->setRequestLang(123);
		$this->assertFalse($result);

		$result = $customer->setRequestLang("EN-EN");
		$this->assertInstanceOf(Customer::class, $result);
		$this->assertSame("EN-EN", $result->getRequestLang());

		return $result;
	}

	/**
	 * @depends testRequestLang
	 */
	public function testCountryCode($customer) {

		$result = $customer->setCountryCode(123);
		$this->assertFalse($result);

		$result = $customer->setCountryCode("US");
		$this->assertInstanceOf(Customer::class, $result);
		$this->assertSame("US", $result->getCountryCode());

		return $result;
	}

	public function testEmpty() {

		$customer = new Customer();
		$this->assertTrue($customer->isEmpty());

		$customer->setEmail("test@test.com");
		$this->assertFalse($customer->isEmpty());

		$customer1 = new Customer();
		$customer1->setPhone("123-123-123");
		$this->assertFalse($customer1->isEmpty());

		$customer2 = new Customer();
		$customer2->setFullName("Full Name");
		$this->assertFalse($customer2->isEmpty());
	}

	public function testJson() {

		$customer = new Customer();
		$customer->setEmail("test@test.com")
			->setPhone("123-123-123")
			->setCountryCode("US");

		$this->assertSame('{"email":"test@test.com","phone":"123-123-123","countryCode":"US"}', json_encode($customer));

		$customer1 = new Customer();
		$customer1->setFullName("Full Name")
			->setRequestLang("EN-EN");

		$this->assertSame('{"fullname":"Full Name","requestLang":"EN-EN"}', json_encode($customer1));

	}

	protected function tearDown(): void {

		test::clean(); // remove all registered test doubles
	}
}