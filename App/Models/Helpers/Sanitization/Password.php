<?php
/**
 * Password Sanitization Implementation Model (strategy Design Pattern)
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Sanitization;

class Password implements SanitizationInterface {

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
        $this->output = password_hash($this->input, PASSWORD_BCRYPT, array('cost' => 12));
		return $this->output;
	}

}
