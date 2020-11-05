<?php

	
class UserTreeWrapper  {

	var $conn;

    function __construct() {
//		$this->connection_init();
    }

    ////////////////////////////////////////////////////////////////////////
    // only for test 
    private function connection_init (){

        $db = 'treetest';
        $us = 'treetest';
        $ps = 'treetest';    	
    	
		$this->conn = mysql_connect("localhost", $us, $ps);

		if (!$this->conn) {
		    echo "Unable to connect to DB: " . mysql_error();
		    exit;
		}
  
		if (!mysql_select_db($db)) {
		    echo "Unable to select mydbname: " . mysql_error();
		    exit;
		}

	}


	private function getRecurseChildren($_customer_id, $_cur_node_id){

		$ret = '';
		
		$sql = "SELECT * FROM usersfolder WHERE usersfolder.userid=".$_customer_id." AND usersfolder.parentid=".$_cur_node_id." ORDER BY usersfolder.order asc";
		$result = mysql_query($sql);
		
		$folder_cur_level = array();
		while ($row = mysql_fetch_assoc($result)) {

			// Проверим нет ли потомков
			$sql2		= "SELECT * FROM usersfolder WHERE usersfolder.userid=".$_customer_id." AND usersfolder.parentid=".$row["id"]." ORDER BY usersfolder.order asc";
			$result2	= mysql_query($sql2);
			$nrows		= mysql_num_rows($result2);

			if($nrows != 0){
				$ret .= 'd.add('.$row["id"].','.$_cur_node_id.',\''.$row["name"].'\',\'javascript:setUserFolderChoise('.$row['id'].');\');'."\n";
				$ret .= $this->getRecurseChildren($_customer_id, $row["id"]);
			} else {
				$ret .= 'd.add('.$row["id"].','.$_cur_node_id.',\''.$row["name"].'\',\'javascript:setUserFolderChoise('.$row['id'].');\');'."\n";
			}
		}

		return $ret;
		
	}
    ////////////////////////////////////////////////////////////////////////




	/* 
		make first initialization Users folder
    */
	public function firstInitFolderStruct($_customer_id){

		// Создается такая структура папок для пользователя
		/*
			Музыка
				Поп
				Рок
				Jazz

			Фильмы

			Картинки

			Разное
		*/


		$sql = 'INSERT INTO  `treetest`.`usersfolder` (`id` ,`userid` ,`parentid` ,`name` , `order`) VALUES ( NULL ,  \''.$_customer_id.'\',  \'0\',  \'Музыка\',  \'1\')';
		$result = mysql_query($sql);
		$_ID = mysql_insert_id();

		$sql = 'INSERT INTO  `treetest`.`usersfolder` (`id` ,`userid` ,`parentid` ,`name` , `order`) VALUES ( NULL ,  \''.$_customer_id.'\',  \''.$_ID.'\',  \'Поп\',  \'1\')';
		$result = mysql_query($sql);

		$sql = 'INSERT INTO  `treetest`.`usersfolder` (`id` ,`userid` ,`parentid` ,`name` , `order`) VALUES ( NULL ,  \''.$_customer_id.'\',  \''.$_ID.'\',  \'Рок\',  \'2\')';
		$result = mysql_query($sql);

		$sql = 'INSERT INTO  `treetest`.`usersfolder` (`id` ,`userid` ,`parentid` ,`name` , `order`) VALUES ( NULL ,  \''.$_customer_id.'\',  \''.$_ID.'\',  \'Jazz\',  \'3\')';
		$result = mysql_query($sql);

		$sql = 'INSERT INTO  `treetest`.`usersfolder` (`id` ,`userid` ,`parentid` ,`name` , `order`) VALUES ( NULL ,  \''.$_customer_id.'\',  \'0\',  \'Фильмы\',  \'2\')';
		$result = mysql_query($sql);

		$sql = 'INSERT INTO  `treetest`.`usersfolder` (`id` ,`userid` ,`parentid` ,`name` , `order`) VALUES ( NULL ,  \''.$_customer_id.'\',  \'0\',  \'Картинки\',  \'3\')';
		$result = mysql_query($sql);

		$sql = 'INSERT INTO  `treetest`.`usersfolder` (`id` ,`userid` ,`parentid` ,`name` , `order`) VALUES ( NULL ,  \''.$_customer_id.'\',  \'0\',  \'Разное\',  \'4\')';
		$result = mysql_query($sql);

	}


	/* 
		return JS code for JS class "dtree";
    */
	public function getUsersTree($_customer_id){

		$ret = '';

		// все папки 1-го уровня
		$sql = "SELECT * FROM usersfolder WHERE usersfolder.userid=".$_customer_id." AND usersfolder.parentid=0 ORDER BY usersfolder.order asc";
		$result = mysql_query($sql);

		if (!$result) {
		    echo "Could not successfully run query ($sql) from DB: " . mysql_error();
		    exit;
		}

		if (mysql_num_rows($result) == 0) {
		    echo "No rows found, nothing to print so am exiting";
		    exit;
		}

		$folder_null_level = array();
		while ($row = mysql_fetch_assoc($result)) {

			// Проверим нет ли потомков
			$sql2		= "SELECT * FROM usersfolder WHERE usersfolder.userid=".$_customer_id." AND usersfolder.parentid=".$row["id"]." ORDER BY usersfolder.order asc";
			$result2	= mysql_query($sql2);
			$nrows		= mysql_num_rows($result2);

			if($nrows != 0){                                  
				$ret .= 'd.add('.$row["id"].',0,\''.$row["name"].'\',\'javascript:setUserFolderChoise('.$row['id'].');\');'."\n";
				$ret .= $this->getRecurseChildren($_customer_id, $row["id"]);
			} else {
				$ret .= 'd.add('.$row["id"].',0,\''.$row["name"].'\',\'javascript:setUserFolderChoise('.$row['id'].');\');'."\n";
			}
		}

		return $ret;
	}



	
} // end of class


?>