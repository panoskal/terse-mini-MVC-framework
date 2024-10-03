<?php
/**
 * Sanitization Interface Model (strategy Design Pattern)
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Sanitization;

interface SanitizationInterface {

	public function __construct(string $input);

	public function sanitize();

}
