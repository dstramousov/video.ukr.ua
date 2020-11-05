<?php

class Vida_Helpers_FeedHelper
{
    /**
     * ��������� RSS �����
     * @param  string       $url  ����� ����� ��� ��������
     * @return object             ������ feed ��� ���������
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
     * ������������ ������ �� utf-8 � ��������� ������ �����
     * @param  string       $val  ������ ������� ���������� �������������
     * @return string       ��������������� ������
     */
    public static function convert($val)
    {
        return iconv("utf-8", "windows-1251", $val);
    }

    /**
    * ������������ ���� ��� �������� � �������
     * @param  string       $link ����, ������� ���������� �������������
     * @return string       ��������������� ����
    */
    public static function prepare_link($link)
    {
        return strtolower($link);
    }

    /**
    * ��������� hash ����-� ��� �������� � �������
     * @param  string       $link 
     * @return string       Hash ����������� �����
    */
    public static function hash_link($link)
    {
        return md5(self::prepare_link($link));
    }

    /**
     * ������������ ���� �� ������� gmt
     * @param  string       $string  ���� � ������� gmt
     * @return date         ������ ���� � ����� ������� �����
     */
    public static function get_date_from_gmt($string) {
      return date('Y-m-d H:i:s', strtotime($string));
    }
    
}