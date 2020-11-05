<?php

/**
 *
 * @see http://framework.zend.com/manual/en/zend.loader.html#zend.loader.load.autoload
 */
class StorageController extends Vida_Controller_Action 
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('playlist', 'json');
        $ajaxContext->addActionContext('videos', 'json');
        //$ajaxContext->addActionContext('users', 'html');
        $ajaxContext->initContext();
        $this->_logger = Zend_Registry::get('logger');
        
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addContexts(array(
            'xml_rel' => array(
                'suffix'    => 'xml_rel',
                'headers'   => array('Content-Type' => 'text/xml')
            ))
        );
        $contextSwitch->addActionContext('related', 'xml_rel')
                      ->initContext();
        
        
    }
    
    public function preDispatch()
    {
        $action = strtolower($this->getRequest()->getActionName());
		if (in_array($action, array("videos")) && !Zend_Auth::getInstance()->hasIdentity()) {
			$this->_helper->redirector('register', 'index');
		}
        parent::preDispatch();
    }

    /**
     * Возвращает форматированный xml связанный видео с данным
     */
    public function relatedAction() {
        $request = $this->getRequest();
        
        $id = $this->_getParam('id');
        $videos = array();
        if(!empty($id)) {
            $model_files = new Model_Files();
            $file = $model_files->fetchById($id);
            if(!empty($file)) {
                $select = $model_files->getByCategoryId($file['category_id']);
                $db = Zend_Registry::getInstance()->dbAdapter;
                $select = $select
                    ->where(new Zend_Db_Expr($db->quoteIdentifier('id') . '!=' . $db->quote($id)))
                    ->limit(20);
                $files = Vida_Helpers_DB::fetchAll(null, $select, false);
                foreach($files as $file) {
                    $data = $model_files->format($file['id']);
                    $videos[] = $data;
                }
            }
        }
        
        $script = $this->_helper->viewRenderer->getViewScript();
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        if(is_array($videos)) {
            $videos = Vida_Helpers_AJAXHelper::convert($videos);
        }
        
        $this->view->videos = $videos;

        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->initContext('xml_rel');
        
        $data = $this->view->render($script);
        $response = $this->getResponse();
        $response->setBody($data);
        
    }
    
    /**
     * Панель мои ролики
     */
    public function videosAction() {
        $res = array();
        if(!$this->getRequest()->isPost()) {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }
        $users_model = new Model_Users();
        $user = $users_model->fetchCurrentUser();
        if(empty($user)) {
            throw new Video_Session_ExpiredException();
        }
        $request = $this->getRequest();
        $task = $request->getPost('task', '');
        $files_model = new Model_Files();
        if($task == "DELETE") {
            $ids = $request->getPost('ids', '[]');
            if(($ids = json_decode($ids))) {
                foreach($ids as $id) {
                    $file = $files_model->fetchById($id);
                    
                    if(!empty($file)) {
                        if($user['id'] == $file['user_id']) {
                            $files_model->deleteById($id);
                        } else {
                            $this->_logger->log(sprintf("Попытка удаления чужого файла %d (Владелец: %d, удаляющий: %d)", $id, $file['user_id'], $user['id']), Zend_Log::ERR);
                        }
                    }
                }
            }
        }
        unset($files_model);
        $res['result'] = true;
        if(is_array($res)) {
            $res = Vida_Helpers_AJAXHelper::convert($res);
        }
        $this->_helper->json->sendJson($res);
    }
    
    /**
     * Выборка/Обновление/Создание данных справочника "Пользователи"
     */
    public function playlistAction() 
    {
        if(!$this->getRequest()->isPost()) {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }
    
        $request = $this->getRequest();
        $return = 0;
        $playlist_model = new Model_UserPlayList();
        
        $task = $request->getPost('task', '');
        if($task == '') {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }
        if($task == "LIST") {
            $return = $playlist_model->getPlayList();
        } else if ($task == "ADD") {
            $file_id = $request->getPost('file_id', '');
            if(!empty($file_id)) {
                $playlist_model->save(array('file_id' => $file_id));
            }
        } else if ($task == "REMOVE") {
            $file_id = $request->getPost('file_id', '');
            if(!empty($file_id)) {
                $playlist_model->remove(array('file_id' => $file_id));
            }
        }
        unset($playlist_model);

        if(is_array($return)) {
            $return = Vida_Helpers_AJAXHelper::convert($return);
        }
        $this->_helper->json->sendJson($return);
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
    }
}

