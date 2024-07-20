<?php

namespace App\Controllers;

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

    public function __construct(ReminderService $reminderService, ZAPIService $zapiService, OpenAIService $openAIService)
    {
        $this->reminderService = $reminderService;
        $this->zapiService = $zapiService;
        $this->openAIService = $openAIService;
    }

    private function extractDateFromMessage($message)
    {
        if (preg_match('/dia (\d{1,2}) de (\w+) de (\d{4})( às (\d{1,2}):(\d{2}))?/', $message, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            $hour = isset($matches[5]) ? $matches[5] : '00';
            $minute = isset($matches[6]) ? $matches[6] : '00';

            $months = [
                'janeiro' => 1, 'fevereiro' => 2, 'março' => 3, 'abril' => 4, 'maio' => 5,
                'junho' => 6, 'julho' => 7, 'agosto' => 8, 'setembro' => 9, 'outubro' => 10,
                'novembro' => 11, 'dezembro' => 12
            ];

            if (isset($months[$month])) {
                $monthNumber = $months[$month];
                return Carbon::create($year, $monthNumber, $day, $hour, $minute)->format('Y-m-d H:i:s');
            }
        } elseif (preg_match('/(hoje|amanhã)( às (\d{1,2}):(\d{2}))?/', strtolower($message), $matches)) {
            $now = Carbon::now();
            $date = $matches[1] === 'amanhã' ? $now->addDay() : $now;

            $hour = isset($matches[3]) ? $matches[3] : '00';
            $minute = isset($matches[4]) ? $matches[4] : '00';

            return $date->setTime($hour, $minute)->format('Y-m-d H:i:s');
        }

        return null;
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

        $dateTime = $this->extractDateFromMessage($message);
        if (!$dateTime) {
            $response->getBody()->write(json_encode(['error' => 'Não foi possível extrair a data e hora da mensagem']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $reminder = new Reminder($message, $phoneNumber, $dateTime, $mood);
        $this->reminderService->addReminder($reminder);

        $response->getBody()->write(json_encode(['success' => 'Lembrete adicionado com sucesso']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function deleteReminder(Request $request, Response $response, $args)
    {
        $id = $args['id'] ?? null;

        if (!$id) {
            $response->getBody()->write(json_encode(['error' => 'ID do lembrete é obrigatório']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $deleted = $this->reminderService->deleteReminder($id);

            if ($deleted) {
                $response->getBody()->write(json_encode(['success' => 'Lembrete deletado com sucesso']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['error' => 'Lembrete não encontrado']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        } catch (Exception $e) {
            error_log('Error in deleteReminder: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Erro ao processar a solicitação']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
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
