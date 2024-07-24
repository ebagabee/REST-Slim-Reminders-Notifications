<?php

namespace App\Controllers;

use App\Handlers\DateHandler;
use App\Models\Reminder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\ReminderService;
use App\Services\ZAPIService;
use App\Services\OpenAIService;
use Carbon\Carbon;
use Exception;

class ReminderController
{
    private $reminderService;
    private $zapiService;
    private $openAIService;
    private $dateHandler;

    public function __construct(ReminderService $reminderService, ZAPIService $zapiService, OpenAIService $openAIService, DateHandler $dateHandler)
    {
        $this->reminderService = $reminderService;
        $this->zapiService = $zapiService;
        $this->openAIService = $openAIService;
        $this->dateHandler = $dateHandler;
    }

    public function addReminder(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $message = $data['message'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $mood = $data['mood'] ?? null;

        if (!$message || !$phoneNumber || !$mood) {
            $response->getBody()->write(json_encode(['error' => 'Mensagem, número de telefone e humor são obrigatórios']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $dateTime = $this->dateHandler->extractDateFromMessage($message) ?? Carbon::now();

        $reminder = new Reminder($message, $phoneNumber, $dateTime->format('Y-m-d H:i:s'), $mood);
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
        $page = $request->getQueryParams()['page'] ?? 1;
        $perPage = $request->getQueryParams()['perPage'] ?? 5;

        try {
            $reminders = $this->reminderService->getReminders($page, $perPage);
            $response->getBody()->write(json_encode($reminders));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            error_log('Error in getReminders: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function deleteReminder(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        try {
            $this->reminderService->deleteReminder($id);
            $response->getBody()->write(json_encode(['success' => 'Lembrete excluído com sucesso']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            error_log('Error in deleteReminder: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Erro ao excluir lembrete']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
