<?php

class Model_WorkersPool 
{
    protected $options = null;
    const MAX_PROC_NUM = 10;
    
    protected $errors = null;
    
    public function __construct() {
        $this->options = array();
        $this->options['keys'] = Vida_Helpers_Config::prepare(Zend_Registry::getInstance()->configuration->security->keys);
        $this->options['user'] = 'runner';
    }    

    /**
     * Выполняет команду на указанном сервере
     * @param string $ip
     * @param string $command
     */
    public function _exec($ip, $command) {
        $this->options['ip'] = $ip;
        $ssh = new Vida_SSH($this->options);
        $res = $ssh->_exec($command);
        $this->errors = $ssh->getErrors();
        return $res;
    }

    public function getErrors() {
        return $this->errors;    
    }
    
    /**
     * Копирует файл с локальной машины на удаленную
     * @param string $ip
     * @param string $l_file
     * @param string $r_file
     */
    public function cp_send($ip, $l_file, $r_file) {
        $this->options['ip'] = $ip;
        $ssh = new Vida_SSH($this->options);
        return ssh2_scp_send($ssh->connect(), $l_file, $r_file, 0644);
    }

    /**
     * Копирует файл с удаленной машины на локальную
     * @param string $ip
     * @param string $l_file
     * @param string $r_file
     */
    public function cp_recv($ip, $r_file, $l_file) {
        $this->options['ip'] = $ip;
        $ssh = new Vida_SSH($this->options);
        return ssh2_scp_recv($connection, $r_file, $l_file . '-test');
    }
    
    /**
     * Возвращает кол-во процессов
     * @param string $ip IP сервер
     * @return integer
     */
    protected function _getProcNum($ip) {
        $this->options['ip'] = $ip;
        $ssh = new Vida_SSH($this->options);
        $res = $ssh->_exec('~/./proc_num');
        if(is_numeric(trim($res))) {
            $res = (int) trim($res);
        } else {
            $res = -1;  //сервер вышел из строя или недоступен
        }
        return $res;
    }

    /**
     * Возвращает сервер для запуска процедуры конвертации
     * @param string $ip IP сервер
     * @return integer
     */
    public function getWorker() {
        $workers = Zend_Registry::getInstance()->configuration->workers->toArray();
        $min_pn = self::MAX_PROC_NUM;
        $min_ip = '';
        foreach($workers as $worker ) {
            $pn = $this->_getProcNum($worker);
            //var_dump($worker .'->'. $pn);
            if($pn == 0) {
                $min_ip = $worker;
                $min_pn = 0;
                break;
            }
            if($pn < self::MAX_PROC_NUM && $pn <= $min_pn) {
                $min_pn = $pn;
                $min_ip = $worker;
            }
        }
        if($min_ip != '') {
            return array('ip' => $min_ip, 'pn' => self::MAX_PROC_NUM - $min_pn);
        } else {
            return null;
        }
    }
    
}