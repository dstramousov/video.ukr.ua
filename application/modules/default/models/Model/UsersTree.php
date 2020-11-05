<?php

/*

CREATE TABLE IF NOT EXISTS `usersfolder` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `parentid` int(11) default NULL,
  `name` varchar(50) NOT NULL,
  `order` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

*/


class Model_UsersTree extends Vida_Model
{
    protected $_className = "DbTable_UsersTree";
    
    /**
     * Удаление записи по первичному ключу
     */
    public function deleteById($entry_id)
    {
		$foldersfile = new Model_FoldersFile();
		$foldersfile->deleteByFolderId($entry_id);
		
        return parent::deleteById($entry_id);
    }
    
    /**
    * Save a new users folder
    * 
    * @param  array $data 
    * @return int|string
    */
    public function save(array $data)
    {    
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
    * Обновляет сведения о папке
    * 
    * @param  array $data 
    * @return int|string
    */
    public function update(array $data)
    {        
        return parent::update($data);
    }

    /**
     * Fetch default users tree.
     * 
     * @return false|folders id 
     */
    public function getDefaultFolder($_customer_id) {

    	$ret = 0;

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('uf' => 'usersfolder'), array('uf.id', 'uf.userid', 'uf.parentid', 'uf.name', 'uf.order'))
			->where('userid=?', $_customer_id)
            ->order('uf.order ASC')
            ->limit(1);

        $rows = Vida_Helpers_DB::fetchAll(null, $select);
                                                      
        if($rows){ 
        	$ret = $rows[0]['id']; 
        }

        return $ret;
    }

    /**
     * Fetch all files from some users folder.
     * @param  int $_folder_id OR NULL
     * @return false|folders id 
     */
    public function fetchAllContentFromFolder($_customer_id, $_folder_id = null) {

    	$ret = array();

    	if(!$_folder_id) {
    		$_folder_id = $this->getDefaultFolder($_customer_id);
    	}

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ff' => 'foldersfile'), array('ff.id', 'ff.folder_id', 'ff.file_id'))
			->where('folder_id=?', $_folder_id)
            ->order('ff.id DESC');

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);
        foreach($rows as $row){

			$file_model = new Model_Files();
			$file_info = $file_model->fetchInfoByID($row['file_id']);

			array_push($ret, $file_info);
        }

        return $ret;
    }

    /**
     * Check if exist folder 
     * 
     * @param  int $folderid 
     * @return true|false 
     */
    public function ifFolderExist($_folder_id)
    {

    }

    /**
     * Create new folder 
     * 
     * @param  string $login 
     * @return true|string $err 
     */
    public function deleteFolder($_folder_id, $_customer_id)
    {

    	if(!$this->isUsersFolder($_folder_id, $_customer_id)){
			throw new Zend_Controller_Dispatcher_Exception('Security Error');
    	}

    	$this->deleteById($_folder_id);
    }

    
    /**
     * Create new folder 
     * 
     * @param  string $login 
     * @return true|string $err 
     */
    public function insertNewFolder($_folder_name, $_customer_id, $_parent_id)
    {

    	// Проверить тут есть ли уже такая папка.
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('uf' => 'usersfolder'), array('uf.name'))
            ->where('name=?', $_folder_name)
            ->where('parentid=?', $_parent_id)
            ->where('userid=?', $_customer_id);

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);
        if(count($rows) != 0){
        	return 'Папка с таким именем уже существует';
        }

    	$max_order= $this->getMaxOrder($_customer_id);

		$row = $this->save(array(
									'userid'		=> $_customer_id, 
									'parentid'		=> $_parent_id,
									'name'			=> $_folder_name,
									'order'			=> $max_order,
								));
	
        return $row;
    }

    /**
     * Fetch JavaScript code for initialize Dtree JS object.
     * 
     * @param  int $_customer_id
     * @return false|JS string
     */
	public function getUsersTree($_customer_id, $__tree_name, $_mode='js'){

		if($_mode=='js'){
			$ret = false;
		} else { 
			$ret = array();
		}


        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('uf' => 'usersfolder'), array('uf.id', 'uf.userid', 'uf.parentid', 'uf.name', 'uf.order'))
            ->where('userid=?', $_customer_id)
            ->where('parentid=?', 0)
            ->order('uf.order ASC');

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);
        foreach($rows as $row){

	        $select = Zend_Registry::getInstance()->dbAdapter->select();
			$select = $select
							->from(array('uf' => 'usersfolder'), array('uf.id', 'uf.userid', 'uf.parentid', 'uf.name', 'uf.order'))
							->where('userid=?', $_customer_id)
							->where('parentid=?', $row['id'])
							->order('uf.order ASC');

		    $rows_u = Vida_Helpers_DB::fetchAll(null, $select, true);

			if(count($rows_u) != 0){

				if($_mode=='js'){
					$ret .= $__tree_name.'.add('.$row["id"].',0,\''.$row["name"].'\',\'javascript:setUserFolderChoise_'.$__tree_name.'('.$row['id'].');\');'."\n";
				} else {
					array_push($ret, array($row["id"], 0,$row["name"]));
				}

				if($_mode=='js'){
					$ret .= $this->getRecurseChildren($_customer_id, $row["id"], $__tree_name, $_mode);
				} else {
					$ret = array_merge($ret, $this->getRecurseChildren($_customer_id, $row["id"], $__tree_name, $_mode));
				}

			} else {

				if($_mode=='js'){
					$ret .= $__tree_name.'.add('.$row["id"].',0,\''.$row["name"].'\',\'javascript:setUserFolderChoise_'.$__tree_name.'('.$row['id'].');\');'."\n";
				} else {
					array_push($ret, array($row["id"], 0,$row["name"]));
				}
			}
        }

		return $ret;
	}

	// PRIVATE FUNCTIONS ////////////////////////////////////////////////////////////
	private function getRecurseChildren($_customer_id, $_cur_node_id, $__tree_name, $_mode){

		if($_mode=='js'){
			$ret = false;
		} else { 
			$ret = array();
		}

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('uf' => 'usersfolder'), array('uf.id', 'uf.userid', 'uf.parentid', 'uf.name', 'uf.order'))
            ->where('userid=?', $_customer_id)
            ->where('parentid=?', $_cur_node_id)
            ->order('uf.order ASC');

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);

        foreach($rows as $row){

	        $select = Zend_Registry::getInstance()->dbAdapter->select();
			$select = $select
							->from(array('uf' => 'usersfolder'), array('uf.id', 'uf.userid', 'uf.parentid', 'uf.name', 'uf.order'))
							->where('userid=?', $_customer_id)
							->where('parentid=?', $row['id'])
							->order('uf.order ASC');

		    $rows_u = Vida_Helpers_DB::fetchAll(null, $select, true);

			if(count($rows_u) != 0){

				if($_mode=='js'){
					$ret .= $__tree_name.'.add('.$row["id"].','.$_cur_node_id.',\''.$row["name"].'\',\'javascript:setUserFolderChoise_'.$__tree_name.'('.$row['id'].');\');'."\n";
				} else {
					array_push($ret, array($row["id"], $_cur_node_id,$row["name"]));
				}

				if($_mode=='js'){
					$ret .= $this->getRecurseChildren($_customer_id, $row["id"], $__tree_name, $_mode);
				} else {
					$ret = array_merge($ret, $this->getRecurseChildren($_customer_id, $row["id"], $__tree_name, $_mode));
				}
			} else {

				if($_mode=='js'){
					$ret .= $__tree_name.'.add('.$row["id"].','.$_cur_node_id.',\''.$row["name"].'\',\'javascript:setUserFolderChoise_'.$__tree_name.'('.$row['id'].');\');'."\n";
				} else {
					array_push($ret, array($row["id"], $_cur_node_id,$row["name"]));
				}
			}        
        }

		return $ret;
	}

    /**
     * Check permissions(owners) for some folder.
     * 
     * @param  int $_folder_id, int $_customer_id
     * @return false|true
     */
	public function isUsersFolder($_folder_id, $_customer_id){

		$ret = false;

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('uf' => 'usersfolder'), array('uf.id'))
            ->where('id=?', $_folder_id)
            ->where('userid=?', $_customer_id);

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);
        if(count($rows)>0){ $ret = true; }

		return $ret;
	}

    /**
     * Fetch JavaScript code for initialize Dtree JS object.
     * 
     * @param  int $_customer_id
     * @return false|JS string
     */
	private function getMaxOrder($_customer_id){

		$ret = 100;

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('uf' => 'usersfolder'), array('uf.order'))
            ->where('userid=?', $_customer_id)
            ->where('parentid=?', 0)
            ->limit(1)
            ->order('uf.order DESC');

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);
		if(count($rows) != 0){
			$ret = $rows[0]['order'];
		}
		$ret++;

		return $ret;
	}
	/////////////////////////////////////////////////////////////////////////////////

}