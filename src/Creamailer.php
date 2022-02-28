<?php

namespace Creamailer;

use Creamailer\Api\TransferApi;
use Creamailer\Api\ListsApi;
use Creamailer\Api\SubscribersApi;
use Creamailer\Api\SuppressionsApi;

class Creamailer extends TransferApi
{
    protected $accessToken, $sharedSecret, $apiUrl;

    public function __construct($accessToken, $sharedSecret, $apiUrl = 'https://api.cmfile.net/v1/api/')
    {
        $this->sharedSecret = $sharedSecret;
        $this->accessToken = $accessToken;
        $this->apiUrl = $apiUrl;
    }


    /**
     * @return ListsApi
     */
    public function lists()
    {
        return new ListsApi($this);
    }


    /**
     * @return SubscribersApi
     */
    public function subscribers()
    {
        return new SubscribersApi($this);
    }


    /**
     * @return SuppressionsApi
     */
    public function suppressions()
    {
        return new SuppressionsApi($this);
    }

}