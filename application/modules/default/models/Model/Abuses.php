<?php

class Model_Abuses extends Vida_Model
{
    protected $_className = "DbTable_Abuses";
    
    /**
     * Fetch an individual entry
     * 
     * @param  string $login 
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByFileId($file_id)
    {
        $row = $this->fetchRowByCol('file_id', $file_id, true);
        return $row;
    }

    /**
     * Удаляет все жалобы по файлу
     * @param integer $file_id
     * @return none
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
    * Select all user files
    * 
    * @param  string $file_id file
    * @return null|Zend_Db_Table_Row_Abstract
    */
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

}