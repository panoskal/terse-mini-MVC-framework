<?php
/**
 * Validation Interface Model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Validation;

interface ValidationInterface 
{

	public function __construct(string $value, string $rule = '');

	public function validate(): string;

}
