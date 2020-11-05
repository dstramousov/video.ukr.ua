<?php

class Vida_Model
{
    /** Instance of application logger */
    protected $_logger;

    /** DbTable class name */
    protected $_className;

    /** DbTable object */
    protected $_table;


    /**
     * Retrieve table object
     * 
     * @return DbTable_Feeds
     */
    protected function _getTable()
    {
        if (null === $this->_table) {
            $this->_table = new $this->_className;
        }
        return $this->_table;
    }


    /**
    * Constructor
    *
    * @return void
    */
    public function __construct()
    {
        $this->_logger = Zend_Registry::get('logger');
    }

    /**
    * Save a new entry
    * 
    * @param  array $data 
    * @return int|string
    */
    public function save(array $data)
    {
        $table  = $this->_getTable();
        $fields = $table->info(Zend_Db_Table_Abstract::COLS);
        
        foreach ($data as $field => $value) {
            if (!in_array($field, $fields)) {
                unset($data[$field]);
            }
        }
        
        return $table->insert($data);
    }

    /**
    * Update existen entry
    * 
    * @param  array $data 
    * @return int|string
    */
    public function update(array $data)
    {
        $table  = $this->_getTable();
        $fields = $table->info(Zend_Db_Table_Abstract::COLS);

        foreach ($data as $field => $value) {
            if (!in_array($field, $fields)) {
                unset($data[$field]);
            }
        }
        
        $where = $table->getAdapter()->quoteInto('id = ?', $data['id']);
        
        return $table->update($data, $where);
    }

    /**
     * Delete entry by id
     */
    public function deleteById($entry_id)
    {
        $table = $this->_getTable();
        $table->delete('id=' . $entry_id);
    }

    /**
     * Fetch all feeds
     * 
     * @param  integer $login 
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchAll($order = null, $limit = null)
    {
        $table = $this->_getTable();
        $select = $table->select();

        if(!is_null($order)) {
          $select = $select->order($order);  
        }
        if(!is_null($limit)) {
          $select = $select->limit($limit);  
        }
        
        $rows = Vida_Helpers_DB::fetchAll($table, $select);
        
        return $rows;
    }

    /**
    * Return select object for table
    * @param none
    * @return none
    */
    public function getSelect() {
        $table = $this->_getTable();
        $select = $table->select();
        return $select;
    }

    /**
     * Fetch entry by Id
     * 
     * @param  string $login 
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchById($entry_id, $noCache=null)
    {
        return $this->fetchRowByCol('id', $entry_id, $noCache);
    }

    /**
     * Выборки строки по значению колонки
     *
     */    
    public function fetchAllByCol($column, $value, $noCache = null)
    {
        $table = $this->_getTable();
        $db = $table->getAdapter();

        $select = $table->select()->where($db->quoteIdentifier($column) . ' = '. $db->quote($value));

        return Vida_Helpers_DB::fetchAll($table, $select, $noCache);
    }

    /**
     * Выборки строки по значению колонки
     *
     */    
    public function fetchRowByCol($column, $value, $noCache = null)
    {
        $table = $this->_getTable();
        $db = $table->getAdapter();

        $select = $table->select()->where($db->quoteIdentifier($column) . ' = '. $db->quote($value));
        
        return Vida_Helpers_DB::fetchRow($table, $select, $noCache);
    }
    

}
