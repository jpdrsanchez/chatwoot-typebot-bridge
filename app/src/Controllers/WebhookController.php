<?php

namespace Studio\Bridge\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class WebhookController
{
    public function index(Request $request, Response $response)
    {
        $file = fopen(__DIR__ . '/../../logs/logs.txt', 'a+');
        $json = $request->getBody();
        if ($file) {
            fputs($file, $json);
            fclose($file);
        }

        $response->getBody()->write('Webhook');

        return $response;
    }
}
