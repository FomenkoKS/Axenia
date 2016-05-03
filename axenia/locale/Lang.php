<?php

class Lang
{
    
    private static $messageArray;   //массив сообщений из messages.php
    private static $currentLang;   // 
    public static $availableLangs;

    public static function defaultLang()
    {
        if (!isset(self::$availableLangs)) {
            self::$availableLangs = array('en' => '🇬🇧 English', 'ru' => '🇷🇺 Русский');
        }

        return self::$availableLangs;
    }

    /**
     * Обязательно должен вызваться
     * @param string $lang 'ru' or 'en' or etc.
     */
    public static function init($lang = 'en')
    {
        if (!isset(self::$availableLangs)) {
            self::$availableLangs = array('en' => '🇬🇧 English', 'ru' => '🇷🇺 Русский');
        }
        if (!isset(self::$messageArray)) {
            self::$messageArray = include 'messages.php';
        }
        self::$currentLang = $lang;
    }

    public static function message($modificator, $param = NULL)
    {
        if (!isset(self::$messageArray)) {
            self::$messageArray = include 'messages.php';
        }

        $out = self::$messageArray[$modificator][isset(self::$currentLang) ? self::$currentLang : "en"];

        return $param != NULL ? Util::insert($out, $param) : $out;
    }

}