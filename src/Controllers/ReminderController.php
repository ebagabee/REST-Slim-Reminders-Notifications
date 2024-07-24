<?php

namespace App\Controllers;

use App\Models\Reminder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\ReminderService;
use App\Services\OpenAIService;
use Exception;
use App\Helpers\JsonHelper;
use App\Services\ValidationService;
use App\Services\MessageProcessingService;
use App\Services\WhatsappService;
use Carbon\Carbon;

class ReminderController
{
    private $reminderService;
    private $openAIService;
    private $validationService;
    private $messageProcessingService;
    private $whatsappService;

    public function __construct(
        ReminderService $reminderService,
        OpenAIService $openAIService,
        ValidationService $validationService,
        MessageProcessingService $messageProcessingService,
        WhatsappService $whatsappService
    ) {
        $this->reminderService = $reminderService;
        $this->openAIService = $openAIService;
        $this->validationService = $validationService;
        $this->messageProcessingService = $messageProcessingService;
        $this->whatsappService = $whatsappService;
    }

    public function getReminders(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();
        $page = $queryParams['page'] ?? 1;
        $perPage = $queryParams['perPage'] ?? 5;
        $startDate = $queryParams['startDate'] ?? null;
        $endDate = $queryParams['endDate'] ?? null;

        try {
            $reminders = $this->reminderService->getReminders($page, $perPage, $startDate, $endDate);
            $response->getBody()->write(json_encode($reminders));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            error_log('Error in getReminders: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function addReminder(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();
            list($message, $phoneNumber, $mood) = $this->validationService->validateReminderData($data);
            list($event, $dateTime) = $this->messageProcessingService->analyzeMessage($message);

            if ($dateTime < Carbon::now('America/Sao_Paulo')->setSecond(0)) {
                throw new \InvalidArgumentException("Calma lá, amigao, ainda não inventamos a maquina do tempo");
            }

            $reminder = new Reminder($event, $phoneNumber, $dateTime, $mood);
            $this->reminderService->addReminder($reminder);

            return JsonHelper::jsonResponse($response, ['success' => 'Lembrete adicionado com sucesso'], 201);
        } catch (\InvalidArgumentException $e) {
            return JsonHelper::jsonResponse($response, ['error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            error_log('Erro ao adicionar lembrete: ' . $e->getMessage());
            return JsonHelper::jsonResponse($response, ['error' => 'Erro ao adicionar lembrete'], 500);
        }
    }

    public function deleteReminder(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        try {
            $this->reminderService->deleteReminder($id);
            return JsonHelper::jsonResponse($response, ['success' => 'Lembrete excluído com sucesso'], 200);
        } catch (Exception $e) {
            error_log('Error in deleteReminder: ' . $e->getMessage());
            return JsonHelper::jsonResponse($response, ['error' => 'Erro ao excluir lembrete'], 500);
        }
    }

    public function sendReminderToWhatsapp(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();
            list($message, $phoneNumber, $character) = $this->validationService->validateSendReminderData($data);

            $aiMessage = $this->generateAiMessage($message, $character);

            $this->whatsappService->sendReminder($aiMessage, $phoneNumber);

            return JsonHelper::jsonResponse($response, ['success' => 'Mensagem enviada com sucesso'], 200);
        } catch (\InvalidArgumentException $e) {
            return JsonHelper::jsonResponse($response, ['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return JsonHelper::jsonResponse($response, ['error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            error_log('Erro ao enviar lembrete para WhatsApp: ' . $e->getMessage());
            return JsonHelper::jsonResponse($response, ['error' => 'Erro ao processar a solicitação'], 500);
        }
    }

    private function generateAiMessage($message, $character)
    {
        return $this->openAIService->generateMessage($message, $character);
    }
}
