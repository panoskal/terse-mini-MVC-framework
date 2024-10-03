<?php

namespace App\Models\Helpers;

class InputProcess {

    public static $output;

    public static function cleanString($input) {
        $string = trim($input);
        $string = stripslashes($string);
        $string = strip_tags($string);
        return $string;
    }

	public static function processEmail($input) {
        $input = trim($input);
        self::$output = filter_var($input, FILTER_SANITIZE_EMAIL);
		return self::$output;
	}

    public static function processPass($input) {
        $input = trim($input);
        self::$output = password_hash( $input, PASSWORD_BCRYPT, array('cost' => 12));
        return self::$output;
    }

    public static function processString($input) {
        $input = trim($input);
        self::$output = filter_var($input, FILTER_SANITIZE_STRING);
        return self::$output;
    }

    public static function processInt($input) {
		$input = trim($input);
		self::$output = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
		return self::$output;
    }

    public static function processFloat($input) {
        $input = trim($input);
        self::$output = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		return self::$output;
    }

    public static function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public static function generateRandom(bool $number = false, int $length = 10)
    {
        switch($number) {
            case true:
                $characters = '0123456789';
                break;
            default:
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}


    public static function ucWithoutAccents($string, $enc = "utf-8") {
        return strtr(mb_strtoupper($string, $enc),
          array('Ά' => 'Α', 'Έ' => 'Ε', 'Ί' => 'Ι', 'Ή' => 'Η', 'Ύ' => 'Υ',
            'Ό' => 'Ο', 'Ώ' => 'Ω', 'A' => 'A', 'A' => 'A', 'A' => 'A', 'A' => 'A',
            'Y' => 'Y','ΐ' => 'Ϊ'
          ));
    }

}
