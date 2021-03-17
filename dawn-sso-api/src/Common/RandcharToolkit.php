<?php

namespace App\Common;

class RandcharToolkit
{
    /**
     *  type:
     *  1-numbers;
     *  2-letters;
     *  3-upLetters;
     *  4-numbers+letters
     *  5-numbers+upLetters
     *  6-letters+upLetters
     *  7-numbers+letters+upLetters.
     */
    public static function genChars(int $length = 16, int $type = 1)
    {
        $numbers = '1234567890';
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $upLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $chars = '';
        switch ($type) {
            case 1:
                $chars = $numbers;
                break;
            case 2:
                $chars = $letters;
                break;
            case 3:
                $chars = $upLetters;
                break;
            case 4:
                $chars = $numbers . $letters;
                break;
            case 5:
                $chars = $numbers . $upLetters;
                break;
            case 6:
                $chars = $letters . $upLetters;
                break;
            case 7:
            default:
                $chars = $numbers . $letters . $upLetters;
                break;
        }

        $charLen = strlen($chars);
        $randStr = '';
        for ($i = 0; $i < $length; ++$i) {
            $randStr .= $chars[mt_rand(0, $charLen - 1)];
        }

        return $randStr;
    }
}
