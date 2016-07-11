<?php

class Lang
{

        public static $availableLangs;   //массив сообщений из messages.php
    private static $messageArray;   //
private static $currentLang;

    public static function defaultLang()
    {
        if (!isset(self::$availableLangs)) {
            self::$availableLangs = array('en' => '🇬🇧 English', 'ru' => '🇷🇺 Русский', 'ruUN' => '🔞 Русский (матерный)');
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
            self::$availableLangs = array('en' => '🇬🇧 English', 'ru' => '🇷🇺 Русский', 'ruUN' => '🔞 Русский (матерный)');
        }
        if (!isset(self::$messageArray)) {
            self::$messageArray = include 'messages.php';
        }
        self::$currentLang = $lang;
    }

    public static function isUncensored()
    {
        return self::$currentLang == 'ruUN';
    }

    public static function message($modificator, $param = NULL)
    {
        if (!isset(self::$messageArray)) {
            self::$messageArray = include 'messages.php';
        }

        $out = self::$messageArray[$modificator][isset(self::$currentLang) ? self::$currentLang : "en"];

        return $param != NULL ? Util::insert($out, $param) : $out;
    }

    public static function messageRu($modificator, $param = NULL)
    {
        if (!isset(self::$messageArray)) {
            self::$messageArray = include 'messages.php';
        }

        $out = self::$messageArray[$modificator]['ru'];

        return $param != NULL ? Util::insert($out, $param) : $out;
    }

}