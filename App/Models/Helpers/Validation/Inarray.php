<?php
/**
 * Email Validation Implementation Model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Validation;

class Inarray implements ValidationInterface 
{

	protected $value, $rule;

	public function __construct(string $value, string $rule = '') 
	{
		$this->value = trim($value);
		$this->rule = json_decode($rule);
	}

	/**
	 * [[Description]]
	 * @return string [[Description]]
	 */
	public function validate(): string 
	{
        if (!in_array($this->value, $this->rule)) {
            return false;
        }
		return true;
	}

}
