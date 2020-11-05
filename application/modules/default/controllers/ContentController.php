<?php

/**
 * IndexController is the default controller for this application
 * 
 * Notice that we do not have to require 'Zend/Controller/Action.php', this
 * is because our application is using "autoloading" in the bootstrap.
 *
 * @see http://framework.zend.com/manual/en/zend.loader.html#zend.loader.load.autoload
 */
class ContentController extends Zend_Controller_Action 
{
    /**
     * Формирует облако тегов
     */
    public function cloudAction()
    {
        $segment = $this->_getParam('seg', 'postcontent');
        
        $this->_helper->viewRenderer->setResponseSegment($segment);
        
        $filetags_model = new Model_FileTags();
        $tags = $filetags_model->fetchTagClouds(25);
        $tag_model = new Model_Tags();
        $cloud = array();
        $max = -1;
        $link = '/index/tag/id/';
        
        foreach($tags as $tag) {
            if($max < 0) {
                $max = $tag['count'];    
            }
            $t = $tag_model->fetchById($tag['tag_id']);
            $cloud[] = array(
                'tag' => $t['tag'],
                'count' => $tag['count'],
                'tag_id' => $tag['tag_id'],
                'class' => ceil($tag['count']* 7/$max),
                'link' => $link . $tag['tag_id']
                /*
                'link' => $this->_helper->url->url(
                   array(
                       'controller' => 'index',
                       'action'     => 'tag',
                       'module'     => 'default',
                       'id'         => $tag['tag_id']
                   ))
                */
             );
        }
        $this->view->cloud = $cloud;
        unset($filetags_model, $tag_model);
        
        //dump($this->_helper->viewRenderer->getResponse()->getBody());
        
    }

    /**
     * Выводит "nearestadd" 
     */
    public function nearestaddAction()
    {
        $segment = $this->_getParam('seg', 'postcontent');
        $this->_helper->viewRenderer->setResponseSegment($segment);
        
        // Недавно добавленные (видео)
    	$model_files = new Model_Files();
        $_arr = $model_files->fetchNearestSelect(CONST_COUNT_NEAREST_VIDEO);
        $this->view->nearest_video_array = $_arr;
    }


    /**
     * Выводит "category list" 
     */
    public function categoryAction()
    {
        $segment = $this->_getParam('seg', 'postcontent');
        $this->_helper->viewRenderer->setResponseSegment($segment);

    	$mc = new Model_Category();
    	$arr = $mc->fetchAllCategoriesDepLang();
        $link = "/index/category/id/";
        
    	$category_uploaded_file_html = '';
    	foreach($arr as $iterator=>$row){
    		$category_uploaded_file_html .= '<a href="' .
                $link . $row[0]
                /* //FIXME: Наблюдается нестабильность работы данного helper-а
                $this->_helper->url->url(
                array(
                    'controller' => 'index',
                    'action'     => 'category',
                    'module'     => 'default',
                    'id'         => $row[0]
                ))*/.
            '">'.$row[1].'</a>';
    	}
        $this->view->category_uploaded_file	= $category_uploaded_file_html;
    }

    
    /**
     * The "index" action is the default action for all controllers. This 
     * will be the landing page of your application.
     *
     * Assuming the default route and default router, this action is dispatched 
     * via the following urls:
     *   /
     *   /story/
     *   /story/index
     *
     * @return void
     */
    public function indexAction() 
    {
    }
}
