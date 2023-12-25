<?php

namespace Studio\Bridge\Support;

use stdClass;

class InputTextReader
{
    private function __construct()
    {
    }

    /**
     * Transforms the typebot choices input into a text message.
     *
     * @param stdClass[] $items
     *
     * @return string
     */
    public static function parseInputForChatwoot(array $items): string
    {
        $finalMessage = "";

        foreach ($items as $value) {
            if (! empty($value->content)) {
                $finalMessage .= "{$value->content}" . PHP_EOL;
            }
        }

        return $finalMessage;
    }
}
