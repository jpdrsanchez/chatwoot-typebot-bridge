<?php

namespace Studio\Bridge\Usecases;

use stdClass;
use Studio\Bridge\Support\Logger;

class SendMessageToChatwoot
{
    /**
     * Sends the bot formatted message to chatwoot.
     *
     * @param string $message
     * @param stdClass $chatwoot
     * @param stdClass $query
     *
     * @return void
     */
    public static function execute(string $message, stdClass $chatwoot, stdClass $query): void
    {
        Logger::log("Message to be sent to chatwoot: $message");

        $chatwoot_url    = $query->chatwoot_url;
        $account_id      = $chatwoot->account?->id;
        $conversation_id = $chatwoot->conversation?->id;
        $bot_token       = $query->chatwoot_bot_token;

        $url     =
            "$chatwoot_url/api/v1/accounts/$account_id/conversations/$conversation_id/messages";
        $body    = json_encode([
            'content'      => $message,
            'message_type' => 'outgoing',
            'private'      => true
        ]);
        $headers =
            "Content-Type: application/json\r\nAccept: application/json\r\napi_access_token: $bot_token";
        $options = [
            'http' => [
                'method'  => 'POST',
                'content' => json_encode($body),
                'header'  => $headers,
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        Logger::log("Message sent to Chatwoot");
        Logger::log("Response: $response");
    }
}
