<?php


namespace App\Helpers;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    private $client;

    public function __construct($host, $port)
    {
        $this->client = new Client(
            [
                "base_uri" => env('MODULE_USERS_SCHEME').'://'.$host.':'.$port ?? ''.'/'
            ]
        );
    }


    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return ResponseInterface
     */
    public function get($uri, $params = [], $headers = []): ResponseInterface
    {
        return $this->client->post($uri, [
            "json"      => $params,
            "headers"   => $headers
        ]);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return ResponseInterface
     */
    public function delete($uri, $params = [], $headers = []): ResponseInterface
    {
        return $this->client->put($uri, [
            "json"      => $params,
            "headers"   => $headers
        ]);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return ResponseInterface
     */
    public function post($uri, $params = [], $headers = []): ResponseInterface
    {
        return $this->client->post($uri, [
            "json"      => $params,
            "headers"   => $headers
        ]);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return ResponseInterface
     */
    public function put($uri, $params = [], $headers = []): ResponseInterface
    {
        return $this->client->put($uri, [
            "json"      => $params,
            "headers"   => $headers
        ]);
    }
}
