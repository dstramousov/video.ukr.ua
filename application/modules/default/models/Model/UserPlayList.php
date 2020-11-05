<?php

/**
* PlayList основанный на DB хранении плейдиста дл€ зарегестрированные пользователей.
*/
class Model_UserPlayList extends Vida_Model {

    protected $_className = "DbTable_UserPlayList";

    /**
    * ”далить все записи тегов по идентификатору файла
    */
    public function deleteByFileId($file_id)
    {
        $table = $this->_getTable();
        $db = $table->getAdapter();
        $where = array(
            new Zend_Db_Expr($db->quoteIdentifier('file_id') . '=' . $db->quote($file_id))
        );
        return $table->delete($where);
    }

    /**
     * ¬озвращает информацию о текущем пользователе
     * @return Zend_DbTable_Row | null
     */
    protected function _getCurrentUserId() {
        $model_users = new Model_Users();
        $user = $model_users->fetchCurrentUser();
        if(!empty($user)) {
            return $user['id'];
        }
        return false;
    }
    
    protected function _getPlayList() {
        $pl = Vida_Helpers_SessionHelper::getParam('playlist');
        if(empty($pl)) {
            $pl = array();
        }
        return $pl;
    }
    protected function _setPlayList($playlist) {
        Vida_Helpers_SessionHelper::setParam('playlist', $playlist);
    }


    /**
    * «агрузить строку по идентификатору файла
    */
    public function fetchAllByUserId($user_id) {
        return $this->fetchAllByCol('user_id', $user_id);
    }
    
    /**
    * ƒобавл€ет новую запись playlist-а
    * 
    * @param  array $data 
    * @return int|string
    */
    public function save(array $data)  {
        if(($user_id = $this->_getCurrentUserId())) {   //пользователь авторизован
            $data['user_id'] = $user_id;
            //TODO: сделать проверку на наличие такой записи
            parent::save($data);
        } else { //анонимный пользователь
            $pl = $this->_getPlayList();
            if(!in_array($data['file_id'], $pl)) {
                $pl[] = $data['file_id'];
                $this->_setPlayList($pl);
            }
        }
    }
    
    /**
     * ”дал€ет запись из playlist-a
     */
    public function remove($data) {
        if(($user_id = $this->_getCurrentUserId())) {   //пользователь авторизован
            $data['user_id'] = $user_id;
            $table = $this->_getTable();
            $db = $table->getAdapter();
            $where = array(
                new Zend_Db_Expr($db->quoteIdentifier('user_id') . '=' . $db->quote($data['user_id'])),
                new Zend_Db_Expr($db->quoteIdentifier('file_id') . '=' . $db->quote($data['file_id']))
            );
            return $table->delete($where);
        } else { //анонимный пользователь
            $pl = $this->_getPlayList();
            $tmp = array();
            foreach($pl as $i) {
                if($i != $data['file_id']) {
                    $tmp[] = $i;
                }
            }
            $this->_setPlayList($tmp);
            unset($pl);

            /*
            if(!($key = array_search($data['file_id'], $pl))) {
                unset($pl[$key]);
                $this->_setPlayList($pl);
            }
            */
        }
        return true;
    }

    /**
     * ¬овзращает playlist дл€ пользовател€
     * @return array
     */
    public function getPlayList() {
        $pl = array();
        if(($user_id = $this->_getCurrentUserId())) {
            $rows = $this->fetchAllByUserId($user_id);
            if(!empty($rows)) {
                foreach($rows as $row) {
                    $pl[] = $row['file_id'];
                }
            }
        } else {
            $pl = $this->_getPlayList();
        }
        return $pl;
    }
}