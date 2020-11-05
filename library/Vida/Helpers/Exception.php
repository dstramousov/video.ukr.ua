<?php

class Vida_Helpers_Exception
{
    protected static $_logger;

    protected static function _getLogger() {
        if(is_null(self::$_logger)) {
            self::$_logger = Zend_Registry::get('logger');
        }
        return self::$_logger;
    }

    protected static function _printBackTrace() {
        $backtrace = debug_backtrace();
        $str = '';
        for($i = 1; $i < count($backtrace); $i++) {
            $str .= $i . ': ' . $backtrace[$i]['file'] . '(' . $backtrace[$i]['line'] . ")";
            if($i < count($backtrace) - 1) {
                $str .= "\r\n";
            }
        }
        return $str;
    }
    /**
     * Функция форматирования логирования ошибок в системе
     * @param $exp Exception Обрабатываемый объект исключения
     * @return none
     */
    public static function processException($exp, $priority = Zend_Log::ERR)
    {
       $logger = self::_getLogger();
       $message = sprintf("[Ошибка] Текст ошибки: \"%s\"\r\n\tСтэк вызова: \"%s\"", $exp->getMessage(), self::_printBackTrace());
       if( DAEMON_MODE && $priority != Zend_Log::DEBUG) {
            echo iconv("windows-1251", "cp866", $message);
       }
       $logger->log($message, $priority);
    }
}