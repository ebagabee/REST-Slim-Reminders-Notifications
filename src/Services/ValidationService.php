<?php

namespace App\Services;

class ValidationService
{
    public function validateReminderData($data)
    {
        $message = $data['message'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $mood = $data['mood'] ?? null;

        if (!$message || !$phoneNumber || !$mood) {
            throw new \InvalidArgumentException('Mensagem, número de telefone e humor são obrigatórios');
        }

        return [$message, $phoneNumber, $mood];
    }

    public function validateSendReminderData($data)
    {
        $message = $data['message'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $character = $data['character'] ?? 'Bob Esponja';

        if (!$message || !$phoneNumber || !$character) {
            throw new \InvalidArgumentException('Mensagem, número de telefone e personagem são obrigatórios');
        }

        return [$message, $phoneNumber, $character];
    }
}
