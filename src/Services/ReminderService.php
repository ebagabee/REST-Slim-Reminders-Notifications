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

    public function getReminders($page = 1, $perPage = 5, $startDate = null, $endDate = null)
    {
        try {
            $data = json_decode(file_get_contents($this->filePath), true);
            $reminders = $data['reminders'] ?? [];
            $total = count($reminders);

            if ($startDate) {
                $reminders = array_filter($reminders, function ($reminder) use ($startDate) {
                    return $reminder['dateTime'] >= $startDate;
                });
            }

            if ($endDate) {
                $reminders = array_filter($reminders, function ($reminder) use ($endDate) {
                    return $reminder['dateTime'] <= $endDate;
                });
            }


            $totalFiltered = count($reminders);
            $start = ($page - 1) * $perPage;
            $pageReminders = array_slice($reminders, $start, $perPage);

            return [
                'total' => $totalFiltered,
                'reminders' => $pageReminders
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
