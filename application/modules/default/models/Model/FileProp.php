<?php

class Model_FileProp extends Vida_Model
{
    protected $_className = "DbTable_FileProp";
    
    /**
    * Удалить все записи тегов по идентификатору файла
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
     * Fetch all tags of file
     * 
     * @param  string $file_id file
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByFileId($file_id)
    {
        $row = $this->fetchRowByCol('file_id', $file_id);
        return $row;
    }

}