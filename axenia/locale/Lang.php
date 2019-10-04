<?php

class Lang
{
    private static $availableLangs;
    private static $messageArray;   //массив сообщений из messages.php
    private static $currentLang;

    public static function availableLangs()
    {
        if (!isset(self::$availableLangs)) {
            self::$availableLangs = ["en" => '🇬🇧 English', "ru" => '🇷🇺 Русский', "ua" => '🇺🇦 Українська', "ruUN" => '🔞 Русский (матерный)', "uz" => "🇺🇿 O'zbek"];
        }

        return self::$availableLangs;
    }

    public static function defaultLangKey()
    {
        return "ru";
    }

    public static function getCurrentLangDesc()
    {
        return self::$availableLangs[isset(self::$currentLang) ? self::$currentLang : self::defaultLangKey()];
    }

    /**
     * Обязательно должен вызваться
     * @param string $lang 'ru' or 'en' or etc.
     */
    public static function init($lang = "ru")
    {
        self::availableLangs();
        self::loadMessages($lang);
        self::$currentLang = $lang;
    }

    public static function isUncensored()
    {
        return self::$currentLang == 'ruUN';
    }

    public static function message($modificator, $param = NULL)
    {
        self::loadMessages(isset(self::$currentLang) ? self::$currentLang : self::defaultLangKey());

        $out = self::$messageArray[$modificator];

        return $param != NULL ? Util::insert($out, $param) : $out;
    }

    public static function messageRu($modificator, $param = NULL)
    {
        self::loadMessages("ru");
        $out = self::$messageArray[$modificator];
        return $param != NULL ? Util::insert($out, $param) : $out;
    }

    public static function loadMessages($lang)
    {
        if (!isset(self::$messageArray)) {
            self::$messageArray = include "messages.$lang.php";
            //self::$messageArray = parse_ini_file("messages.ini", true);
        }
    }

}