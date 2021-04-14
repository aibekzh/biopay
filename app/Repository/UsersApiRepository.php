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
     * @param $user_id
     * @return mixed
     */
    public function bindBaseRate($user_id)
    {
        return json_decode($this->client->post("api/v1/users/$user_id/rate", [
            "rate_id" => 379
        ])->getBody()->getContents());
    }
}
