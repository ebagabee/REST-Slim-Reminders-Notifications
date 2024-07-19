<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ZAPIService
{
    private $apiKey;
    private $client;
    private $instanceId;
    private $token;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->apiKey = $config['zapi_client_token'];
        $this->instanceId = $config['zapi_instance_id'];
        $this->token = $config['zapi_token'];
        $this->client = new Client([
            'verify' => false 
        ]);
    }

    public function sendReminder($message, $phoneNumber)
    {
        $url = 'https://api.z-api.io/instances/' . $this->instanceId . '/token/' . $this->token . '/send-text';

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Client-Token' => $this->apiKey,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'phone' => $phoneNumber,
                    'message' => $message
                ]
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }
}
