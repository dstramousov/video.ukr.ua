<?php

/**
* PlayList ���������� �� DB �������� ��������� ��� ������������������ �������������.
*/
class Model_UserPlayList extends Vida_Model {

    protected $_className = "DbTable_UserPlayList";

    /**
    * ������� ��� ������ ����� �� �������������� �����
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
     * ���������� ���������� � ������� ������������
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
    * ��������� ������ �� �������������� �����
    */
    public function fetchAllByUserId($user_id) {
        return $this->fetchAllByCol('user_id', $user_id);
    }
    
    /**
    * ��������� ����� ������ playlist-�
    * 
    * @param  array $data 
    * @return int|string
    */
    public function save(array $data)  {
        if(($user_id = $this->_getCurrentUserId())) {   //������������ �����������
            $data['user_id'] = $user_id;
            //TODO: ������� �������� �� ������� ����� ������
            parent::save($data);
        } else { //��������� ������������
            $pl = $this->_getPlayList();
            if(!in_array($data['file_id'], $pl)) {
                $pl[] = $data['file_id'];
                $this->_setPlayList($pl);
            }
        }
    }
    
    /**
     * ������� ������ �� playlist-a
     */
    public function remove($data) {
        if(($user_id = $this->_getCurrentUserId())) {   //������������ �����������
            $data['user_id'] = $user_id;
            $table = $this->_getTable();
            $db = $table->getAdapter();
            $where = array(
                new Zend_Db_Expr($db->quoteIdentifier('user_id') . '=' . $db->quote($data['user_id'])),
                new Zend_Db_Expr($db->quoteIdentifier('file_id') . '=' . $db->quote($data['file_id']))
            );
            return $table->delete($where);
        } else { //��������� ������������
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
     * ���������� playlist ��� ������������
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