<?php

use App\Controllers\ReminderController;

$app->post('/api/reminder', [ReminderController::class, 'addReminder']);
$app->get('/api/reminders', [ReminderController::class, 'getReminders']);
$app->post('/api/send-reminder', [ReminderController::class, 'sendReminderToWhatsapp']);
$app->delete('/api/reminder/{id}', [ReminderController::class, 'deleteReminder']);
