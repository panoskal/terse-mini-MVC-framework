<?php
/**
 * Required Validation Implementation Model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Validation;

class Required implements ValidationInterface 
{

	protected $value, $rule;

	public function __construct(string $value, string $rule = '') 
	{
		$this->value = $value;
	}

	/**
	 * [[Description]]
	 * @return string [[Description]]
	 */
	public function validate(): string 
	{
        if (strlen(trim($this->value)) === 0) {
            return  false;
        }
		return true;
	}

}
