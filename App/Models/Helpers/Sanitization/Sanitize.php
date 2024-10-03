<?php
/**
 * Email Validation Implementation Model (strategy Design Pattern)
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Sanitization;

class Sanitize {

	public static function sanitize($type, $input) {

		if (empty($type)) {
			throw new \Exception('Value type not defined for sanitization process');
		}

		if (empty($input)) {
			throw new \Exception('Input value not defined for sanitization process');
		}

		$model = self::getNamespace() . str_replace(' ', '', ucwords(str_replace('-', ' ', $type)));

		if (class_exists($model)) {
			$output = (new SanitizationStrategy(new $model($input)))->sanitize();
		}

		return $output;

	}


	protected static function getNamespace() {
        $namespace = 'App\Models\Helpers\Sanitization\\';

        return $namespace;
    }

}
