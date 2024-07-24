<?php

namespace App\Services;

class WhatsappService
{
    private $zapiService;

    public function __construct(ZAPIService $zapiService)
    {
        $this->zapiService = $zapiService;
    }

    public function sendReminder($message, $phoneNumber)
    {
        return $this->zapiService->sendReminder($message, $phoneNumber);
    }
}