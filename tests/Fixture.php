<?php namespace ATDev\Viva\Tests;

/**
 * Fixture class to stub methods of classes returned from libraries
 */
class Fixture {

	private $statusCode;
	private $contents;

	public function getStatusCode() {

		return $this->statusCode;
	}

	public function setStatusCode($statusCode) {

		$this->statusCode = $statusCode;
	}

	public function getContents() {

		return $this->contents;
	}

	public function setContents($contents) {

		$this->contents = $contents;
	}

	public function getBody() {

		return $this;
	}
}