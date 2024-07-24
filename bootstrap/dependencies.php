<?php

use App\Services\ReminderService;
use App\Services\ZAPIService;
use App\Services\OpenAIService;

return [
    ReminderService::class => function () {
        return new ReminderService();
    },
    ZAPIService::class => function () {
        return new ZAPIService();
    },
    OpenAIService::class => function () {
        return new OpenAIService();
    }
];
