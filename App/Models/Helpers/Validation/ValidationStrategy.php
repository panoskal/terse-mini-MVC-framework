<?php
/**
 * Email Validation Implementation Model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Validation;


class ValidationStrategy 
{


	protected $validation;


	public function __construct(ValidationInterface $validation) 
	{
		$this->validation = $validation;
	}


	public function validate(): string 
	{
		return $this->validation->validate();
	}

}
