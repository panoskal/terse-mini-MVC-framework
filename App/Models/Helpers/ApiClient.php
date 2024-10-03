<?php

/**
 * ApiClient model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers;

use Core\Config;

class ApiClient
{

    /**
     * [apiCall description]
     * @param  string  $pathtocall  [description]
     * @param  string  $type        [description]
     * @param  string  $data        [description]
     * @param  array   $credentials [description]
     * @param  array   $headers     [description]
     * @param  boolean $useproxy    [description]
     * @return [type]               [description]
     */
    public function apiCall(string $pathtocall, string $type='get', $data='', array $credentials=[], iterable $headers=[], bool $useproxy=false)
    {
        $curlheaders = array();
        $urltocall = str_replace(' ', '%20', $pathtocall);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($useproxy && !empty($_ENV['FW_PROXY'])) {
            curl_setopt($curl, CURLOPT_PROXY, $_ENV['FW_PROXY']);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        }

        if ($headers) {
            foreach ($headers as $headerKey=>$headerVal) {
                $curlheaders []=$headerKey.': '.$headerVal;
            }
        }

        if ($type=='get') {
            $data=http_build_query($data);
            $urltocall.='?'.$data;
            curl_setopt($curl, CURLOPT_HTTPGET, true);
        }

        if ($type=='post') {
            $data=http_build_query($data);
            $curlheaders []='Content-Type: application/x-www-form-urlencoded;charset=UTF-8';
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        if ($type=='json') {
            $data = json_encode($data, JSON_UNESCAPED_SLASHES);
            $curlheaders []= 'Content-Type: application/json';
            $curlheaders []= 'Content-Length: '.strlen($data);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        if (!empty($credentials)) {
            curl_setopt($curl, CURLOPT_USERPWD, $credentials['user'].':'.$credentials['pass']);
        }

        if (Config::SHOW_ERRORS === false) {
            $timezone = 'Europe/Athens';
            date_default_timezone_set($timezone);
            $date=date("Y-m-d, H:i:s").", ".$timezone;
            $fp = fopen($_SERVER['DOCUMENT_ROOT'].'/_jobs/logs/curl_raw.lg', 'a');
            fwrite($fp, PHP_EOL.PHP_EOL.PHP_EOL.$date.PHP_EOL.PHP_EOL);
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_STDERR, $fp);
        }

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $urltocall);

        if (count($curlheaders)>0) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $curlheaders);
        }

        $urlresponse = curl_exec($curl);
        //object to return
        $curlResult = (object) [
            'data' => $data,
            'urlCalled' => $urltocall,
            'result'=> false,
            'status'=> false
         ];


        if (curl_errno($curl)) {//error on the curl function
            $curlResult->status=700;
            $curlResult->result=curl_error($curl);
        } else {
            $curlResult->status=200;
            $curlResult->result=$urlresponse;
        }
        curl_close($curl);
        return($curlResult);
    }
}
