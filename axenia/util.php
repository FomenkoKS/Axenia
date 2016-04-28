<?php

class Util
{
    /**
     * Содержится ли элемент в списке
     * @param $enumString String с элементами через , (1, '23', 'test')
     * @param $value
     * @return true/false
     */
    public static function isInEnum($enumString, $value)
    {
        $enumArray = explode(',', $enumString);
        return in_array($value, $enumArray);
    }
}