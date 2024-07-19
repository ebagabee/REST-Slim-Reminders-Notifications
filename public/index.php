<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use App\Controllers\MessageController;
use App\Controllers\ReminderController;
use App\Services\ReminderService;

$container = new Container();

$container->set(ReminderService::class, function () {
    return new ReminderService();
});

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->post('/api/message', [MessageController::class, 'sendMessage']);
$app->post('/api/reminder', [ReminderController::class, 'addReminder']);
$app->get('/api/reminders', [ReminderController::class, 'getReminders']);
$app->post('/api/send-message', [ReminderController::class, 'sendReminderToWhatsApp']);

$app->get('/api/test', function (ServerRequestInterface $request, Response $response) {
    $response->getBody()->write(json_encode(['status' => 'API is working']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->run();
