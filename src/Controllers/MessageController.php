<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\ZAPIService;

class MessageController
{
    private $zapiService;

    public function __construct()
    {
        $this->zapiService = new ZAPIService();
    }

    public function sendMessage(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $phoneNumber = $data['number'];
        $message = $data['message'];

        if (!$phoneNumber || !$message) {
            $response->getBody()->write(json_encode(['error' => 'Phone number and message are required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $result = $this->zapiService->sendReminder($message, $phoneNumber);

        $response->getBody()->write(json_encode(['result' => $result]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
