<?php
/**
 * Email Sanitization Implementation Model (strategy Design Pattern)
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Sanitization;

class Email implements SanitizationInterface {

	protected $input;
	protected $output;

	public function __construct(string $input) {
		$this->input = (string) trim($input);
	}

	/**
	 * [[Description]]
	 * @return string [[Description]]
	 */
	public function sanitize() {
        $this->output = filter_var($this->input, FILTER_SANITIZE_EMAIL);
		return $this->output;
	}

}
