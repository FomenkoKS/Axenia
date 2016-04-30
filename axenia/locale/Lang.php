<?php

class Lang
{

    private static $langArray;
    private static $lang;

    public static function init($lang)
    {
        if (!isset(self::$langArray)) {
            self::$langArray = include 'messages.php';
        }
        self::$lang = $lang;
    }

    public static function message($modificator, $param = NULL)
    {
        if (!isset(self::$langArray)) {
            self::$langArray = include 'messages.php';
        }

        $out = self::$langArray[$modificator][isset(self::$lang) ? self::$lang : "en"];

        return $param != NULL ? Util::insert($out, $param) : $out;
    }

}