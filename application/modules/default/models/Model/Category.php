<?php

/*   
Autos & Vehicles
Comedy
Education
Entertainment
Film & Animation
Gaming
Howto & Style
Movies
Music
News & Politics
Nonprofits & Activism
People & Blogs
Pets & Animals
Science & Technology
Sports
Travel & Events

*/

class Model_Category extends Vida_Model
{
    protected $_className = "DbTable_Category";
    

    /**
     * Возвращает массив ID => Сформированное для языка имя категории
     * 
     * @param  
     * @return array
     */
    public function fetchAllCategoriesDepLang()
    {
    	$ret = array();
    	
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ct' => 'category'), array('ct.id', 'ct.name'))
            ->where('ct.status=?', ST_CATEGORY_OPEN);

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);

        foreach($rows as $iterator=>$row){
	        $ret[$iterator] = array($row['id'], Vida_Helpers_Text::_T($row['name']));
        }

		return $ret;
    }


    /**
     * Возвращает срочку по ID
     * 
     * @param  
     * @return array
     */
    public function fetchCategoryDepLang($category_id)
    {

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('ct' => 'category'), array('ct.id', 'ct.name'))
            ->where('ct.id=?', $category_id);

        $row = Vida_Helpers_DB::fetchRow(null, $select, true);

		return Vida_Helpers_Text::_T($row['name']);
    }


    /**
     * Для фомирования списка категорий учавствующих в поиске.
     * 
     * @param  
     * @return array
     */
    public function getFormatedList()
    {
    	$ret = '<li><a href="javascript:void(0);" onclick="doSwitcherUserCategory(0, \''.Vida_Helpers_Text::_T('allcategory').'\');"><b>'.Vida_Helpers_Text::_T('allcategory').'</b></a></li>';	

		$arr = $this->fetchAllCategoriesDepLang();

    	$category_uploaded_file_html = '';
    	foreach($arr as $iterator=>$row){
    		$ret .= '<li><a href="javascript:void(0);" onclick="doSwitcherUserCategory('.$row[0].', \''.$row[1].'\');">'.$row[1].'</a></li>';
    	}

    	return $ret;
    }

}