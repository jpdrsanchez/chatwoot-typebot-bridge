<?php

namespace Studio\Bridge\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use stdClass;
use Studio\Bridge\Support\Logger;

final class WebhookController
{
    public function index(Request $request, Response $response): Response
    {
        $json = $request->getBody()->read(5000);
        $data = json_decode($json);

        if (! is_array($data) && ! $data instanceof stdClass) {
            Logger::log('Invalid JSON');

            return $response->withStatus(204);
        }

        $message_type = $data->message_type;
        if ($message_type !== 'incoming') {
            Logger::log('Invalid JSON');

            return $response->withStatus(204);
        }

        $message_event_type = $data->event;
        if ($message_event_type !== 'message_created') {
            Logger::log('Invalid Message Type');

            return $response->withStatus(204);
        }

        Logger::log('Message is incoming');
        Logger::log("Message type: $message_type");

        $message_content = $data->content;
        Logger::log("Message Content: " . $message_content);
        $conversation_id = $data->conversation->id;
        Logger::log("Conversation ID: $conversation_id");
        $account_id = $data->account->id;
        Logger::log("Account ID: $account_id");

        $typebot_response = $this->sendMessageToTypeBot($message_content);
        Logger::log("Message recieved from Typebot");
        Logger::log("Response: $typebot_response");

        $typebot_response = json_decode($typebot_response);

        $message =
            "{$typebot_response->messages[0]->content->richText[0]->children[0]->text}";

        $chatwoot_response = $this->sendToChatWoot($message, $account_id, $conversation_id);
        Logger::log("Message sent to Chatwoot");
        Logger::log("Response: $chatwoot_response");


        return $response->withStatus(204);
    }

    private function sendMessageToTypeBot(string $content)
    {
        $content = [
            'startParams' => [
                'typebot' => $_ENV['TYPEBOT_ID']
            ]
        ];

        $options = [
            'http' => [
                'method'  => 'POST',
                'content' => json_encode($content),
                'header'  => 'Content-Type: application/json'
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($_ENV['TYPEBOT_API_URL'], false, $context);

        return $response;
    }

    public function sendToChatWoot(string $message, string $account, string $conversation)
    {
        $url  = "{$_ENV['CHATWOOT_BASE_URL']}/api/v1/accounts/$account/conversations/$conversation/messages";
        $data = [
            'content'      => $message,
            'message_type' => 'outgoing',
            'private'      => true
        ];

        $options = [
            'http' => [
                'method'  => 'POST',
                'content' => json_encode($data),
                'header'  =>
                    "Content-Type: application/json\r\nAccept: application/json\r\napi_access_token: {$_ENV['CHATWOOT_BOT_TOKEN']}"
            ]
        ];

        $context = stream_context_create($options);
        $result  = file_get_contents($url, false, $context);

        return $result;
    }
}
