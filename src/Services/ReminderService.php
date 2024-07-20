<?php

namespace App\Services;

use App\Models\Reminder;
use Exception;

class ReminderService
{
    private $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/../../storage/reminders.json';
        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode(['total' => 0, 'reminders' => []]));
        }
    }

    public function addReminder(Reminder $reminder)
    {
        $data = json_decode(file_get_contents($this->filePath), true);

        if (!isset($data['reminders'])) {
            $data['reminders'] = [];
        }

        $data['reminders'][] = [
            'id' => $reminder->id,
            'message' => $reminder->message,
            'phoneNumber' => $reminder->phoneNumber,
            'dateTime' => $reminder->dateTime,
            'mood' => $reminder->mood,
        ];

        $data['total'] = count($data['reminders']);

        try {
            file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
            return $reminder;
        } catch (Exception $e) {
            error_log('Error saving reminder: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getReminders($page = 1, $perPage = 5)
    {
        try {
            $data = json_decode(file_get_contents($this->filePath), true);

            if (!isset($data['reminders'])) {
                $data['reminders'] = [];
            }

            $total = count($data['reminders']);
            $start = ($page - 1) * $perPage;
            $end = min($start + $perPage, $total);
            $pagedReminders = array_slice($data['reminders'], $start, $end - $start);

            return [
                'total' => $total,
                'reminders' => $pagedReminders
            ];
        } catch (Exception $e) {
            error_log('Error fetching reminders: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteReminder($id)
    {
        try {
            $data = json_decode(file_get_contents($this->filePath), true);

            if (!isset($data['reminders'])) {
                $data['reminders'] = [];
            }

            $data['reminders'] = array_filter($data['reminders'], function ($reminder) use ($id) {
                return $reminder['id'] !== $id;
            });

            $data['total'] = count($data['reminders']);

            file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            error_log('Error deleting reminder: ' . $e->getMessage());
            throw $e;
        }
    }
}
