<?php

class Lang
{
    private static $availableLangs;
    private static $messageArray;   //массив сообщений из messages.php
    private static $currentLang;

    public static function availableLangs()
    {
        if (!isset(self::$availableLangs)) {
            self::$availableLangs = array("en" => '🇬🇧 English', "ru" => '🇷🇺 Русский', "ruUN" => '🔞 Русский (матерный)');
        }

        return self::$availableLangs;
    }

    public static function defaultLangKey()
    {
        return "en";
    }

    public static function getCurrentLangDesc()
    {
        return self::$availableLangs[isset(self::$currentLang) ? self::$currentLang : self::defaultLangKey()];
    }

    /**
     * Обязательно должен вызваться
     * @param string $lang 'ru' or 'en' or etc.
     */
    public static function init($lang = "en")
    {
        self::availableLangs();
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

        $out = self::$messageArray[$modificator][isset(self::$currentLang) ? self::$currentLang : self::defaultLangKey()];

        return $param != NULL ? Util::insert($out, $param) : $out;
    }

    public static function messageRu($modificator, $param = NULL)
    {
        if (!isset(self::$messageArray)) {
            self::$messageArray = include 'messages.php';
        }

        $out = self::$messageArray[$modificator]["ru"];

        return $param != NULL ? Util::insert($out, $param) : $out;
    }

}