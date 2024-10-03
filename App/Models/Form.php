<?php

/**
 * User model
 *
 * PHP version 7.4
 */

namespace App\Models;

use App\Models\Helpers\FormBuilder\FormBuilder;

class Form
{
    private $dbConn;

    public static function build(array $form)
    {
        $builder = new FormBuilder($form);
        return $builder;
    }
   
}
