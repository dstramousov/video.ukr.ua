<?php

class Vida_Helpers_FeedHelper
{
    /**
     * Загружает RSS ленту
     * @param  string       $url  Адрес ленты для загрузки
     * @return object             Объект feed для обработки
     */
    public function loadFeed($url)
    {
        $feed = null;
        $hasError = false;
        try {
            $client = new Zend_Http_Client;
            $client->setConfig(
                array('timeout'=>10)
            );
            $feed = Zend_Feed::import($url);
        } catch (Zend_Feed_Exception $e) {
            Vida_Helpers_Exception::processException($e, Zend_Log::DEBUG);
            $hasError = true;
        } catch (Zend_Http_Client_Exception $e) {
            Vida_Helpers_Exception::processException($e, Zend_Log::DEBUG);
            $hasError = true;
        }
        if($hasError) {
            $feed = null;
        }
        
        return $feed;
    }
    
    /**
     * Конвертирует строку из utf-8 в кодировку нашего сайта
     * @param  string       $val  Строка которую необходимо преобразовать
     * @return string       Преобразованная строка
     */
    public static function convert($val)
    {
        return iconv("utf-8", "windows-1251", $val);
    }

    /**
    * Обрабатывает линк для хранения в системе
     * @param  string       $link Линк, который необходимо преобразовать
     * @return string       Преобразованный линк
    */
    public static function prepare_link($link)
    {
        return strtolower($link);
    }

    /**
    * Вычисляет hash линк-а для хранения в системе
     * @param  string       $link 
     * @return string       Hash переданного линка
    */
    public static function hash_link($link)
    {
        return md5(self::prepare_link($link));
    }

    /**
     * Конвертирует дату из формата gmt
     * @param  string       $string  Дата в формате gmt
     * @return date         Объект даты в нашем часовом поясе
     */
    public static function get_date_from_gmt($string) {
      return date('Y-m-d H:i:s', strtotime($string));
    }
    
}