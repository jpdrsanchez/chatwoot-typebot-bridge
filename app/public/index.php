<?php

use Dotenv\Dotenv;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use Studio\Bridge\Controllers\WebhookController;
use Studio\Bridge\Support\Database;

require __DIR__ . '/../vendor/autoload.php';

$database = new Database();
$database->init();

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$app = AppFactory::create();

$app->post('/webhook', function (Request $request, Response $response) use ($database) {
    $controller = new WebhookController($database->db);

    return $controller->index($request, $response);
});

$app->addErrorMiddleware(true, true, true);

$app->run();
