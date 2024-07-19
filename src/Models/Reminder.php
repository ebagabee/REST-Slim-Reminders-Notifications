<?php

namespace App\Models;

class Reminder
{
    public $id;
    public $message;
    public $phoneNumber;
    public $dateTime;
    public $mood;

    public function __construct($message, $phoneNumber, $dateTime, $mood, $id = null)
    {
        $this->message = $message;
        $this->phoneNumber = $phoneNumber;
        $this->dateTime = $dateTime;
        $this->mood = $mood;
        $this->id = $id ?? uniqid();
    }
}
