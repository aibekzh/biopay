<?php


namespace App\Repository;


use App\Helpers\HttpClient;

class UsersApiRepository
{
    private $client;

    public function __construct()
    {
        $this->client = new HttpClient(env('MODULE_USERS_HOST'), env('MODULE_USERS_PORT'));
    }

    /**
     * @return mixed
     */
    public function bindBaseRate()
    {
        return json_decode($this->client->post("api/v1/user/rate", [
            "rate_id" => 1
        ])->getBody()->getContents());
    }
}
