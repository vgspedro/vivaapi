<?php namespace ATDev\Viva\Tests\Account;

use \PHPUnit\Framework\TestCase;
use \ATDev\Viva\Account\Url;

class UrlTest extends TestCase {

	public function testGetUrl() {

		$url = Url::getUrl();
		$this->assertSame("https://accounts.vivapayments.com", $url);

		$url = Url::getUrl(false);
		$this->assertSame("https://accounts.vivapayments.com", $url);

		$url = Url::getUrl(true);
		$this->assertSame("https://demo-accounts.vivapayments.com", $url);
	}
}