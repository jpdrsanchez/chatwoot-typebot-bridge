<?php

namespace Studio\Bridge\Usecases;

use stdClass;
use Studio\Bridge\Support\Logger;

class UpdateChatwootConversationStatus
{
    /**
     * Changes a conversation status.
     *
     * @param stdClass $query
     * @param stdClass $chatwoot
     * @param string $status
     *
     * @return void
     */
    public static function execute(stdClass $query, stdClass $chatwoot, string $status = "open"): void
    {
        Logger::log('Started to update conversation status');

        if ($status === "open" && $chatwoot->conversation?->status !== 'pending') {
            Logger::log('Conversation is already not pending');

            return;
        }

        $chatwoot_url    = $query->chatwoot_url;
        $account_id      = $chatwoot->account?->id;
        $conversation_id = $chatwoot->conversation?->id;
        $bot_token       = $query->chatwoot_bot_token;

        $url     =
            "$chatwoot_url/api/v1/accounts/$account_id/conversations/$conversation_id/toggle_status";
        $body    = json_encode([
            "status" => $status,
        ]);
        $headers =
            "Content-Type: application/json\r\nAccept: application/json\r\napi_access_token: $bot_token";
        $options = [
            'http' => [
                'method'  => 'POST',
                'content' => $body,
                'header'  => $headers,
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        Logger::log("Conversation Status Updated");
        Logger::log("Response: $response");
    }
}
