<?php

namespace App\Helpers;

use Exception;

class FileHelper
{
    public static function readJsonFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception('File does not exist.');
        }

        $contents = file_get_contents($filePath);
        return json_decode($contents, true);
    }

    public static function writeJsonFile($filePath, $data)
    {
        if (false === file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT))) {
            throw new Exception('Failed to write file.');
        }
    }
}
