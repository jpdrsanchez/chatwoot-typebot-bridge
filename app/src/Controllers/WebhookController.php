<?php

namespace Studio\Bridge\Controllers;

use PDO;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use stdClass;
use Studio\Bridge\Support\Logger;
use Studio\Bridge\Support\RichTextReader;

final class WebhookController
{
    public function __construct(private PDO $database)
    {
    }

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
            Logger::log('Invalid Message Type');

            return $response->withStatus(204);
        }

        $message_event_type = $data->event;
        if ($message_event_type !== 'message_created') {
            Logger::log('Invalid Message Event');

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

        $sanitized_conversation_id = $this->database->quote($conversation_id);
        $query                     = "SELECT * FROM `conversations` WHERE `conversation_id` = :conversation_id";
        $statement                 = $this->database->prepare($query);
        $statement->bindParam(':conversation_id', $sanitized_conversation_id);
        $statement->execute();

        $conversation = $statement->fetch(PDO::FETCH_ASSOC);

        if (! $conversation) {
            Logger::log("Conversation not found in database");
        } else {
            Logger::log("Conversation found in database id: {$conversation['id']}");
        }

        if (! empty($conversation['ended']) && $conversation['ended'] === 'true') {
            Logger::log("Conversation is finalized");

            return $response->withStatus(204);
        }

        $typebot_response = $this->sendMessageToTypeBot($message_content, $conversation);
        Logger::log("Message recieved from Typebot");
        Logger::log("Response: $typebot_response");

        $typebot_response = json_decode($typebot_response);

        if (empty($typebot_response->messages)) {
            Logger::log("Invalid Typebot Response");

            return $response->withStatus(204);
        }

        $typebot_conversation_token = $typebot_response?->sessionId;

        if (! empty($conversation['conversation_token']) &&
             empty($typebot_conversation_token) &&
             $typebot_conversation_token !== $conversation['conversation_token']
        ) {
            Logger::log("Finalizing Conversation");

            $value     = "true";
            $query     = "UPDATE `conversations` SET `ended` = :ended WHERE `conversation_id` = :conversation_id";
            $statement = $this->database->prepare($query);
            $statement->bindParam(':ended', $value);
            $statement->bindParam(':conversation_id', $conversation['conversation_id']);
            $statement->execute();

            return $response->withStatus(204);
        }

        if (! $conversation) {
            unset($conversation);
            unset($query);
            unset($statement);

            $conversation = [
                'account_id'         => $this->database->quote($account_id),
                'conversation_id'    => $this->database->quote($conversation_id),
                'conversation_token' => $typebot_conversation_token,
                'ended'              => 'false'
            ];
            $query        =
                "
	        INSERT INTO `conversations`
	            (
	             	account_id,
	             	conversation_id,
	             	conversation_token,
	             	ended
	             )
			VALUES     
				(
				 	:account_id,
					:conversation_id,
					:conversation_token,
				 	:ended
				) 
	        ";
            $statement    = $this->database->prepare($query);
            $statement->bindParam(':account_id', $conversation['account_id']);
            $statement->bindParam(':conversation_id', $conversation['conversation_id']);
            $statement->bindParam(':conversation_token', $conversation['conversation_token']);
            $statement->bindParam(':ended', $conversation['ended']);
            $statement->execute();

            Logger::log("Conversation stored in database successfully");
            Logger::log("Stored Conversation ID {$this->database->lastInsertId()}");
        }

        $message = RichTextReader::parseToString($typebot_response->messages);

        $chatwoot_response = $this->sendToChatwoot($message, $account_id, $conversation_id);
        Logger::log("Message sent to Chatwoot");
        Logger::log("Response: $chatwoot_response");


        return $response->withStatus(204);
    }

    private function sendMessageToTypeBot(string $message, array|false $conversation): false|string
    {
        $additionalParams = [];

        Logger::log("Conversation Body: " . json_encode($conversation));

        if (is_array($conversation) && ! empty($conversation['conversation_token'])) {
            $additionalParams['message']   = $message;
            $additionalParams['sessionId'] = $conversation['conversation_token'];
        }

        $content = [
            ...$additionalParams,
            'startParams' => [
                'typebot' => $_ENV['TYPEBOT_ID']
            ]
        ];

        Logger::log("Body sent to typebot: " . json_encode($content));

        $options = [
            'http' => [
                'method'  => 'POST',
                'content' => json_encode($content),
                'header'  => 'Content-Type: application/json'
            ]
        ];

        $context = stream_context_create($options);

        return file_get_contents($_ENV['TYPEBOT_API_URL'], false, $context);
    }

    public function sendToChatwoot(string $message, string $account, string $conversation): false|string
    {
        $url     = "{$_ENV['CHATWOOT_BASE_URL']}/api/v1/accounts/$account/conversations/$conversation/messages";
        $data    = [
            'content'      => $message,
            'message_type' => 'outgoing',
            'private'      => true
        ];
        $headers = "application/json\r\nAccept: application/json\r\napi_access_token: {$_ENV['CHATWOOT_BOT_TOKEN']}";

        $options = [
            'http' => [
                'method'  => 'POST',
                'content' => json_encode($data),
                'header'  =>
                    "Content-Type: $headers"
            ]
        ];

        $context = stream_context_create($options);

        return file_get_contents($url, false, $context);
    }
}
