<?php

namespace App\Services;

use App\Services\OpenAIService;
use Carbon\Carbon;

class MessageProcessingService
{
    private $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function analyzeMessage($message)
    {
        list($event, $dateTime) = $this->openAIService->analyzeMessage($message);

        if (!$dateTime) {
            $dateTime = Carbon::now('America/Sao_Paulo')->format('Y-m-d H:i:s');
        }

        return [$event, $dateTime];
    }
}
