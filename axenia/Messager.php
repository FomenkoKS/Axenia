<?php

define("MESSAGER_LANGUAGE", 'default');

class Messager
{
    private static $messageContainer;

    /**
     * Messager constructor.
     * @param $messageContainer
     */
    private function __construct()
    {
    }

    private static function getContainer()
    {
        if (is_null(self::$messageContainer)) {
            self::$messageContainer = array('default' =>
                array('chat_greetings' => 'Всем чмаффки в этом чатике.'));
        }
        return self::$messageContainer;
    }

    public static function msgLang($language, $textLink)
    {
        return self::getLanguageMap($language)[$textLink];
    }

    public static function msg($textLink)
    {
        return self::getLanguageMap(null)[$textLink];
    }

    private static function getLanguageMap($language)
    {
        $messageContainer = self::getContainer();
        print_r($messageContainer);
        if (!is_null($language) && array_key_exists($language, $messageContainer)) {
            return $messageContainer[$language];
        }
        if (defined('MESSAGER_LANGUAGE')) {
            return $messageContainer[MESSAGER_LANGUAGE];
        }
        throw new Exception('Language \'' . $language . '\' was not found. Global parameter \'MESSAGER_LANGUAGE\' is not defined.');
    }

    private static function updateContainer($newMessageContainer)
    {
        self::$messageContainer = $newMessageContainer;
    }

    public static function appendLanguage($arrayMap)
    {
        $messageContainer = self::getContainer();
        if (is_null($arrayMap)) return false;
//        if ( !is_array($arrayMap[0])) return false;
//        $countInputArray = count($arrayMap);
//        if ($countInputArray == 0) return false;
//        if (!in_array($arrayMap[0], $messageContainer)) return false;
        $mergedMessageContainer = array_merge($messageContainer, $arrayMap);
        self::updateContainer($mergedMessageContainer);
        print_r($mergedMessageContainer);
        return true;
    }


}

//echo Messager::msg('chat_greetings');
echo "merge = " . Messager::appendLanguage(array('default2' =>
        array('chat_greetings' => 'Привет!')));
echo Messager::msgLang('default2', 'chat_greetings')
?>