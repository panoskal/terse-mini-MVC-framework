<?php

/**
 * Recaptcha model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers;

class Recaptcha
{

    private $apiClient;

    public function __construct() {
        $this->apiClient = new ApiClient();
    }

    public function verifyRecaptcha3($recaptchaResponse, $redirectStep=false)
    {
        $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptchaData = array('secret'=>RECAPTCHA_SECRET_KEY, 'response'=>$recaptchaResponse);
        $response = $this->apiClient->apiCall($recaptchaUrl, 'get', $recaptchaData, [], []);

        // object to return
        $recaptchaResult = (object) [
            'data' => json_encode($recaptchaData),
            'result'=> false,
            'status' => false,
            'validscore'=> RECAPTCHA_PERCENT
        ];

        if (!empty($response) && $response->status == 200) {// got something
            $recaptcha = json_decode($response->result, true);
            if (!empty($recaptcha) && !empty($recaptcha->score) && ($recaptcha->score >= RECAPTCHA_PERCENT)) {
                $recaptchaResult->status = 200;
                $recaptchaResult->result = $recaptcha;
            } else {
                $recaptchaResult->status = 700;
                $recaptchaResult->result = $recaptcha;
            }
        } else {
            $recaptchaResult->status = 700;
            $recaptchaResult->result = $response->result;
        }

        return ($recaptchaResult);
    }
}
