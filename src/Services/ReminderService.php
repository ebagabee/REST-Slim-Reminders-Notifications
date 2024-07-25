<?php

namespace App\Services;

use App\Models\Reminder;
use App\Helpers\FileHelper;

class ReminderService
{
    private $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/../../storage/reminders.json';
        $this->initializeFile();
    }

    private function initializeFile()
    {
        if (!file_exists($this->filePath)) {
            FileHelper::writeJsonFile($this->filePath, ['total' => 0, 'reminders' => []]);
        }
    }

    public function addReminder(Reminder $reminder)
    {
        $data = $this->readData();
        $data['reminders'][] = $this->formatReminder($reminder);
        $data['total'] = count($data['reminders']);
        $this->writeData($data);
        return $reminder;
    }

    public function getReminders($page = 1, $perPage = 5, $startDate = null, $endDate = null)
    {
        $data = $this->readData();
        $reminders = $this->filterReminders($data['reminders'], $startDate, $endDate);
        $reminders = $this->sortRemindersByDate($reminders);
        $totalFiltered = count($reminders);
        $pageReminders = $this->paginateReminders($reminders, $page, $perPage);

        return [
            'total' => $totalFiltered,
            'reminders' => $pageReminders
        ];
    }

    public function deleteReminder($id)
    {
        $data = $this->readData();
        $data['reminders'] = array_filter($data['reminders'], fn ($reminder) => $reminder['id'] !== $id);
        $data['total'] = count($data['reminders']);
        $this->writeData($data);
    }

    private function readData()
    {
        return FileHelper::readJsonFile($this->filePath);
    }

    private function writeData($data)
    {
        FileHelper::writeJsonFile($this->filePath, $data);
    }

    private function formatReminder(Reminder $reminder)
    {
        return [
            'id' => $reminder->id,
            'message' => $reminder->message,
            'phoneNumber' => $reminder->phoneNumber,
            'dateTime' => $reminder->dateTime,
            'mood' => $reminder->mood,
        ];
    }

    private function filterReminders(array $reminders, $startDate, $endDate)
    {
        return array_filter($reminders, function ($reminder) use ($startDate, $endDate) {
            $dateTime = $reminder['dateTime'];
            $reminderDate = substr($dateTime, 0, 10);

            
            if ($startDate && $endDate && $startDate === $endDate) {
                return $reminderDate === $startDate;
            }

            return (!$startDate || $reminderDate >= $startDate) && (!$endDate || $reminderDate <= $endDate);
        });
    }

    private function sortRemindersByDate(array $reminders)
    {
        usort($reminders, function ($a, $b) {
            return strtotime($a['dateTime']) - strtotime($b['dateTime']);
        });

        return $reminders;
    }

    private function paginateReminders(array $reminders, $page, $perPage)
    {
        $start = ($page - 1) * $perPage;
        return array_slice($reminders, $start, $perPage);
    }
}
