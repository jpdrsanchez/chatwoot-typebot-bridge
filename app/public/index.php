<?php

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Studio\Bridge\Controllers\WebhookController;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$app = AppFactory::create();

$twig = Twig::create('../views', [ 'cache' => false ]);

$app->post('/webhook', [ WebhookController::class, 'index' ]);
$app->get('/form', function ($request, $response, $args) {
    $view = Twig::fromRequest($request);

    return $view->render($response, 'form.html');
});

$app->addErrorMiddleware(true, true, true);
$app->add(TwigMiddleware::create($app, $twig));

$app->run();
