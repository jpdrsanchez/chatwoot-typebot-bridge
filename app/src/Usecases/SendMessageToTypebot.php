<?php

namespace Studio\Bridge\Usecases;

use stdClass;
use Studio\Bridge\Support\Logger;

class SendMessageToTypebot
{
    /**
     * Sends the provided chatwoot message to typebot and returns the bot message or false if something is wrong.
     *
     * @param stdClass $conversation
     * @param stdClass $query
     *
     * @return false|stdClass
     */
    public static function execute(stdClass $conversation, stdClass $query): false|stdClass
    {
        $additionalBodyParams = [];

        if (! empty($conversation->sender?->custom_attributes?->typebot_token)) {
            $additionalBodyParams['message']   = $conversation->content;
            $additionalBodyParams['sessionId'] = $conversation->sender->custom_attributes->typebot_token;
        }

        $body = json_encode([
            ...$additionalBodyParams,
            'startParams' => [
                'typebot' => $query->typebot_chat_id,
            ]
        ]);
        Logger::log("Body to be sent to typebot: " . $body);

        $options = [
            'http' => [
                'method'  => 'POST',
                'content' => $body,
                'header'  => 'Content-Type: application/json'
            ]
        ];
        $context = stream_context_create($options);

        $response = file_get_contents($query->typebot_api_url, false, $context);
        if (empty($response)) {
            Logger::log("Invalid typebot response $response");

            return false;
        }

        Logger::log("Response recieved from typebot: $response");

        $decoded_response = json_decode($response);

        if (empty($decoded_response->messages)) {
            Logger::log("Invalid typebot response");

            return false;
        }

        return $decoded_response;
    }
}
