<?php

class Model_Messages extends Vida_Model
{
    protected $_className = "DbTable_Messages";

    const ST_MESSAGE_READED		= 0;	// Сообщение было прочитанно пользователем 
    const ST_MESSAGE_NEW		= 1;	// Новое сообщение (НЕ было прочитанно пользователем)

    const ST_PRIORITY_HIGHT		= 1;
    const ST_PRIORITY_NORMAL	= 2;
    const ST_PRIORITY_LOW		= 3;

	/**
	 * Создает новое сообщение в системе
	 * @param array $data
	 */
	public function save(array $data) {
		$data['status'] = self::ST_MESSAGE_NEW;
		if(!array_key_exists('priority', $data)) {
			$data['priority'] = self::ST_PRIORITY_NORMAL;
		}
		$data['created'] = Vida_Helpers_DateHelper::today();
		parent::save($data);
	}


    /**
    * Проверяет возможность удаления сообщения.
    */
    public function isPossibleDelete($message_id, $user_id)
    {
    	$ret =  false;

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ms' => 'messages'), array('ms.user_id'))
            ->where('ms.id=?', $message_id);

        $row = Vida_Helpers_DB::fetchRow(null, $select);

        if($row['user_id'] == $user_id){ $ret = true; }

    	return $ret;
    }



    /**
    * Удалить сообщение по ID
    */
    public function deleteByMessageId($message_id)
    {
        $table = $this->_getTable();
        $db = $table->getAdapter();
        $where = array(
            new Zend_Db_Expr($db->quoteIdentifier('id') . '=' . $db->quote($message_id))
        );

        return $table->delete($where);
    }

    /**
     * Пoлучить все сообщения для конкретного пользователя
     * @param integer $user_id 
     * @param $new_message_sight true/false  Признак того что вернуть только непрочитанные сообщения 
     * @return array {0} - system messages / {1} - users messages
     */
    public function getCountUsersMessages($user_id)
	{
		
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ms' => 'messages'), array('count(*) as count'))
            ->where('ms.status=?', self::ST_MESSAGE_NEW)
            ->where('ms.user_id=?', $user_id)
			->order('ms.priority DESC');

        $rows = Vida_Helpers_DB::fetchRow(null, $select);

		return $rows['count'];
	}

    /**
     * Пoлучить все сообщения для конкретного пользователя
     * @param integer $user_id 
     * @param $new_message_sight true/false  Признак того что вернуть только непрочитанные сообщения 
     * @return array
     */
    public function getUsersMessages($user_id, $new_message_sight=false)
	{

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ms' => 'messages'), array('ms.id', 'ms.status', 'ms.priority', 'ms.body', 'ms.user_id', 'ms.created'))
            ->where('ms.user_id=?', $user_id)
			->order('ms.created DESC');

		if($new_message_sight){
	        $select = $select->where('ms.status=?', self::ST_MESSAGE_NEW);
		}

        $rows = Vida_Helpers_DB::fetchAll(null, $select);

		// Пометить все вычитанные сообщения как прочтенные.
		$update_sql = 'UPDATE messages SET status=\''.self::ST_MESSAGE_READED.'\' WHERE user_id=\''.$user_id.'\''.' AND status=\''.self::ST_MESSAGE_NEW.'\'';
		Zend_Registry::getInstance()->dbAdapter->query($update_sql);

		return $rows;
    }

    

    
}