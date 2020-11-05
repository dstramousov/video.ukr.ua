<?php

class Model_FileTags extends Vida_Model
{
    protected $_className = "DbTable_FileTags";
    
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
     * Fetch all tags of file
     * 
     * @param  string $file_id file
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByFileId($file_id)
    {
        $table = $this->_getTable();
        $select = $table->select()
            ->where('file_id=?', $file_id);
        $rows = Vida_Helpers_DB::fetchAll($table, $select);
        return $rows;
    }

    /**
     * ‘ормирует select дл€ выборки файлов по тегу
     */
    public function getFilesByTag($tag_id) {
        $table = $this->_getTable();
        $name = $table->info(Zend_Db_Table::NAME);
        
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ft' => $name), array('ft.file_id'))
            ->joinInner(array('f' => 'files'), 'f.id = ft.file_id', array('f.title', 'f.key'))
            ->where('ft.tag_id=?', $tag_id);
        return $select;
    }

    /**
     * ƒелает выборку попул€рных тегов
     */
    public function fetchTagClouds($limit) {
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('f' => 'files'), array('count(f.id) as count'))
            ->joinInner(array('ft' => 'filetags'), 'ft.file_id = f.id', array('ft.tag_id'))
            ->group('ft.tag_id')
            ->order('count(f.id) DESC');
        if(!empty($limit)) {
            $select = $select->limit($limit);
        }
        
        //dump($select->__toString());
        
        $rows = Vida_Helpers_DB::fetchAll(null, $select);
        return $rows;
    /*
    SELECT ft.tag_id, COUNT(f.requested) 
        FROM files f 
        INNER JOIN filetags ft ON ft.file_id = f.id
    GROUP BY ft.tag_id
    */
    }

}