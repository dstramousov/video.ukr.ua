<?php

class Vida_Helpers_DojoHelper
{
    /**
     * Конвертирует строку из utf-8 в кодировку нашего сайта
     * @param  string       $val  Строка которую необходимо преобразовать
     * @return string       Преобразованная строка
     */
    public static function convert($val)
    {
        return iconv("windows-1251", "utf-8", $val);
    }
}