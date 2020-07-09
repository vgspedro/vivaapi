<?php namespace ATDev\Viva\Tests\Transaction;

use \PHPUnit\Framework\TestCase;
use \ATDev\Viva\Transaction\Url;

class UrlTest extends TestCase {

	public function testGetUrlDefault() {

		$url = Url::getUrl();
		$this->assertSame("https://api.vivapayments.com", $url);

		$url = Url::getUrl(false);
		$this->assertSame("https://api.vivapayments.com", $url);

		$url = Url::getUrl(true);
		$this->assertSame("https://demo-api.vivapayments.com", $url);
	}
}