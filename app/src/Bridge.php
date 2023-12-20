<?php

namespace Studio\Bridge;

class Bridge
{
    public function __construct()
    {
    }

    public function read(): void
    {
        if ($json = json_decode(file_get_contents("php://input"), true)) {
            $data = $json;
        } else {
            $data = $_POST;
        }

        $data = serialize($data);

        $file = fopen(__DIR__ . '/log.txt', 'a+');

        if (! $file) {
            return;
        }
        $now = date('c');
        fputs($file, "[$now]: $data \n");
        fclose($file);
    }
}
