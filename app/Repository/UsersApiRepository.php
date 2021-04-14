<?php


namespace App\Repository;


use App\Helpers\HttpClient;
use Psr\Http\Message\ResponseInterface;

class UsersApiRepository
{
    private $client;

    public function __construct()
    {
        $this->client = new HttpClient(env('MODULE_USERS_HOST'), null);
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function bindBaseRate($user_id)
    {
        return json_decode($this->client->post("api/v1/users/$user_id/rate", [
            "rate_id" => 1
        ])->getBody()->getContents());
    }
}
