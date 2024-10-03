<?php

/**
 * Test model
 *
 * PHP version 7.4
 */

namespace App\Models;

use \App\Models\Helpers\MySQL;
use \App\Models\Helpers\Validation\Validator as Validator;
use \App\Models\Helpers\Password as Password;
use \App\Models\Helpers\Recaptcha;

class Test
{
    private $dbConn;

    public function __construct()
    {
        $this->dbConn = new MySQL();
    }


    public function testValidation()
    {

        $request = [
            'name'              => '',
            'email'             => 'kalogirou.pa@gmail.com',
            'password'          => '1234qwer',
            'confirm_password'  => '1234qwer',
            'phone'             => '6911111111'
        ];

        Validator::validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:10'
        ],[
            'name.required' => 'The name field is required',
            'email.required' => 'The email field is required',
            'email.email' => 'The email field must be valid email',
            'password.min' => 'The password must be at least 10 characters'
        ]);


    //    Validator::validate($request,[
    //        'firstname' => 'required|min:5|max:35',
    //        'lastname' => 'required|min:5|max:35',
    //        'email' => 'required|email|unique:users',
    //        'mobileno' => 'required|numeric',
    //        'password' => 'required|min:3|max:20',
    //        'confirm_password' => 'required|min:3|max:20|same:password',
    //        'details' => 'required'
    //    ],[
    //        'firstname.required' => ' The first name field is required.',
    //        'firstname.min' => ' The first name must be at least 5 characters.',
    //        'firstname.max' => ' The first name may not be greater than 35 characters.',
    //        'lastname.required' => ' The last name field is required.',
    //        'lastname.min' => ' The last name must be at least 5 characters.',
    //        'lastname.max' => ' The last name may not be greater than 35 characters.',
    //    ]);

    }

}
