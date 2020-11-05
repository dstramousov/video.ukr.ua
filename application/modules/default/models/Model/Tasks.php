<?php

class Model_Tasks extends Vida_Model
{
    protected $_className = "DbTable_Tasks";
	
	const CREATED = '0';
	const SCHEDULED = '1';
    
	/**
	 * Удаляет все задачи по файлу
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
     * Fetch all tasks by file_id
     * @param  string $file_id file
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchAllByState($state)
    {
        $rows = $this->fetchAllByCol('state', $state);
        return $rows;
    }

    /**
     * Fetch all tasks by file_id
     * @param  string $file_id file
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByFileId($file_id)
    {
        $row = $this->fetchRowByCol('file_id', $file_id);
        return $row;
    }

    /**
     * Создание новой задачи
     * @param array $data данные для создания
     */
    public function save(array $data) {
		if(array_key_exists('params', $data) && is_array($data['params'])) {
			$data['params'] = self::paramsEncode($data);
		}
		$data['created'] = mktime();
		$data['state'] = self::CREATED;
		
        return parent::save($data);
    }
	
	public static function paramsDecode($data) {
		$params = $data['params'];
		if(!isset($params)) {
			$params = array();
		} else {
			if(is_string($params)) {
				$params = unserialize($data['params']);
			}
		}
		return $params;
	}
	
	public static function paramsEncode($data) {
		$params = $data['params'];
		if(!isset($params)) {
			$params = array();
		}
		return serialize($params);
	}

	
    /**
     * Обновление данных о файле
     * @param array $data данные для обновления
     */
    public function update(array $data) {
		if(array_key_exists('params', $data) && is_array($data['params'])) {
			$data['params'] = self::paramsEncode($data);
		}
        parent::update($data);
    }

}