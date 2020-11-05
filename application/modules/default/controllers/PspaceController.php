<?php 

/**
 * ErrorController
 */ 
class PspaceController extends Vida_Controller_Action
{ 
	var $cur_user = null;

    public function preDispatch()
    {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_helper->redirector('register', 'index');
		}
        
        $action = strtolower($this->getRequest()->getActionName());
        if('video' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Редактирование видео'));
        } else if('videos' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Мои ролики'));
        }
        parent::preDispatch();
    }

    public function postDispatch()
    {
        // вывести cloud
        $this->_helper->actionStack('cloud', 'content', 'default', array('seg' => 'rightsidebar'));
        // вывести categorylist
        $this->_helper->actionStack('category', 'content', 'default', array('seg' => 'leftsidebar'));
        
        parent::postDispatch();
    }
    
    /**
     * Редактирование описания video
     */
    public function videoAction() {
        $values = array();
        $errors = array();
        
        //выборка параметров вызова
        $file_id = $this->_getParam('id', '-1');
        if((int)$file_id < 0) {
            throw new Zend_Controller_Dispatcher_Exception('Internal error');
        }
        
        //проверка принадлежности файла
        $model_users = new Model_Users();
        $user = $model_users->fetchCurrentUser();
        if(empty($user)) {
            throw new Video_Session_ExpiredException();
        }

        $files_model = new Model_Files();
        $file =  $files_model->fetchById($file_id);
        if(empty($file)) {
            throw new Zend_Controller_Dispatcher_Exception('Указан несуществующий файл');
        }
        
        if(!empty($file)) {
            if($user['id'] != $file['user_id']) {
                $this->_logger->log(sprintf("Попытка редактирования чужого файла %d (Владелец: %d, удаляющий: %d)", $id, $file['user_id'], $user['id']), Zend_Log::ERR);
                throw new Zend_Controller_Dispatcher_Exception('Internal error');
            }
        }

        //формирование списка категорий
        $model_categories = new Model_Category();
        $arr = $model_categories->fetchAllCategoriesDepLang();
        $categories = array();
        foreach($arr as $a) {
            $categories[] = array("category_id" => $a[0], "category" => $a[1]);
        }
        $this->view->categories = $categories;
        unset($model_categories);
        
        if(!$this->getRequest()->isPost()) {
            //подчитать значения полей формы редактирования
            $file = $files_model->format($file_id);
            
            //dump($file);
            
            $values['title'] = $file['title'];
            $values['tags'] = $file['filetags'];
            $values['description'] = $file['description'];
            $values['category_id'] = $file['category_id'];
            
        } else {

            $values = $this->getRequest()->getPost();
    
            $empty = new Zend_Validate_NotEmpty();
            if(!$empty->isValid($values['title'])) {
                $errors['title'] = Vida_Helpers_Text::_T('Поле не может быть пустым');
            }
                
            $empty = new Zend_Validate_NotEmpty();
            if(!$empty->isValid($values['tags'])) {
                $errors['tags'] = Vida_Helpers_Text::_T('Поле не может быть пустым');
            }

            $empty = new Zend_Validate_NotEmpty();
            if(!$empty->isValid($values['description'])) {
                $errors['description'] = Vida_Helpers_Text::_T('Поле не может быть пустым');
            }

            $empty = new Zend_Validate_NotEmpty();
            if(!array_key_exists('category_id', $values)||!$empty->isValid($values['category_id'])) {
                $errors['category_id'] = Vida_Helpers_Text::_T('Поле не может быть пустым');
            }
                
            if(count($errors) == 0) {
                $data['tags'] = Vida_Helpers_Text::purge($values['tags']);
                if(array_key_exists('folder_id', $values)) {
                    $data['folder_id'] = $values['folder_id'];
                }
                $data['id'] = $file_id;
                $data['description'] = Vida_Helpers_Text::purge($values['description']);
                $data['title'] = Vida_Helpers_Text::purge($values['title']);
                $data['category_id'] = $values['category_id'];
                
                $files_model->update($data);
                
                $link = '/pspace/videos';
                $this->_helper->redirector->gotoUrl($link);
            }
            //$this->_helper->redirector('index', 'index', 'default');
        }
        
        $values = Vida_Helpers_AJAXHelper::convert($values);
        $this->view->values = json_encode($values);
        $errors = Vida_Helpers_AJAXHelper::convert($errors);
        $this->view->errors = json_encode($errors);
    }    
    
    
    /**
     * Отображение списка видео роликов пользователя (с возможностью редактирования/удаления)
     */
    public function videosAction() 
    {
        //$helper = $this->view->getHelper('videoItem');
        
        $users_model = new Model_Users();
        $files_model = new Model_Files();
        
        $row = $users_model->fetchCurrentUser();
        if(!empty($row)) {
            $select = $files_model->getByUserId($row['id']);
        } else {
            throw new Video_Session_ExpiredException();
        }
        
        $paginator = new Zend_Paginator(new Vida_Paginator_Adapter_DbSelect($select));
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(
            'my_pagination_control.phtml'
        );
        $paginator->setItemCountPerPage(5);
        $paginator->setView($this->view);
        $paginator->setCurrentPageNumber($this->_getParam('page'));

        $this->view->count = $paginator->getAdapter()->count();
        
        $files = $paginator->getCurrentItems();
        
        $helper = new Vida_Helpers_SearchHelper();
        $entries = array();
        foreach($files as $file)
        {
            $entry = array();
            $entry['id'] = $file['file_id'];
            $entries[] = $entry;
        }
        $this->view->paginator = $paginator;
        $this->view->entries = $entries;
        
        unset($users_model, $files_model);
    	
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

    /**
     * отображает профайл пользователя
     * @return void
     */
    public function profileAction() 
    {
        $values = array();
        $errors = array();

        if($this->getRequest()->isPost()) {
            $values = $this->getRequest()->getPost();
            $res = Model_Users::profile();

            if(is_array($res)) {
				$users_model = new Model_Users();
				$user = $users_model->fetchCurrentUser();
				if(!empty($user)) {
					$res = array();
					$res['_login'] = $user['login'];
					$values['username'] = $user['login'];
					$res['_id'] = $user['ext_id'];
					$validator = new Zend_Validate_EmailAddress();
					if (!$validator->isValid($values['email'])) {
						$errors['email'] = '';
						foreach ($validator->getMessages() as $message) {
							$errors['email'] = Vida_Helpers_Text::_T('Некорректный email');
						}
					} else {
						$res['_email'] = $values['email'];
						$res['_birthDate'] = date("d.m.Y", mktime(0, 0, 0, $values['bmonth'], $values['bday'], $values['byear']));
						if(!empty($values['password'])) {
							if($values['password'] != $values['conf_password']) {
								$errors['conf_password'] = "Пароли не совпадают";
							} else {
								$res['_password'] = $values['password'];
							}
						} else {
							$res['_password'] = $user['password'];
						}
						if($values['sex'] == 1) {
							$res['_male'] = "true";
						} else {
							$res['_male'] = "false";
						}
					}
				}
			}
			if(count($errors) == 0) {

				Model_Users::update_profile($res);
				$this->_helper->redirector('profile', 'pspace');
			}
        } else {
            $res = Model_Users::profile();
            if(is_array($res)) {
                $values = $res;
            }
        }

        $values = Vida_Helpers_AJAXHelper::convert($values);
        $this->view->values = json_encode($values);
        $errors = Vida_Helpers_AJAXHelper::convert($errors);
        $this->view->errors = json_encode($errors);
    }

    /**
     * отображает менеджер файлов пользователя
     * @return void
     */
    public function myexplorerAction()
    {
    	return;
    }

    public function uploadAction()
    {
        	
    	$mc = new Model_Category();
    	$arr = $mc->fetchAllCategoriesDepLang();
    	
    	$category_uploaded_file_html = '';
    	foreach($arr as $iterator=>$row){
    		$category_uploaded_file_html .= '<option value="'.$row[0].'">'.$row[1].'</option>';
    	}

        $this->view->category_uploaded_file	= $category_uploaded_file_html;
    }


    /**
     * отображает сообщения пользователя
     * @return void
     */
    public function mymessagesAction() 
    {
    	
    }

}
