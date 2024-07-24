<?php

namespace App\Handlers;

class MessageHandler
{
    public function createMessage(string $event, string $character): string
    {
        return "Mensagem gerada para o evento '$event' e o personagem '$character'.";
    }
}
