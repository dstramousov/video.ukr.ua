<?php

class Vida_Helpers_Config
{
    /**
     * Заменяет предустановленные константы значениями
     * @param $str string Исходное значение
     * @return none
     */
    public static function prepare($str)
    {
        $src = array("%APPLICATION_PATH%");
        $dst = array(APPLICATION_PATH);
        $str = str_replace($src, $dst, $str);
        return $str;
    }


    public static function get_baseurl() {
        $host  = $_SERVER['HTTP_HOST'];
        $proto = (empty($_SERVER['HTTPS'])) ? 'http' : 'https';
        $port  = $_SERVER['SERVER_PORT'];
        $uri   = $proto . '://' . $host;
        if ((('http' == $proto) && (80 != $port)) || (('https' == $proto) && (443 != $port))) {
            $uri .= ':' . $port;
        }
        $url = $uri . '/';
        return $url;
    }

    /**
    * Добавляет конечный slash если необходимо
    */
    public static function fix_url($url) {
        if (!preg_match("/\/$/i", $url)) {
            $url = $url . '/';
        }
        return $url;
    }

    /**
    * Возвращает базовый url сайта
    */
    public static function get_siteurl() {
        $site_url = Zend_Registry::getInstance()->configuration->site->url;
        return self::fix_url($site_url);
    }

    public static function get_admin_email() {
        $email = Zend_Registry::getInstance()->configuration->admin->email;
        return $email;
    }

    public static function get_broadcast_email() {
        $email = Zend_Registry::getInstance()->configuration->broadcast->email;
        return $email;
    }

    public static function get_config() {
        return Zend_Registry::getInstance()->configuration;
    }


}