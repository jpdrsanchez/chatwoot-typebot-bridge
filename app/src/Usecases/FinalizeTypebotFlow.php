<?php

namespace Studio\Bridge\Usecases;

use stdClass;
use Studio\Bridge\Support\Logger;

class FinalizeTypebotFlow
{
    public static function execute(string $token, stdClass $query, stdClass $message): void
    {
        $body = json_encode([
            'custom_attributes' => [
                'statusbot' => 'atendido'
            ]
        ]);
        Logger::log("Body to be sent to chatwoot for finalize flow: " . $body);

        $options = [
            'http' => [
                'method'  => 'PUT',
                'content' => $body,
                'header'  =>
                    "Content-Type: application/json\r\nAccept: application/json\r\napi_access_token: $token"
            ]
        ];
        $context = stream_context_create($options);

        $url =
            "$query->chatwoot_url/api/v1/accounts/{$message->account?->id}/contacts/{$message->sender?->id}";

        $response =
            file_get_contents(
                $url,
                false,
                $context
            );
        if (empty($response)) {
            Logger::log("Could not finalize typebot flow: $response");

            return;
        }

        Logger::log("Finalized typebot flow: $response");
    }
}
