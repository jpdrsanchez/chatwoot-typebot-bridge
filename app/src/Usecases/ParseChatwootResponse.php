<?php

namespace Studio\Bridge\Usecases;

use stdClass;
use Studio\Bridge\Support\Logger;

class ParseChatwootResponse
{
    /**
     * Get the current provided chatwoot message and turns it into a stdClass or returns false if something is wrong.
     *
     * @param string $body
     *
     * @return false|stdClass
     */
    public static function execute(string $body): false|stdClass
    {
        $data = json_decode($body);
        if (! is_array($data) && ! $data instanceof stdClass) {
            Logger::log('Invalid JSON');

            return false;
        }

        $message_type = $data->message_type;
        if ($message_type !== 'incoming') {
            Logger::log('Invalid message type');

            return false;
        }

        $message_event_type = $data->event;
        if ($message_event_type !== 'message_created') {
            Logger::log('Invalid message event');

            return false;
        }

        Logger::log("Message type: $message_type");
        Logger::log("Message body: $body");
        Logger::log("Message content: $data->content");
        Logger::log("Conversation ID: {$data->conversation?->id}");
        Logger::log("Account ID: {$data->account?->id}");
        Logger::log("Conversation status: {$data->sender?->custom_attributes?->statusbot}");

        if ($data->sender?->custom_attributes?->statusbot === 'atendido') {
            Logger::log("Conversation already finalized");

            return false;
        }

        return $data;
    }
}
