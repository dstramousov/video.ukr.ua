<?php

class Model_FileRate extends Vida_Model
{
    protected $_className = "DbTable_FileRate";

    /**
     * Выборка файлов с максимальным рейтингом.
     * 
     * @param  type of limit (see constants.php), int $_count_items
     * @return null|array
     */
    public function fetchByInterval($_type, $_count_items)
    {
    	$ret = array();

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('fr' => 'filerate'), array('fr.file_id', 'SUM(fr.rateval) as summrateval', 'COUNT(fr.id) as count_records'))
            ->group('fr.file_id')
            ->order('summrateval DESC')
            ->limit($_count_items);

        switch ($_type) {

            case ST_REQUEST_TOP_TYPE_TODAY:

		        $select = $select->where('DAY = CURDATE()');
                break;

            case ST_REQUEST_TOP_TYPE_WEEK:

		        $select = $select->where('DAY >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
                break;

            case ST_REQUEST_TOP_TYPE_MONTH:

		        $select = $select->where('DAY >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
                break;

            case ST_REQUEST_TOP_TYPE_ALL:
                break;
        }

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);
		foreach($rows as $iterator=>$row){
			$row['avgval'] = $this->round_to($row['summrateval']/$row['count_records'], CONST_RATE_GRADATION_VAL);
			array_push($ret, $row);
		}

        return $ret;
    }

    /**
     * set rate 
     * 
     * @param  int $file_id, int $user_id
     * @return null
     */
    public function setRateByFileID($_file_id, $_user_id, $_rate_val)
    {
    	if($this->isPosibleRated($_file_id, $_user_id) && CONST_RATE_MIN_VAL <= $_rate_val && $_rate_val <= CONST_RATE_MAX_VAL){
			$data = array();

			$data['user_id'] = $_user_id;
			$data['file_id'] = $_file_id;
			$data['rateval'] = $_rate_val;

			$data['day'] = Vida_Helpers_DateHelper::short_today();

	        parent::save($data);
    	}
    }
                                                                                   
    /**
     * check posibility rated (Один пользователь может поставить рейт только один раз).
     * 
     * @param  int $file_id, int $user_id
     * @return true|false
     */
    public function isPosibleRated($_file_id, $_user_id)
    {
    	$ret = false;

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('fr' => 'filerate'), array('fr.id'))
            ->where('fr.user_id=?', $_user_id)
            ->where('fr.file_id=?', $_file_id);
    	
        $rows = Vida_Helpers_DB::fetchRow(null, $select, null);

        if(!$rows){$ret = true;}
        return $ret;
    }

    /**
     * Fetch total rate
     * 
     * @param  int $file_id file
     * @return int
     */
    public function getRateByFileID($_file_id)
    {           
    	$ret = 0;

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('fr' => 'filerate'), array('SUM(fr.rateval) as total', 'COUNT(fr.rateval) as count'))
            ->where('fr.file_id=?', $_file_id);

        $rows = Vida_Helpers_DB::fetchRow(null, $select, null);
        if($rows['total']){ $ret = $this->round_to($rows['total']/$rows['count'], CONST_RATE_GRADATION_VAL); }
        return $ret;
    }


    /**
     * Округление ДО десятичной составляющей
     * 
     * @param  float $number: число для округления,  $increments: десятичная составляющая (например 0.5 будет округлять до значений с шагом 0.5) 
     * @return int
     */
	private function round_to($number, $increments) {

		$increments = 1 / $increments;
		return (round($number * $increments) / $increments);
	}


}