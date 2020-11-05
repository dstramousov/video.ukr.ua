<?php
class Vida_Plugins_ModelsIncludePath extends Zend_Controller_Plugin_Abstract
{
    protected $_logger;

    /**
     * The HTTP Auth adapter
     */ 
    protected $_adapter;
 
 
    /**
     * Constructor
     *
     * @param Zend_Auth_Adapter_Interface
     */ 
    public function __construct(Zend_Auth_Adapter_Interface $adapter) 
    { 
        $this->_adapter = $adapter; 
    } 

    /**
    * Устанавливает необходимые пути для корректной работы модульной структуры приложения
    * @param Zend_Controller_Request_Abstract $request
    */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {

        if(ADMIN_AREA) {
            if ((null !== ($request = Zend_Controller_Front::getInstance()->getRequest())) && (method_exists($request, 'setBaseUrl')) && (method_exists($request, 'setModuleName'))) {
                $request
                    ->setBaseUrl('/')
                    ->setModuleName('admin');
            }
        }

        //$this->_logger = Zend_Registry::get('logger');
        $module = $request->getModuleName();
        $path = Zend_Controller_Front::getInstance()->getModuleDirectory($module);
        
        if($module != Zend_Controller_Front::getInstance()->getDefaultModule()) {
            set_include_path(
                get_include_path().PATH_SEPARATOR.
                    Zend_Controller_Front::getInstance()->getModuleDirectory(Zend_Controller_Front::getInstance()->getDefaultModule()) . '/models'
            );
        }
        
        //автозагрузка классов форм и моделей модуля
        set_include_path(
            get_include_path().PATH_SEPARATOR.
                $path . '/models'.PATH_SEPARATOR.
                $path . '/forms'
        );

        //$this->_logger->log(sprintf("Include path=\"%s\"", get_include_path()), Zend_Log::DEBUG);

        Zend_Layout::getMvcInstance()->setLayoutPath($path . '/layouts/scripts');
        Zend_Layout::getMvcInstance()->setLayout("index");

        $view = Zend_Layout::getMvcInstance()->getView();
        $view->actionName = $request->getActionName();
        $view->controllerName = $request->getControllerName();
        
        $view->setHelperPath($path . '/views/helpers/', 'Vida_View_Helper_');
        
        // Tell the view where it finds Zend_Dojo ViewHelper
        $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper_');
        
        //dump($view->getHelperPaths());

        // авторизация доступа к админке
        if(strtolower($module) == 'admin')
        {
            //FIXME: apache аутентификация в большинстве хостингов не работает
            /*
            $auth = Zend_Auth::getInstance(); 
            $this->_adapter->setRequest($this->_request); 
            $this->_adapter->setResponse($this->_response); 
            $result = $auth->authenticate($this->_adapter);
            //$this->_logger->log(var_dump($result), Zend_Log::DEBUG);
            
            if (!$result->isValid())
            {
                $this->_request->setControllerName('index');
                $this->_request->setActionName('authenticate');
            }
            */
        }
        else
        {
            //FIXME: кто-то зашел из админки на главную
            $auth = Zend_Auth::getInstance();
            if($auth->hasIdentity() && is_array($auth->getIdentity()))
            {
                $auth->clearIdentity();
            }
        }
        
    }
}