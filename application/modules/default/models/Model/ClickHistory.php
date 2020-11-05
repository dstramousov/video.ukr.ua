<?php

class Model_ClickHistory extends Vida_Model
{
    protected $_className = "DbTable_ClickHistory";

    /**
     * Удаляет всю статистику по файлу
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
     * Save/Update для $_file_id кол-во просмотров
     * 
     * @param  int $file_id file
     * @return int (кол-во просмотров после Save/Update)
     */
    public function update($_file_id){$this->save($_file_id);}
    public function save($_file_id)
    {

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ch' => 'clickhistory'), array('ch.id', 'ch.day', 'ch.count_see_it', 'ch.file_id'))
            ->where('ch.file_id=?', $_file_id)
			->where('ch.day=?', Vida_Helpers_DateHelper::short_today());

        $rows = Vida_Helpers_DB::fetchRow(null, $select);

        $data = array();
        $data['day']		= Vida_Helpers_DateHelper::short_today();
        $data['file_id']	= $_file_id;
        if($rows){ 
        	$data['id'] = $rows['id'];
        	$data['count_see_it'] = $rows['count_see_it']+1;
	        parent::update($data);
        } else { 
        	$data['count_see_it'] = 1; 
	        parent::save($data);
        }

        return $data['count_see_it'];
    }


    /**
     * Fetch count see it value for current day 
     * 
     * @param  int $file_id file, $day - date (2009-06-03)
     * @return null|int
     */
    public function fetchCSIByDate($_file_id, $_day)
    {
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ch' => 'clickhistory'), array('ch.day', 'ch.count_see_it', 'ch.file_id'))
            ->where('ch.file_id=?', $_file_id)
			->where('ch.day=?', $_day);

        $rows = Vida_Helpers_DB::fetchRow(null, $select);
        return $rows[0]['count_see_it'];
    }


    
    /**
     * Fetch count see it value for current day 
     * 
     * @param  int $file_id file
     * @return int
     */
    public function fetchTotalCSIByFileID($_file_id)
    {	
    	$ret = 0;

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ch' => 'clickhistory'), array('SUM(ch.count_see_it) as total'))
            ->where('ch.file_id=?', $_file_id);

        $rows = Vida_Helpers_DB::fetchRow(null, $select);
        if($rows['total']){$ret = $rows['total'];}
        return number_format($ret);
    }


    /**
     * Fetch $_count_items records for some interval
     * 
     * @param  type of limit (see constants.php), int $_count_items
     * @return null|array
     */
    public function fetchByInterval($_type, $_count_items)
    {
        $select = Zend_Registry::getInstance()->dbAdapter->select();

        switch ($_type) {

            case ST_REQUEST_TOP_TYPE_TODAY:

		        $select = $select
		            ->from(array('ch' => 'clickhistory'), array('ch.count_see_it', 'ch.file_id'))
		            ->where('DAY = CURDATE()')
		            ->order('count_see_it DESC')
		            ->limit($_count_items);

                break;

            case ST_REQUEST_TOP_TYPE_WEEK:

		        $select = $select
		            ->from(array('ch' => 'clickhistory'), array('SUM(ch.count_see_it) as sum_csi', 'ch.file_id'))
		            ->where('DAY >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')
		            ->order('SUM(count_see_it) desc')
		            ->group('ch.file_id')
		            ->limit($_count_items);

                break;

            case ST_REQUEST_TOP_TYPE_MONTH:

		        $select = $select
		            ->from(array('ch' => 'clickhistory'), array('SUM(ch.count_see_it) as sum_csi', 'ch.file_id'))
		            ->where('DAY >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
		            ->order('SUM(count_see_it) desc')
		            ->group('ch.file_id')
		            ->limit($_count_items);

                break;

            case ST_REQUEST_TOP_TYPE_ALL:

		        $select = $select
		            ->from(array('ch' => 'clickhistory'), array('SUM(ch.count_see_it) as sum_csi', 'ch.file_id'))
		            ->order('sum_csi desc')
		            ->group('ch.file_id')
		            ->limit($_count_items);

                break;
        }

        return Vida_Helpers_DB::fetchAll(null, $select, true);
    }

}