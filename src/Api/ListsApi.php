<?php

namespace Creamailer\Api;

class ListsApi
{
    protected $creamailer;

    public function __construct($creamailer)
    {
        $this->creamailer = $creamailer;
    }


    /**
     * Create mailing list
     *
     * @param $name
     * @param $lang
     * @param $autoSuppress
     * @return mixed
     */
    public function create($name, $lang = 'fi', $autoSuppress = true)
    {
        $query = [
            "name"          => $name,
            "language"      => $lang,
            "auto_suppress" => $autoSuppress
        ];

        $result = $this->creamailer->call(
            'lists.json',
            'POST',
            json_encode($query)
        );

        return json_decode($result);
    }


    /**
     * Get mailing list info by ID
     *
     * @param $listId
     * @return mixed
     */
    public function show($listId)
    {
        $result = $this->creamailer->call(
            'lists/' . $listId . '.json',
            'GET'
        );

        return json_decode($result);
    }


    /**
     * Get all mailing lists
     *
     * @return mixed
     */
    public function showMany()
    {
        $result = $this->creamailer->call(
            'lists.json',
            'GET'
        );

        return json_decode($result);
    }


    /**
     * Get mailing list subscribers
     *
     * @param $listId
     * @param $status
     * @param $page
     * @param $pageSize
     * @param $newerThan
     * @return mixed
     */
    public function subscribers($listId, $status = 'all', $page = 0, $pageSize = 50000, $newerThan = '2010-12-24')
    {

        $result = $this->creamailer->call(
            'lists/subscribers/' . $listId . '.json?status=' . $status . '&page=' . $page . '&pagesize=' . $pageSize . '&date=' . $newerThan,
            'GET'
        );

        return json_decode($result, true);
    }


    /**
     * Update mailing list
     *
     * @param $listId
     * @param $name
     * @param $lang
     * @param $autoSuppress
     * @return mixed
     */
    public function update($listId, $name, $lang = 'fi', $autoSuppress = true)
    {
        $query = [
            "name"          => $name,
            "language"      => $lang,
            "auto_suppress" => $autoSuppress
        ];

        $result = $this->creamailer->call(
            'lists/' . $listId . '.json',
            'PUT',
            json_encode($query)
        );

        return json_decode($result);
    }


    /**
     * Delete mailing list
     *
     * @param $listId
     * @return mixed
     */
    public function delete($listId)
    {
        $result = $this->creamailer->call(
            'lists/' . $listId . '.json',
            'DELETE'
        );

        return json_decode($result);
    }

}