<?php
define("BOT_NAME", "Axenia_bot");
require_once('../axenia/core/util.php');
require('../axenia/locale/Lang.php');


class LangTest extends PHPUnit_Framework_TestCase
{

    public function testMessage()
    {
        Lang::init("ruUN");

        $this->assertEquals(Lang::message("chat.lang.end", array("botName" => BOT_NAME)),
            "Проверка языка: борщ, балалайка. Теперь я могу говорить по-русски, блеать!");

        $this->assertEquals(
            Lang::message("karma.plus", array("from" => "formatq", "to" => "abrikoseg", "k1" => "100", "k2" => "666")),
            "<b>formatq (100)</b> подкинул в карму <b>abrikoseg (666)</b>");

        Lang::init();

        $this->assertEquals(Lang::message("chat.lang.end", array("botName" => BOT_NAME)),
            "Ok, now I'm speaking English!");

        $this->assertEquals(
            Lang::message("karma.plus", array("from" => "formatq", "to" => "abrikoseg", "k1" => "100", "k2" => "666")),
            "<b>formatq (100)</b> give some karma to <b>abrikoseg (666)</b>");

        $this->assertTrue(array_search('🇬🇧 English', Lang::defaultLang()) == 'en');
        $this->assertTrue(array_search('🇬🇧 English2', Lang::defaultLang()) === false);
    }

}


