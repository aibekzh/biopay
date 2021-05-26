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
    public function bindBaseRate($user_id)
    {
        return json_decode($this->client->post("api/v1/user/rate/reset", [
            "user_id" => $user_id
        ])->getBody()->getContents());
    }
}
