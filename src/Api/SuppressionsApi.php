<?php

namespace Creamailer\Api;

class SuppressionsApi
{
    protected $creamailer;

    public function __construct($creamailer)
    {
        $this->creamailer = $creamailer;
    }


    /**
     * Add subscriber to suppression list
     *
     * @param $email
     * @return mixed
     */
    public function create($email)
    {
        $query = [
            'email' => $email
        ];

        $result = $this->creamailer->call(
            'suppressions.json',
            'POST',
            json_encode($query)
        );

        return json_decode($result);
    }


    /**
     * Get subscriber from suppression list
     *
     * @return mixed
     */
    public function show()
    {
        $result = $this->creamailer->call(
            'suppressions.json',
            'GET'
        );

        return json_decode($result, true); // True on json_decode fixes bug in api v.1 and makes object(stdClass) to array

    }


    /**
     * Remove subscriber from suppression list
     *
     * @param $email
     * @return mixed
     */
    public function delete($email)
    {

        $result = $this->creamailer->call(
            'suppressions.json?email=' . $email,
            'DELETE'
        );

        return json_decode($result);
    }

   
}