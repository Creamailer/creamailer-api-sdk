<?php

namespace Creamailer\Api;

class SubscribersApi
{
    protected $creamailer;

    public function __construct($creamailer)
    {
        $this->creamailer = $creamailer;
    }


    /**
     * Create subscriber
     *
     * @param $listId
     * @param $options
     * @return mixed
     */
    public function create($listId, $options = array())
    {
        $query = $this->_setupSubscriber($options);

        $result = $this->creamailer->call(
            'subscribers/' . $listId . '.json',
            'POST',
            json_encode($query)
        );

        return json_decode($result);
    }


    /**
     * Create many subscribers
     *
     * @param $listId
     * @param $options
     * @return mixed
     */
    public function createMany($listId, $options = array())
    {
        foreach($options['Subscribers'] as &$subscriber)
        {
            $subscriber = $this->_setupSubscriber($subscriber);
        }

        $result = $this->creamailer->call(
            'subscribers/import/' . $listId . '.json',
            'POST',
            json_encode($options)
        );

        return json_decode($result);

    }


    /**
     * Get subscriber from mailing list
     *
     * @param $listId
     * @param $email
     * @return mixed
     */
    public function show($listId, $email)
    {
        $result = $this->creamailer->call(
            'subscribers/' . $listId . '.json?email=' . $email,
            'GET'
        );

        return json_decode($result);
    }


    /**
     * Get all subscribers from mailing list
     *
     * @param $listId
     * @param $status
     * @return mixed
     */
    public function allSubscribers($listId, $status = 'all')
    {
        $result = $this->creamailer->call(
            'lists/subscribers/' . $listId . '.json?status=' . $status,
            'GET'
        );

        return json_decode($result);
    }


    /**
     * Update subscriber
     *
     * @param $listId
     * @param $options
     * @return mixed
     */
    public function update($listId, $options = array())
    {
        $query = [];
        if (isset($options['email'])) $query['email'] = $options['email'];
        if (isset($options['name'])) $query['name'] = $options['name'];
        if (isset($options['company'])) $query['company'] = $options['company'];
        if (isset($options['address'])) $query['address'] = $options['address'];
        if (isset($options['city'])) $query['city'] = $options['city'];
        if (isset($options['zip_code'])) $query['zip_code'] = $options['zip_code'];
        if (isset($options['country'])) $query['country'] = $options['country'];
        if (isset($options['phone'])) $query['phone'] = $options['phone'];
        if (isset($options['customer_number'])) $query['customer_number'] = $options['customer_number'];
        if (isset($options['custom_fiezlds'])) $query['custom_fields'] = $options['custom_fields'];
        if (isset($options['status'])) $query['status'] = $options['status'];

        $result = $this->creamailer->call(
            'subscribers/' . $listId . '.json',
            'PUT',
            json_encode($query)
        );

        return json_decode($result);
    }


    /**
     * Remove subscriber
     *
     * @param $listId
     * @param $email
     * @return mixed
     */
    public function delete($listId, $email)
    {
        $result = $this->creamailer->call(
            'subscribers/' . $listId . '.json?email=' . $email,
            'DELETE'
        );

        return json_decode($result);
    }


    /**
     * Setup subscriber
     *
     * @param $options
     * @return array
     */
    private function _setupSubscriber($options)
    {
        $user = [];

        if (isset($options['email'])) $user['email'] = $options['email'];
        if (isset($options['name'])) $user['name'] = $options['name'];
        if (isset($options['company'])) $user['company'] = $options['company'];
        if (isset($options['address'])) $user['address'] = $options['address'];
        if (isset($options['city'])) $user['city'] = $options['city'];
        if (isset($options['zip_code'])) $user['zip_code'] = $options['zip_code'];
        if (isset($options['country'])) $user['country'] = $options['country'];
        if (isset($options['phone'])) $user['phone'] = $options['phone'];
        if (isset($options['customer_number'])) $user['customer_number'] = $options['customer_number'];
        if (isset($options['custom_fields'])) $user['custom_fields'] = $options['custom_fields'];
        if (isset($options['status'])) $user['status'] = $options['status'];
        if (isset($options['update_existing'])) $user['update_existing'] = $options['update_existing'];
        if (isset($options['send_autoresponders'])) $user['send_autoresponders'] = $options['send_autoresponders'];
        if (isset($options['send_autoresponders_if_exists'])) $user['send_autoresponders_if_exists'] = $options['send_autoresponders_if_exists'];

        return $user;
    }

}