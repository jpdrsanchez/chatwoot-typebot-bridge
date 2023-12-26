<?php

namespace Studio\Bridge\Usecases;

use stdClass;
use Studio\Bridge\Support\Logger;

class ParseQueryParams
{
    /**
     * Checks if the query is filled and returns it as an array if true.
     *
     * @param string $query
     *
     * @return false|stdClass
     */
    public static function execute(string $query): false|stdClass
    {
        $query = trim($query);
        if (empty($query)) {
            Logger::log("No query params provided");

            return false;
        }

        Logger::log("Query params: $query");

        parse_str($query, $output);

        if (
            empty($output['chatwoot_url']) ||
            empty($output['chatwoot_bot_token']) ||
            empty($output['chatwoot_token']) ||
            empty($output['typebot_api_url']) ||
            empty($output['typebot_chat_id'])
        ) {
            Logger::log("Required query params not provided");

            return false;
        }

        return (object) $output;
    }
}
