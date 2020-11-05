<?php
/*

CREATE TABLE  `upload_db`.`foldersfile` (
`id` INT NOT NULL AUTO_INCREMENT ,
`folder_id` INT NOT NULL ,
`file_id` INT NOT NULL ,
PRIMARY KEY (  `id` ) ,
INDEX (  `folder_id` ,  `file_id` )
) ENGINE = INNODB
*/

class Model_FoldersFile extends Vida_Model
{
    protected $_className = "DbTable_FoldersFile";


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

    public function deleteByFolderId($folder_id)
    {
        $table = $this->_getTable();
        $db = $table->getAdapter();
        $where = array(
            new Zend_Db_Expr($db->quoteIdentifier('folder_id') . '=' . $db->quote($folder_id))
        );
        return $table->delete($where);
    }


    /**
    * Загрузить строку по идентификатору файла
    */
    public function fetchByFileId($file_id)
    {
        $row = $this->fetchRowByCol('file_id', $file_id);
        return $row;
    }



}