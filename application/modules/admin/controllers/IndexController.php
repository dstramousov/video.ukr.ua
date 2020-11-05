<?php

/**
 * IndexController is the default controller for this application
 * 
 * Notice that we do not have to require 'Zend/Controller/Action.php', this
 * is because our application is using "autoloading" in the bootstrap.
 *
 * @see http://framework.zend.com/manual/en/zend.loader.html#zend.loader.load.autoload
 */
class Admin_IndexController extends Vida_Controller_Action 
{
    public function preDispatch()
    {
        $this->view->headTitle('Главная страница');
        $this->view->headMeta("Описание", "description");
        $this->view->headMeta("Ключевые слова", "keywords");
        parent::preDispatch();
    }

    /**
     * Подключает JS-скрипт главного меню
     * @return void
     */
    protected function _includeMainMenuJS() {
        //Подключить скрипт главного меню
        echo '<div id="menu-container"></div>';
        $this->view->inlineScript()->setFile(Vida_Helpers_File::get_rev('/js/admin/menu.js'));
        echo $this->view->inlineScript();
    }


    public function abusefiledetailAction() 
    {

        $request = $this->getRequest();
    	$fid = $request->getPost('fid', 23);
    	dump($fid);

        //$this->view->duration = 45435;
    }

    /**
     * 

     * @return void
     */
    public function abuseAction() 
    {
        $this->_helper->viewRenderer->setScriptAction('index');
        $this->_includeMainMenuJS();

        echo '<div id="grid-container"></div>';
        $this->view->inlineScript()->setFile(Vida_Helpers_File::get_rev('/js/admin/abuse.js'));
        echo $this->view->inlineScript();
    }


    /**
     * Подключает JS-скрипт пользователей системы
     * @return void
     */
    public function videoAction() 
    {
        $this->_helper->viewRenderer->setScriptAction('index');
        $this->_includeMainMenuJS();

        echo '<div id="grid-container"></div>';
        $this->view->inlineScript()->setFile(Vida_Helpers_File::get_rev('/js/admin/video.js'));
        echo $this->view->inlineScript();
    }

    /**
     * @return void
     */
    public function usersAction() 
    {
        $this->_helper->viewRenderer->setScriptAction('index');
        $this->_includeMainMenuJS();
        
        echo '<div id="grid-container"></div>';
        $this->view->inlineScript()->setFile(Vida_Helpers_File::get_rev('/js/admin/users.js'));
        echo $this->view->inlineScript();
    }

    
    /**
     * The "index" action is the default action for all controllers. This 
     * will be the landing page of your application.
     *
     * Assuming the default route and default router, this action is dispatched 
     * via the following urls:
     *   /
     *   /index/
     *   /index/index
     *
     * @return void
     */
    public function indexAction() 
    {
        $this->_includeMainMenuJS();

        // вывести ленту новостей
        /*
        $this->_helper->actionStack('maintape',
                                    'content',
                                    'default',
                                    array('key' => 'content_addons'));
        */
    }
}

