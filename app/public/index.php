<?php

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Studio\Bridge\Controllers\WebhookController;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$app = AppFactory::create();

$app->get('/webhook', [ WebhookController::class, 'index' ]);

$app->addErrorMiddleware(true, true, true);

$app->run();
