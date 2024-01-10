<?php

namespace Studio\Bridge\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Studio\Bridge\Usecases\FinalizeTypebotFlow;
use Studio\Bridge\Usecases\GenerateChatwootMessage;
use Studio\Bridge\Usecases\ParseChatwootResponse;
use Studio\Bridge\Usecases\ParseQueryParams;
use Studio\Bridge\Usecases\SendMessageToChatwoot;
use Studio\Bridge\Usecases\SendMessageToTypebot;
use Studio\Bridge\Usecases\StoreTypebotSession;

final class WebhookController
{
    public function index(Request $request, Response $response): Response
    {
        $query = ParseQueryParams::execute($request->getUri()->getQuery());
        if (! $query) {
            return $response->withStatus(204);
        }

        $chatwoot_provided_data = ParseChatwootResponse::execute($request->getBody()->read(5000));
        if (! $chatwoot_provided_data) {
            return $response->withStatus(204);
        }

        $typebot_response = SendMessageToTypebot::execute($chatwoot_provided_data, $query);
        if (! $typebot_response) {
            FinalizeTypebotFlow::execute($query, $chatwoot_provided_data);

            return $response->withStatus(204);
        }

        if (! empty($typebot_response->sessionId)) {
            StoreTypebotSession::execute($typebot_response->sessionId, $query, $chatwoot_provided_data);
        }

        $message = GenerateChatwootMessage::execute($typebot_response);

        SendMessageToChatwoot::execute($message, $chatwoot_provided_data, $query);

        return $response->withStatus(204);
    }
}
