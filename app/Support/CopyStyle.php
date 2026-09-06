<?php

namespace App\Support;

class CopyStyle
{
    public static function normalize(string $text): string
    {
        return str_ireplace([mb_chr(8212), '&'.'mdash;', '&#'.'8212;', '&#x'.'2014;'], '-', $text);
    }
}
