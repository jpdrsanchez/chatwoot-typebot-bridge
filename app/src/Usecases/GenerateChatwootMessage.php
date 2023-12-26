<?php

namespace Studio\Bridge\Usecases;

use stdClass;
use Studio\Bridge\Support\InputTextReader;
use Studio\Bridge\Support\Logger;
use Studio\Bridge\Support\RichTextReader;

class GenerateChatwootMessage
{
    /**
     * Prepares the final message to be sent to chatwoot.
     *
     * @param stdClass $typebot_response
     *
     * @return string
     */
    public static function execute(stdClass $typebot_response): string
    {
        $richTextMessage = "";
        $inputMessage    = "";

        if (! empty($typebot_response->messages)) {
            $richTextMessage = RichTextReader::parseToString($typebot_response->messages);
        }

        if (! empty($typebot_response->input?->items) && is_array($typebot_response->input?->items)) {
            $inputMessage = InputTextReader::parseInputForChatwoot($typebot_response->input->items);
        }

        Logger::log("Formatted message: $richTextMessage $inputMessage");
        Logger::log("Rich text and image messages: $richTextMessage");
        Logger::log("Rich text and image messages: $inputMessage");

        return "$richTextMessage $inputMessage";
    }
}
