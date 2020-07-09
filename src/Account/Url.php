<?php
namespace VgsPedro\VivaApi\Account;

use \VgsPedro\VivaApi\Url as BaseUrl;

/**
 * An url enumeration class
 */
class Url extends BaseUrl {

	/** @const Live api url */
	const LIVE_URL = "https://accounts.vivapayments.com";
	/** @const Test api url */
	const TEST_URL = "https://demo-accounts.vivapayments.com";
}