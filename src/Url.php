<?php namespace ATDev\Viva;

/**
 * An url enumeration class
 */
abstract class Url {

	/** @const Live api url */
	const LIVE_URL = '';
	/** @const Test api url */
	const TEST_URL = '';

	/**
	 * Gets api url
	 *
	 * @param boolean $testMode
	 *
	 * @return string
	 */
	public static function getUrl($testMode = false) {

		return $testMode ? static::TEST_URL : static::LIVE_URL;
	}
}