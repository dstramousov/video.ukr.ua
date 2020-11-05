<?php

class Vida_Helpers_DB
{
    protected static function _getDefaultCacheMode($noCache) {
        if(!is_bool($noCache)) {
            $noCache = false;
            $auth = null;
            if( !DAEMON_MODE ) {
                $auth = Zend_Auth::getInstance();
            }
            $authorized = false;
            if(!empty($auth) && $auth->hasIdentity()) {
              $authorized = true;
            }
            if($authorized || DAEMON_MODE || Zend_Controller_Front::getInstance()->getRequest()->getModuleName() != Zend_Controller_Front::getInstance()->getDefaultModule()) {
                $noCache = true;
            }
        }
        return $noCache;
    }
    
    /**
     * Выполняет fetchAll для указанной страницы с кэшированием
     * @param Zend_Db_Table_Select|string $select
     * @param Zend_Db_Table $table
     * @return string
     */
    public static function fetchAll($table, $select, $noCache = null)
    {
        $noCache = self::_getDefaultCacheMode($noCache);
        
        $hash = self::_getHash($select);
        $result = null;
        // проверка, есть ли уже запись в кэше:
        if($noCache || !$result = self::getCache()->load($hash)) {
            if(is_null($table)) {
                $result = Zend_Registry::getInstance()->dbAdapter->fetchAll($select);
            } else {
                $result = $table->fetchAll($select);
                if(!is_null($result)) {
                    $result = $result->toArray();
                }
            }
            if($result != null && !$noCache) {
                self::getCache()->save($result, $hash);
            }
        }
        return $result;
    }

    /**
     * Выполняет fetchAll для указанной страницы с кэшированием
     * @param Zend_Db_Table_Select|string $select
     * @param Zend_Db_Table $table
     * @return string
     */
    public static function fetchRow($table, $select, $noCache = null)
    {
        $noCache = self::_getDefaultCacheMode($noCache);

        $hash = self::_getHash($select);
        // проверка, есть ли уже запись в кэше:
        if($noCache || !$result = self::getCache()->load($hash)) {
            if(is_null($table)) {
                $result = Zend_Registry::getInstance()->dbAdapter->fetchRow($select);
            } else {
                $result = $table->fetchRow($select);
                if(!is_null($result)) {
                    $result = $result->toArray();
                }
            }
            if($result != null && !$noCache) {
                self::getCache()->save($result, $hash);
            }
        }
        return $result;
    }

    public static function getCache() {
       return Zend_Registry::get('cache');
    }

    public static function clearCache() {
       // удаление всех записей
       self::getCache()->clean(Zend_Cache::CLEANING_MODE_ALL);
    }
     
    /**
     * Вычисляет Hash запроса для хранения в кэше
     * @param Zend_Db_Table_Select|string $select
     * @return string
     */
    protected static function _getHash($select) {
        if( ($select instanceof Zend_Db_Table_Select)) {
            $sql = $select->__toString();
        } else {
            $sql = $select;
        }

        return md5($sql);
    }
}