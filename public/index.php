<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

AppFactory::setContainer($container);

$app = AppFactory::create();

require __DIR__ . '/../bootstrap/dependencies.php';
require __DIR__ . '/../bootstrap/middleware.php';
require __DIR__ . '/../bootstrap/routes.php';

$app->run();

