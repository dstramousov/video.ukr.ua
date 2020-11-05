<?php

class Model_Tags extends Vida_Model
{
    protected $_className = "DbTable_Tags";
    
    /**
     * Удаление записи по первичному ключу
     */
    public function deleteById($entry_id)
    {
        return parent::deleteById($entry_id);
    }
    
    protected function _hash($tag) {
        $tag = md5(strtolower($tag));
        return $tag;
    }

    /**
     * Возвращает идентификатор тега в таблице. Если запись не существует, то создается новая запись.
     *
     * @param  string       $tag  Ключевое слово для поиска.
     * @param  bool         $insert  Вставить ключевое слово, если не найдено
     * @return int          Идентификатор ключевого слова.
     */
    public function getTagId($tag, $insert = true)
    {
        $tag_id = -1;
        $row = $this->fetchByTag($tag);
        if(empty($row) && $insert) {
            $data = array();
            $data['tag'] = $tag;
            $tag_id = $this->save($data);
        } else {
            $tag_id = $row['id'];
        }
        return $tag_id;
    }

    /**
    * Save a new entry
    * 
    * @param  array $data 
    * @return int|string
    */
    public function save(array $data)
    {
        $data['hash'] = $this->_hash($data['tag']);
        $data['tag'] = ucwords($data['tag']);
        
        return parent::save($data);
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
     * Fetch an individual entry
     * 
     * @param  string $login 
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByTag($tag)
    {
        $row = $this->fetchRowByCol('hash', $this->_hash($tag), true);
        return $row;
    }

}