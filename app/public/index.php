<?php

use Slim\Factory\AppFactory;
use Studio\Bridge\Controllers\WebhookController;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->post('/webhook', [ WebhookController::class, 'index' ]);

$app->addErrorMiddleware(false, true, true);

$app->run();
