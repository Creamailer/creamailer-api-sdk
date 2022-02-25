<?php

namespace Creamailer\Api;

class TransferApi
{
    protected $creamailer;

    public function __construct($creamailer)
    {
        $this->creamailer = $creamailer;
    }


    /**
     * Make Creamailer API call using curl
     *
     * @param $route
     * @param $action
     * @param $json
     * @param $timestamp
     * @param $format
     * @return bool|string
     */
    public function call($route, $action, $json = null, $timestamp = null, $format = 'application/json')
    {
        $timestamp = $this->_timestamp($timestamp);

        $signature = $this->signature($route, $json);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $route);

        switch ($action) {
            case "POST":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                break;
            case "GET":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'X-Access-Token:' . $this->accessToken,
                'X-Request-Signature:' . $signature,
                'X-Request-Timestamp:' . $timestamp,
                'Accept:' . $format,
                'Content-Type: application/json; charset=UTF-8'
            ));

        curl_setopt($ch, CURLOPT_USERAGENT, "Creamailer-PHP/1.0'");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
    }


    /**
     * Create security signature
     *
     * @param $route
     * @param $json
     * @param $timestamp
     * @return string
     */
    public function signature($route, $json, $timestamp = null)
    {
        $timestamp = $this->_timestamp($timestamp);

        return sha1($this->apiUrl . $route . $json . $timestamp . $this->sharedSecret);
    }


    /**
     * Test connection
     *
     * @return mixed
     */
    public function ping()
    {
        $result = $this->call(
            'connection_test.json',
            'GET'
        );

        return json_decode($result);
    }


    /**
     * Timestamp for API call
     *
     * @param $timestamp
     * @return int|mixed
     */
    private function _timestamp($timestamp)
    {
        return (isset($timestamp)) ? $timestamp : time();
    }
}