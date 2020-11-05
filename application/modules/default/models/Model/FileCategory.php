<?php

class Model_FileCategory extends Vida_Model
{
    protected $_className = "DbTable_FileCategory";

    /**
    * Update данных о категориях по фалйу.
    *
    * @param  int $file_id file, array of category
    * @return null
    */
    public function update($file_id, $category_arr)
    {
    	$this->deleteByFileId($file_id);

    	$data = array();
    	$data['file_id'] = $file_id;
    	foreach($category_arr as $cat_id){

	    	$data['category_id'] = $cat_id;
    		$this->save($data);
    	}
    }

    /**
    * Delete данных о категориях по фалйу.
    *
    * @param  int $file_id file, array of category
    * @return null
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
    * Вернуть все имена категорий для данного файла
    *
    * @param  int $file_id file
    * @return array of ID=>Name
    */
    public function fetchCategoriesNameByFileId($file_id)
    {
    	$ret = array();
        $category_model = new Model_Category();
    	$_arr = $this->fetchCategoriesIDByFileId($file_id);

        foreach($_arr as $iterator=>$row){

        	$name = $category_model->fetchCategoryDepLang($row['category_id']);
	        $ret[$row['category_id']] = $name;
        }

		return $ret;
    }


    /**
    * Вернуть все ID категорий по file_id
    *
    * @param  int $file_id file
    * @return array of ID
    */
    public function fetchCategoriesIDByFileId($file_id)
    {
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('fc' => 'filecategory'), array('fc.category_id'))
            ->where('fc.file_id=?', $file_id);

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);

		return $rows;
    }

}