<?php

namespace App\Handlers;

use Carbon\Carbon;

class DateHandler
{
    public function extractDateFromMessage(string $message): ?Carbon
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
                return Carbon::create($year, $monthNumber, $day, $hour, $minute);
            }
        } elseif (preg_match('/(hoje|amanhã)( às (\d{1,2}):(\d{2}))?/', strtolower($message), $matches)) {
            $now = Carbon::now();
            $date = $matches[1] === 'amanhã' ? $now->addDay() : $now;

            $hour = isset($matches[3]) ? $matches[3] : '00';
            $minute = isset($matches[4]) ? $matches[4] : '00';

            return $date->setTime($hour, $minute);
        }

        return null;
    }
}
