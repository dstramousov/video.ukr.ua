<?php

class Vida_Helpers_AJAXHelper
{
    /**
     * Конвертирует строку из utf-8 в кодировку нашего сайта
     * @param  string       $val  Строка которую необходимо преобразовать
     * @return string       Преобразованная строка
     */
    public static function convert(&$array)
    {
        $func = create_function('&$item, $index', 'if(is_string($item)) $item = iconv("windows-1251", "utf-8", $item);');
        array_walk_recursive($array, $func);
        return $array;
    }

    public static function decode(&$array)
    {
        $func = create_function('&$item, $index', 'if(is_string($item)) $item = iconv("utf-8", "windows-1251", $item);');
        array_walk_recursive($array, $func);
        return $array;
    }
}