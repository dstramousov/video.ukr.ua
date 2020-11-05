<?php

class Vida_SSH
{
    var $ip          = '';
    var $port        = 22;
    var $user        = '';
    var $pass        = '';
  
    var $pubkey = '';
    var $prvkey = '';
  
    var $secret      = '';
  
    protected $link = null;
    protected $errors = array();
    protected $timeout;
    
    public function getErrors() {
        return $this->errors;    
    }
    
    public function __construct($options) 
    {
        $this->errors = array();

        if(array_key_exists('secret', $options)) {
            $this->secret = $options['secret'];
        } else {
            $this->secret = '';
        }

        if(array_key_exists('port', $options)) {
            $this->port = $options['port'];
        } else {
            $this->port = 22;
        }

        if(array_key_exists('ip', $options)) {
            $this->ip = $options['ip'];
        } else {
            throw new Zend_Exception("\"ip\" is required parameter");
        }

        if(array_key_exists('user', $options)) {
            $this->user = $options['user'];
        } else {
            throw new Zend_Exception("\"user\" is required parameter");
        }

        if(array_key_exists('pass', $options)) {
            $this->pass = $options['pass'];
        } else {
            $this->pass = '';
        }
        
        if(array_key_exists('timeout', $options)) {
            $this->timeout = $options['timeout'];
        } else {
            $this->timeout = 15;
        }
        
        if(array_key_exists('keys', $options)) {
            $keys = Vida_Helpers_File::fix_path($options['keys']);
            $this->pubkey = $keys . 'id_rsa.pub';
            $this->prvkey = $keys . 'id_rsa';
            if(!@file_exists($this->prvkey) || !@file_exists($this->pubkey)) {
                throw new Zend_Exception(sptintf("SSH keys not found in \"%s\"", $keys));
            }
        } else {
            throw new Zend_Exception("\"keys\" storage is required parameter");
        }
    }    

    public function connect() {
        $this->_get_link();
        return $this->link;
    }

    /**
     * Устанавливает SSH соединение с указанным сервером
     */
    protected function _get_link() {
       if(!empty($this->link)) {
          return true;
       }
  
       //Check if possible to use ssh2 functions.
       if ( ! extension_loaded('ssh2') ) {
              $this->errors[] = 'The ssh2 PHP extension is not available';
              return false;
       }
  
       if ( ! version_compare(phpversion(), '5', '>=') ) {
              $this->errors[] = 'The ssh2 PHP extension is available, however requires PHP 5+';
              return false;
       }
  
       $this->link = ssh2_connect($this->ip, $this->port /*, array('hostkey'=>'ssh-rsa') */);
       $res = !!$this->link && !!ssh2_auth_pubkey_file($this->link, $this->user, $this->pubkey, $this->prvkey, $this->secret);
       return $res;
    }

    /**
     * Выполняет удаленную команду на сервере
     * @param string $command Текст команды
     * @param boolean $returnbool Результат выполнения команды boolean
     * @return boolean | usual
     */
    public function _exec( $command, $returnbool = false) {
       if ( ! $this->_get_link() )
          return false;
  
       if ( ! ($stream = ssh2_exec($this->link, $command)) ) {
          $this->errors[] = sprintf(__('Unable to perform command: %s'), $command);
           return false;
       } else {
          stream_set_blocking( $stream, true );
          stream_set_timeout( $stream, $this->timeout );
          $data = stream_get_contents($stream);
          
          if ( $returnbool )
             return '' != trim($data);
          else
             return $data;
       }
       return true;
    }

}