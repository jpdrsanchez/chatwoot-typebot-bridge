<?php

use Dotenv\Dotenv;
use Studio\Bridge\Bridge;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$bridge = new Bridge();
$bridge->read();
