<?php
/**
 * Email Validation Implementation Model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Validation;

class Password implements ValidationInterface 
{

	protected $value, $rule;

	public function __construct(string $value, string $rule = '') 
	{
		$this->value = trim($value);
		$this->rule = trim($rule);
	}

	/**
	 * [[Description]]
	 * @return string [[Description]]
	 */
	public function validate(): string 
	{
        if (!password_verify($this->value, $this->rule)) {
			return false;
        }
		return true;
	}

}
