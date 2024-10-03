<?php
/**
 * Regex Validation Implementation Model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Validation;

class Regex implements ValidationInterface 
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
        if (!preg_match($this->rule, $this->value)) {
			return false;
        }
		return true;
	}

}
