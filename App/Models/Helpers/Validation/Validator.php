<?php
/**
 * Email Validation Implementation Model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers\Validation;

class Validator 
{

	public static function validate(array $request, array $rules, array $errors) 
    {

		$returnErrors = [];

		foreach ($request as $key => $value) {
			if (array_key_exists($key, $rules)) {

                $rulesArr = explode('|', $rules[$key]);

                foreach ($rulesArr as $rule) {
                    if (false !== strpos($rule, ':')) {
                        $ruleNameValPair = explode(':', $rule);
                        $className = $ruleNameValPair[0];
                        if (array_key_exists($ruleNameValPair[1], $request)) {
                            $ruleValue = $request[$ruleNameValPair[1]];
                        } else {
                            $ruleValue = $ruleNameValPair[1];
                        }
                    } else {
                        $className = $rule;
                        $ruleValue = '';
                    }

                    $model = self::getNamespace() . ucfirst($className);

                    if (!empty($model) && class_exists($model)) {
                        $success = (new ValidationStrategy(
                            new $model($value, $ruleValue)
                        ))->validate();

                        if (!$success) {
                            $returnErrors[] = !empty($errors[$key.".".$className]) ? $errors[$key.".".$className] : '';
                        }
                    }
                }
			}
		}

		return $returnErrors;

	}


	protected static function getNamespace() 
    {
        $namespace = 'App\Models\Helpers\Validation\\';

        return $namespace;
    }

}
