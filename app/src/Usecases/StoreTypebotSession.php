<?php

namespace Studio\Bridge\Usecases;

use stdClass;
use Studio\Bridge\Support\Logger;

class StoreTypebotSession
{
    /**
     * Tries to store the typebot conversation token in the chatwoot contact custom attributes.
     *
     * @param string $session_id
     * @param stdClass $query
     * @param stdClass $message
     *
     * @return void
     */
    public static function execute(string $session_id, stdClass $query, stdClass $message): void
    {
        $body = json_encode([
            'custom_attributes' => [
                'typebot_session' => $session_id,
            ]
        ]);
        Logger::log("Body to be sent to chatwoot for update custom attributes: " . $body);

        $options = [
            'http' => [
                'method'  => 'PUT',
                'content' => $body,
                'header'  =>
                    "application/json\r\nAccept: application/json\r\napi_access_token: {$query->chatwoot_token}"
            ]
        ];
        $context = stream_context_create($options);

        $url      =
            "$query->chatwoot_url/api/v1/accounts/{$message->account?->id}/contacts/{$message->sender?->id}";
        $response =
            file_get_contents(
                $url,
                false,
                $context
            );
        if (empty($response)) {
            Logger::log("Could not store the typebot session. Response: $response");

            return;
        }

        Logger::log("Response recieved from chatwoot: $response");
    }
}
