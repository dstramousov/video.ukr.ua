<?php

require_once('protocolWrapper.php');

class Model_Users extends Vida_Model
{
    protected $_className = "DbTable_Users";
    protected static $_protocolWrapper = null;
    protected static $_self = null;
    
    protected static function instance() {
        if(self::$_self == null) {
            self::$_self =  new Model_Users();
        }
        return self::$_self;
    }

    /**
     * Возвращает security wrapper
     */
    protected static function _getSecurityWrapper() {
        if(self::$_protocolWrapper == null) {
            $security_prc = Zend_Registry::getInstance()->configuration->security->rpc;
            if(empty($security_prc)) {
               throw new Zend_Controller_Dispatcher_Exception('Security RPC not defined');
            }
            self::$_protocolWrapper = new protocolWrapper($security_prc); 
        }
        return self::$_protocolWrapper;
    }
    
    const ACTIVE = 1;
    const DISABLED = 0;
    const SUSPENDED = 2;
    const SECURITY_COOKIE_NAME = 'LG.Portal.SessionID';
    const SECURITY_COOKIE_NAME_PHP = 'LG_Portal_SessionID';

//    const DOMAIN = '.local-global.local';
    const DOMAIN = '.ukr.local';
    //const DOMAIN = '.ukr.ua';
    
    /**
     * Удаление записи по первичному ключу
     */
    public function deleteById($entry_id)
    {
        return parent::deleteById($entry_id);
    }
    
    /**
    * Save a new entry
    * 
    * @param  array $data 
    * @return int|string
    */
    public function save(array $data)
    {
        //$data['password'] = md5($data['password']);
        $data['login'] = strtolower($data['username']);
        $data['email'] = strtolower($data['email']);
        $data['state'] = self::ACTIVE;

        $ret = parent::save($data);
        
        //создание папки по умолчанию
        $userstree_model = new Model_UsersTree();
        $userstree = array();
        $userstree['parentid'] = 0;
        $userstree['order'] = 1;
        $userstree['name'] = "Новая папка";
        $userstree['userid'] = $ret;
        $userstree_model->save($userstree);
        unset($userstree_model);

        return $ret;
    }

    public function select($orderBy = null)
    {
        if(null === $orderBy) {
            $orderBy = "id";
        }
        $table = $this->_getTable();
        $select = $table->select();
        $select = $select->order(new Zend_Db_Expr($orderBy));
        return  $select;
    }


    /**
    * Обновляет сведения о пользователе
    * 
    * @param  array $data 
    * @return int|string
    */
    public function update(array $data)
    {
        if(array_key_exists('password', $data)) {
            //$data['password'] = md5($data['password']);
        }
        
        return parent::update($data);
    }

    /**
     * Fetch an individual entry
     * 
     * @param  string $login 
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByLogin($login)
    {
        $row = $this->fetchRowByCol('login', $login, true);
        return $row;
    }

    /**
     * Fetch entries by state
     * 
     * @param  int $state state of records to fetch
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByState($state)
    {
        $rows = $this->fetchAllByCol('state', $state);
        return $rows;
    }

    /**
     * Fetch an individual entry
     * 
     * @param  string $email
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByEmail($email)
    {
        $row = $this->fetchRowByCol('email', $email);
        return $row;
    }

    protected static function _convertToInt($ext_data) {
//        dump($ext_data);
        $data = array();
        $data['username'] = $ext_data['_login'];
        $data['password'] = $ext_data['_password'];
        $data['email'] = $ext_data['_email'];
        $data['state'] = $ext_data['_disabled'] == 'true' ? self::DISABLED : self::ACTIVE;
        $data['lname'] = $ext_data['_lastName'];
        $data['fname'] = $ext_data['_firstName'];
        $data['ext_id'] = $ext_data['_id'];
        $data['sex'] = $ext_data['_male'] == true ? 1: 0;
        
        $bdate = strtotime($ext_data['_birthDate']);
        
        $data['bday'] = date('j', $bdate);
        $data['bmonth'] = date('n', $bdate);
        $data['byear'] = date('Y', $bdate);
        
        return $data; 
    }

    protected static function _convertIntToExt($data) {
        $ext_data = array();
        if(array_key_exists('login', $data)) {
            $ext_data['_login'] = $data['login'];
        } else {
            $ext_data['_login'] = $data['username'];
        }
        $ext_data['_password'] = $data['password'];
        $ext_data['_email'] = $data['email'];
        $ext_data['_email2'] = $data['email'];
        $ext_data['_disabled'] = $data['state'] == self::DISABLED ? 'true' : 'false';
        if(array_key_exists('lname', $data)) {
            $ext_data['_lastName'] = $data['lname'];
        }
        if(array_key_exists('fname', $data)) {
            $ext_data['_firstName'] = $data['fname'];
        }
        if(array_key_exists('username', $data)) {
            $ext_data['_nickName'] = $data['username'];
        }
        $ext_data['_country'] = 'uk';
        $ext_data['_region'] = '';
        $ext_data['_region'] = '';
        $ext_data['_city'] = '';
        $ext_data['_zipCode'] = '';
        $ext_data['_address'] = '';
        $ext_data['_homePhone'] = '';
        $ext_data['_mobilePhone'] = '';
        $ext_data['_workPhone'] = '';
        $ext_data['_secretQuestion'] = '';
        $ext_data['_secretAnswer'] = '';
        $ext_data['_admin'] = 'false';
        $ext_data['_uploadActive'] = 'false';
        return $ext_data; 
    }

    /**
     * 
     */
    protected static function _upload_authenticate($values) {
        $res = false;
        $db = Zend_Registry::getInstance()->dbAdapter;
        
        if(array_key_exists('password', $values) && array_key_exists('username', $values)) {
            $adapter = new Zend_Auth_Adapter_DbTable($db);
            $adapter
                ->setIdentity($values['username'])
                ->setCredential($values['password'])
                ->setTableName('users')
                ->setIdentityColumn('login')
                ->setCredentialColumn('password')
                ->setCredentialTreatment('? AND state=1');
    //            ->setCredentialTreatment('MD5(?) AND state=1');
            $auth    = Zend_Auth::getInstance();
            $result  = $auth->authenticate($adapter);
            $res = $result->isValid();
        }
        return $res;
    }

    /**
     *
     */
    public static function logout() {
        //$cookie = Zend_Http_Cookie::fromString(urlencode(self::SECURITY_COOKIE_NAME) . '=; domain=.local-global.local; path=/; expires=' . time() + 7200);
        $res = setcookie(urlencode(self::SECURITY_COOKIE_NAME), '', 0, '/', self::DOMAIN, false);
        //$res = setcookie(urlencode(self::SECURITY_COOKIE_NAME), '', 0, '/', '', false);
    }

    /**
     * 
     */
    public static function authorize($values) {
        $rpc = self::_getSecurityWrapper();
        if(array_key_exists('password', $values) && array_key_exists('username', $values)) {
            $params = array(
                            'login'     => $values['username'],
                            'password'  => $values['password'],
                           );
            $res = $rpc->loginWrapper($params);
            if(is_array($res) && array_key_exists('sessionid', $res)) {
                //$cookie = Zend_Http_Cookie::fromString(self::SECURITY_COOKIE_NAME . '='. $res['sessionid'].'; domain=.local-global.local; path=/; expires=' . time() + 7200);
                setcookie(urlencode(self::SECURITY_COOKIE_NAME), $res['sessionid'], mktime() + 7200, '/', self::DOMAIN, false);
                return self::authenticate($res['sessionid']);
            }
        }
        return false;
    }

    public static function update_profile($values) {
        $rpc = self::_getSecurityWrapper();
        $rpc->updateUserWrapper($values);
    }

    public static function profile() {
        $res = self::authenticate();
        
        if(is_array($res)) {
            $res = self::_convertToInt($res);
        } else {
            Zend_Auth::getInstance()->clearIdentity();
            Model_Users::logout();
        }
        return $res;
    }

    public static function register($values) {
        //dump($values);
        $rpc = self::_getSecurityWrapper();
        $values['state'] = self::ACTIVE;
        $ext_data = self::_convertIntToExt($values);
        $ext_data['_birthDate'] = date("d.m.Y", strtotime($values['birthday']));
        $ext_data['_male'] = $values['sex'] == 1 ? 'true': 'false';
        $res = $rpc->createUserWrapper($ext_data);
        if(is_string($res)) {
            $res = json_decode($res, true);
            if(!empty($res) && array_key_exists('result', $res) && $res['result']) {
                $values['username'] = $values['login'];
                self::authorize($values);
                return true; 
            }        
        }
        return false;
    }
    
    /**
     * 
     */
    public static function authenticate($sessionId = null) {
        $rpc = self::_getSecurityWrapper();

        $cookie = str_replace('.', '_', self::SECURITY_COOKIE_NAME);
        
        if(empty($sessionId) && array_key_exists($cookie, $_COOKIE) && null !==  $_COOKIE[$cookie]) {
            $sessionId = $_COOKIE[$cookie];
        }
        
        if(empty($sessionId)) {
            return false;
        }
        
        $params = array(
            'sessionid'=>$sessionId,
        );
        
        $res = $rpc->isAuthorizedWrapper($params);
        
        if(is_array($res)) {
            $self = self::instance();
            $user = $self->fetchRowByCol('ext_id', $res['_id'], true);
            
            //синхронизировать данные
            if(empty($user)) {
                $data = self::_convertToInt($res);
                $self->save($data);
            } else {
                $data = self::_convertToInt($res);
                $data['id'] = $user['id'];
                $user = $data;
                $self->update($data);
            }
            self::_upload_authenticate($data);
            $res['sessionId'] = $sessionId;
        } else {
            $res = false;
        }
        return $res;
    }

    /**
     * Возвращает данные о текущем пользователе
     * 
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchCurrentUser()
    {
        self::profile();
        
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
            return $this->fetchByLogin($username);
        } else {
            return null;
        }
    }

}
