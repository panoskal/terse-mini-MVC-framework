<?php
/**
 * Same Validation Implementation Model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Validation;

class Same implements ValidationInterface 
{

	protected $value, $rule;

	public function __construct(string $value, string $rule = '') 
	{
		$this->value = trim($value);
		$this->rule = trim($rule);
	}


	public function validate(): string 
	{
        if ( $this->value !==  $this->rule ) {
            return false;
        }
		return true;
	}

}
