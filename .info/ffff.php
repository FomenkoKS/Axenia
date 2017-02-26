<?php
// Example 1 [Merging associative arrays. When two or more arrays have same key
// then the last array key value overrides the others one]
define("BOT_NAME", "format_fm_bot");
require_once('../axenia/core/util.php');

//$inline_keyboard = [[
//    [
//        'text' => 1,
//        'callback_data' => 1
//    ]]
//];
//
//$inline_keyboard2[] = array_merge([$inline_keyboard[0]], [[
//    ['text' => 2,
//        'callback_data' => 2],
//    ['text' => 3,
//        'callback_data' => 3
//    ]]]);
//
//print_r($inline_keyboard2);



echo Util::startsWith("👍 fdfdfdf", "👍");