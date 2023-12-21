<?php

namespace Studio\Bridge;

final class Bridge
{
    private const LOG_FILE_PATH = __DIR__ . '/logs.txt';

    public function __construct(
        private readonly string $chatwoot_url,
        private readonly string $chatwoot_bot_token,
        private readonly string $typebot_url,
        private readonly string $typebot_id,
        private readonly bool $should_log_responses
    ) {
    }

    public function read(): void
    {
        $logText = '';

        $response = file_get_contents("php://input");
        if (empty($response) || ( ! is_string($response) && is_array(json_decode($response,)) )) {
            $logText .= '[' . date('c') . ']: Invalid JSON' . PHP_EOL;
            $this->logRequestInfo($logText);

            return;
        }

        $data = json_decode($response);

        $message_type = $data->message_type;
        if ($message_type !== 'incoming') {
            $logText .= '[' . date('c') . ']: Message is not incoming' . PHP_EOL;
            $this->logRequestInfo($logText);

            return;
        }

        $message_content = $data->content;
        $conversation_id = $data->conversation->id;
        $account_id      = $data->account->id;

        $logText .= '[' . date('c') . ']: Message is incoming' . PHP_EOL;
        $logText .= '[' . date('c') . ']: Message Type: ' . $message_type . PHP_EOL;
        $logText .= '[' . date('c') . ']: Message Content: ' . $message_content . PHP_EOL;
        $logText .= '[' . date('c') . ']: Conversation ID: ' . $conversation_id . PHP_EOL;
        $logText .= '[' . date('c') . ']: Account ID: ' . $account_id . PHP_EOL;
    }

    private function logRequestInfo(string $data): void
    {
        if (! $this->should_log_responses) {
            return;
        }

        $file = fopen(self::LOG_FILE_PATH, 'a+');
        if (! $file) {
            return;
        }

        fputs($file, $data);
        fclose($file);
    }

    private function sendMessageToTypeBot(string $content)
    {
        $content = [
            'startParams' => [
                'typebot' => $this->typebot_id
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
        $response = file_get_contents($this->typebot_url, false, $context);

        $data = json_decode($response);
    }

    private function formatFromChatWoot(array $messages)
    {
        return array_map(
            function (object $value) {
            },
            $messages
        );
    }
}
