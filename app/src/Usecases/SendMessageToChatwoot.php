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
        $chatwoot_url    = $query->chatwoot_url;
        $account_id      = $chatwoot->account?->id;
        $conversation_id = $chatwoot->conversation?->id;

        $url     =
            "$chatwoot_url/api/v1/accounts/$account_id/conversations/$conversation_id/messages";
        $body    = [
            'content'      => $message,
            'message_type' => 'outgoing',
            'private'      => true
        ];
        $headers = "application/json\r\nAccept: application/json\r\napi_access_token: $chatwoot->chatwoot_bot_token";
        $options = [
            'http' => [
                'method'  => 'POST',
                'content' => json_encode($body),
                'header'  =>
                    "Content-Type: $headers"
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        Logger::log("Message sent to Chatwoot");
        Logger::log("Response: $response");
    }
}