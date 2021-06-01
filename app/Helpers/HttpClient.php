<?php


namespace App\Helpers;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    private $client;

    public function __construct()
    {
        $this->client = new Client(
            [
                "base_uri" => env('FACETEC_API') . '/',
            ]
        );
    }

    /**
     * @param       $uri
     * @param array $params
     * @param array $headers
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get($uri, array $params = [], array $headers = []): ResponseInterface
    {
        return $this->client->get(
            $uri, [
            "query"   => $params,
            "headers" => $headers
        ]
        );
    }

    /**
     * @param       $uri
     * @param array $params
     * @param array $headers
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post($uri, array $params = [], array $headers = []): ResponseInterface
    {
        return $this->client->post(
            $uri, [
                    "json"    => $params,
                    "headers" => $headers,
                ]
        );
    }
}