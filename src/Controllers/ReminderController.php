<?php

namespace App\Controllers;

use App\Models\Reminder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\ReminderService;
use App\Services\ZAPIService;
use App\Services\OpenAIService;
use Exception;

class ReminderController
{
    private $reminderService;
    private $zapiService;
    private $openAIService;

    public function __construct(ReminderService $reminderService, ZAPIService $zapiService, OpenAIService $openAIService)
    {
        $this->reminderService = $reminderService;
        $this->zapiService = $zapiService;
        $this->openAIService = $openAIService;
    }

    public function addReminder(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $message = $data['message'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $dateTime = $data['dateTime'] ?? null;
        $mood = $data['mood'] ?? null;

        if (!$message || !$phoneNumber || !$dateTime || !$mood) {
            $response->getBody()->write(json_encode(['error' => 'Todos os campos são obrigatórios']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $reminder = new Reminder($message, $phoneNumber, $dateTime, $mood);
        $this->reminderService->addReminder($reminder);

        $response->getBody()->write(json_encode(['success' => 'Lembrete adicionado com sucesso']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function sendReminderToWhatsapp(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $message = $data['message'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $character = $data['character'] ?? 'Bob Esponja';

        if (!$message || !$phoneNumber || !$character) {
            $response->getBody()->write(json_encode(['error' => 'Mensagem, número de telefone e personagem são obrigatórios']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $aiMessage = $this->openAIService->generateMessage($message, $character);

            $result = $this->zapiService->sendReminder($aiMessage, $phoneNumber);

            if (strpos($result, 'error') !== false) {
                $response->getBody()->write(json_encode(['error' => 'Falha ao enviar a mensagem']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }

            $response->getBody()->write(json_encode(['success' => 'Mensagem enviada com sucesso']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            error_log('Error in sendReminderToWhatsapp: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Erro ao processar a solicitação']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getReminders(Request $request, Response $response)
    {
        try {
            $reminders = $this->reminderService->getReminders();
            $response->getBody()->write(json_encode($reminders));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            error_log('Error in getReminders: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
