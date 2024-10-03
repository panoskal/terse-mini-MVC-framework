<?php
/**
 * Float Sanitization Implementation Model (strategy Design Pattern)
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Sanitization;

class Float implements SanitizationInterface {

	protected $input;
	protected $output;

	public function __construct(string $input) {
		$this->input = (float) trim($input);
	}

	/**
	 * [[Description]]
	 * @return string [[Description]]
	 */
	public function sanitize() {
        $this->output = filter_var($this->input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		return $this->output;
	}

}
