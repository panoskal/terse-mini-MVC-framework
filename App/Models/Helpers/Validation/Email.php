<?php
/**
 * Email Validation Implementation Model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Validation;

class Email implements ValidationInterface 
{

	protected $value, $rule;

	public function __construct(string $value, string $rule = '') 
	{
		$this->value = trim($value);
	}

	/**
	 * [[Description]]
	 * @return string [[Description]]
	 */
	public function validate(): string 
	{
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
			return false;
        }
		return true;
	}

}
