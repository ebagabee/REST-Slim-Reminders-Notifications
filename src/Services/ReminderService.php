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
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    public function addReminder(Reminder $reminder)
    {
        $reminders = $this->getReminders();
        $reminders[] = $reminder;
        file_put_contents($this->filePath, json_encode($reminders));
        return $reminder;
    }

    public function getReminders()
    {
        try {
            $remindersData = json_decode(file_get_contents($this->filePath), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Failed to decode reminders JSON: ' . json_last_error_msg());
            }

            return array_map(function ($reminderData) {
                return new Reminder(
                    $reminderData['message'],
                    $reminderData['phoneNumber'],
                    $reminderData['dateTime'],
                    $reminderData['mood'],
                    $reminderData['id']
                );
            }, $remindersData);
        } catch (Exception $e) {
            error_log('Error fetching reminders: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteReminder($id)
    {
        try {
            $reminders = $this->getReminders();
            $reminders = array_filter($reminders, function ($reminder) use ($id) {
                return $reminder->id !== $id;
            });

            $reminders = array_values($reminders);

            file_put_contents($this->filePath, json_encode($reminders));
            return true;
        } catch (Exception $e) {
            error_log('Error deleting reminder: ' . $e->getMessage());
            throw $e;
        }
    }
}
