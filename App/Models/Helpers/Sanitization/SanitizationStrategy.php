<?php
/**
 * Sanitization Strategy Model (strategy Design Pattern)
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Sanitization;


class SanitizationStrategy {


	protected $sanitization;


	public function __construct(SanitizationInterface $sanitization) {
		$this->sanitization = $sanitization;
	}


	public function sanitize() {
		return $this->sanitization->sanitize();
	}

}
