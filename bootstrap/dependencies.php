<?php

use App\Handlers\DateHandler;
use App\Handlers\MessageHandler;
use App\Services\ReminderService;
use App\Services\ZAPIService;
use App\Services\OpenAIService;
use Psr\Container\ContainerInterface;

return [
    ReminderService::class => function (ContainerInterface $container) {
        return new ReminderService();
    },
    ZAPIService::class => function (ContainerInterface $container) {
        return new ZAPIService();
    },
    OpenAIService::class => function (ContainerInterface $container) {
        return new OpenAIService();
    },
    DateHandler::class => function (ContainerInterface $container) {
        return new DateHandler();
    },
    MessageHandler::class => function (ContainerInterface $container) {
        return new MessageHandler();
    }
];
