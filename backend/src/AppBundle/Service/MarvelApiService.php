<?php

namespace AppBundle\Service;

use GuzzleHttp\Client as HttpClient;

class MarvelApiService
{
    private $apiKey;
    private $client;

    public function __construct($apiKey)
    {
        $this->client = new HttpClient([
            'base_uri' => 'https://gateway.marvel.com:443/v1/public/',
            'query' => [
                'apikey' => $apiKey
            ],
            'headers' => [
                'Referer' => 'https://developer.marvel.com/docs'
            ]
        ]);

    }

    public function getCharacters(int $offset = 0, int $limit = 20)
    {
        try {
            $response = $this->client->get('characters', [
                'query' => array_merge([
                    'offset' => $offset,
                    'limit' => $limit,
                ], $this->client->getConfig('query'))
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Invalid response received from Marvel");
        }

        try {
            $result = json_decode($response->getBody()->getContents())->data->results;
        } catch (\Exception $e) {
            throw new \Exception("Could not decode response from Marvel");
        }

        return $result;
    }
}