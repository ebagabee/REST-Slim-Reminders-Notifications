<?php

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;

class JsonHelper
{
    public static function jsonResponse(Response $response, $data, $status = 200)
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}