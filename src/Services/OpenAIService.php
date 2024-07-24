<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class OpenAIService
{
    private $client;
    private $apiKey;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->apiKey = $config['openai_api_key'];
        $this->client = new Client([
            'verify' => false
        ]);
    }

    public function analyzeMessage($message)
    {
        $currentDate = Carbon::now('America/Sao_Paulo')->format('Y-m-d H:i:s');
        $prompt = "A data atual é $currentDate. Por favor, extraia o evento e a data/hora da seguinte mensagem: '$message'. Retorne o resultado no formato 'evento;YYYY-MM-DD HH:MM:SS'. Se não encontrar uma data/hora, use a data e hora atuais.";

        $url = 'https://api.openai.com/v1/chat/completions';

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 150
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            $content = $body['choices'][0]['message']['content'] ?? null;

            if ($content) {
                $parts = explode(';', $content);
                $event = isset($parts[0]) ? trim($parts[0]) : null;
                $dateTime = isset($parts[1]) ? trim($parts[1]) : null;
                return [$event, $dateTime];
            }

            return [null, null];
        } catch (RequestException $e) {
            error_log('Error in analyzeMessage: ' . $e->getMessage());
            return [null, null];
        }
    }


    public function generateMessage($event, $character)
    {
        $prompt = "Fale como $character: como o personagem falaria sobre'$event' para uma outra pessoa, como se fosse uma notificação. Use o humor característico de $character e fale diretamente com a pessoa. Exemplo: personagem bob esponja para o evento de acordar: Acorde *risadas* e não se esqueça de dar comida para o Gary";

        $url = 'https://api.openai.com/v1/chat/completions';

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 150
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            return $body['choices'][0]['message']['content'] ?? "$character: Não consigo criar uma mensagem agora.";
        } catch (RequestException $e) {
            return "$character: Ocorreu um erro ao gerar a mensagem.";
        }
    }
}
