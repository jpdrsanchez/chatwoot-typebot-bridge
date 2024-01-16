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
     * @param stdClass $query
     *
     * @return false|stdClass
     */
    public static function execute(string $body, stdClass $query): false|stdClass
    {
        Logger::log("Message body before validation: $body");

        $data = json_decode($body);
        if (! is_array($data) && ! $data instanceof stdClass) {
            Logger::log('Invalid JSON');

            return false;
        }

        UpdateChatwootConversationStatus::execute($query, $data);

        $message_type = $data->message_type;
        if ($message_type !== 'incoming') {
            Logger::log('Message is not incoming');
            Logger::log("Message Type: $message_type");
            Logger::log("Message is: $data->private");

            if ($message_type === 'outgoing' && $data->private === 'false') {
                FinalizeTypebotFlow::execute($query, $data);
            }
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

            FinalizeTypebotFlow::execute($query, $data);

            return false;
        }

        return $data;
    }
}
