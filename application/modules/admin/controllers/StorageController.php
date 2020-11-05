<?php

/**
 *
 * @see http://framework.zend.com/manual/en/zend.loader.html#zend.loader.load.autoload
 */
class Admin_StorageController extends Vida_Controller_Action 
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('users', 'json');
        $ajaxContext->addActionContext('files', 'json');
        $ajaxContext->addActionContext('abuse', 'json');


        $ajaxContext->initContext();
    }

    /**
     * Выборка/Обновление/Создание данных справочника "Жалобы на файлы"
     */
    public function abuseAction()
    {

        if(!$this->getRequest()->isPost()) {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }
    
        $request = $this->getRequest();
        $return = 0;
        
        $task = $request->getPost('task', '');
        if($task == '') {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }

        // work processed
        if($task == "LISTING" || $task == "SEARCH") {
            $start = $request->getPost('start', 0);
            $limit = $request->getPost('limit', 30);
            $sort = $request->getPost('sort', 'id');
            if(empty($limit)) {
                $limit = 30;
            }
            
            $abuse_model = new Model_Abuses();
            $db = Zend_Registry::getInstance()->dbAdapter;
            $select = $abuse_model->select($sort);

            $descr = $request->getPost('descr', '');
            if(!empty($descr)) {
                $descr = iconv("utf-8", "windows-1251", $descr);
                $descr = strtolower($descr);
                $select = $select
                    ->where(new Zend_Db_Expr($db->quoteIdentifier('descr') . 'LIKE' . $db->quote('%' . $descr . '%')));
            }

            $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($limit);
            $paginator->setCurrentPageNumber((integer) ceil($start / $limit) + 1);
            $abuses = $paginator->getCurrentItems();

            $abuses = $abuses->getArrayCopy();
            $data = array();

            $files_model = new Model_Files();

            $counter_id = 0;
            foreach($abuses as $one_record){

	            $counter_id++;
            	if($one_record['reason'] == 0){
					$one_record['catuf']	 = 'Нарушение правил лицензионного соглашения';
            	} elseif($one_record['reason'] == 1){
					$one_record['catuf']	 = 'Битый файл';
            	} else {
					$one_record['catuf']	 = 'Другая причина';
            	}

				$filedata = $files_model->format($one_record['file_id']);

				$h_url = '	<a target="_blank" href="'.$filedata['video_url'].'">
								<img src="/images/page_green.png" alt="Просмотреть файл.">
							</a>';

				$d_url = '	<a href="#" onclick="return hs.htmlExpand(this, { contentId: \'highslide-html-'.$counter_id.'\', width: 350 } )"
								class="highslide">
								<img src="/images/note_go.png" alt="Просмотреть статистику.">
							</a>
							<div class="highslide-html-content" id="highslide-html-'.$counter_id.'">
								<div class="highslide-header"><ul><li class="highslide-close"><a href="#" onclick="return hs.close(this)">Close</a></li></ul>	    </div>
								<div class="highslide-body">
									<table cellpadding="1" cellspacing="1" id="main_table" align="center" border="1">
										<tr><td><b>Заголовок</b></td><td>'.$filedata['title'].'</td></tr>
										<tr><td><b>Ключевые слова</b></td><td>'.$filedata['filetags'].'</td></tr>
										<tr><td><b>Описание</b></td><td>'.$filedata['description'].'</td></tr>
										<tr><td><b>Хозяин файла</b></td><td>'.$filedata['owner_login'].' ('.$filedata['owner_fname'].' '.$filedata['owner_lname'].')</td></tr>
										<tr><td><b>Просмотров</b></td><td>'.$filedata['reviewed'].'</td></tr>
										<tr><td><b>Категория</b></td><td>'.$filedata['category'].'</td></tr>
									</table>
								</div>
							</div>';

//				$c_url = '<a href="javascript:void(0);"><img src="/images/cancel.png" title="Удалить файл" /></a>';

				$one_record['fileinfo']		= $d_url;
				$one_record['fileview']		= $h_url;
//				$one_record['filedelete']	= $c_url;
				
            	$data[] = $one_record;
            }

            $return = array();
            $return['rows'] = $data;
            $return['total'] = $paginator->getAdapter()->count();
        } else if ($task == "DELETE") {
            $ids = json_decode(stripslashes($request->getPost('ids', '')));
            if(count($ids) > 0) {

	            $abuse_model = new Model_Abuses();
	            $files_model = new Model_Files();

                foreach($ids as $id) {
			        $row = $abuse_model->fetchRowByCol('id', $id);
                    $files_model->deleteById($row['file_id']);
                }

                unset($files_model);
            }
            $return = 1;
        }

    	

        if(is_array($return)) {
            $return = Vida_Helpers_AJAXHelper::convert($return);
        }
        $this->_helper->json->sendJson($return);

	}

    /**
     * Выборка/Обновление/Создание данных справочника "Ролики"
     */
    public function filesAction()
    {

        if(!$this->getRequest()->isPost()) {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }
    
        $request = $this->getRequest();
        $return = 0;
        
        $task = $request->getPost('task', '');
        if($task == '') {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }

        // work processed
        if($task == "LISTING" || $task == "SEARCH") {
            $start = $request->getPost('start', 0);
            $limit = $request->getPost('limit', 30);
            $sort = $request->getPost('sort', 'id');
            if(empty($limit)) {
                $limit = 30;
            }
            
            $files_model = new Model_Files();
            $db = Zend_Registry::getInstance()->dbAdapter;
            $select = $files_model->select($sort);

            $title = $request->getPost('title', '');
            if(!empty($title)) {
                $title = iconv("utf-8", "windows-1251", $title);
                $title = strtolower($title);
                $select = $select
                    ->where(new Zend_Db_Expr($db->quoteIdentifier('title') . 'LIKE' . $db->quote('%' . $title . '%')));
            }
            
            $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($limit);
            $paginator->setCurrentPageNumber((integer) ceil($start / $limit) + 1);
            $files = $paginator->getCurrentItems();

            $files = $files->getArrayCopy();
            $data = array();

            foreach($files as $one_record){
				$one_record['created']  = Vida_Helpers_DateHelper::toDate($one_record['created']);
            	$data[] = $one_record;
            }

            $return = array();
            $return['rows'] = $data;
            $return['total'] = $paginator->getAdapter()->count();

        } else if ($task == "FETCH") {
            $id = $request->getPost('id', '');
            if(!empty($id)) {
    	        $files_model = new Model_Files();
	            $row = $files_model->fetchInfoById($id, true);

                $return = array();
                if(!empty($row)) {
                    unset($row['created']);
                    $return = $row;
                }
                unset($files_model);
            }
        } else if ($task == "UPDATE") {
            $values = $request->getParams();
            $values = Vida_Helpers_AJAXHelper::decode($values);
            $data = array();

            $data['category_id'] = $values['category_id'];
            $data['description'] = $values['description'];
            $data['tags'] = $values['filetags'];
            $data['title'] = $values['title'];

            $files_model = new Model_Files();
            if($values['id'] > 0) {
                $data['id'] = $values['id'];
                $files_model->update($data);
            } else {
                $files_model->save($data);
            }
            
            $return = 1;
        } else if ($task == "DELETE") {
            $ids = json_decode(stripslashes($request->getPost('ids', '')));
            if(count($ids) > 0) {
	            $files_model = new Model_Files();
                foreach($ids as $id) {
                    $files_model->deleteById($id);
                }
                unset($files_model);
            }
            $return = 1;
        }

        if(is_array($return)) {
            $return = Vida_Helpers_AJAXHelper::convert($return);
        }
        $this->_helper->json->sendJson($return);
    }


    
    /**
     * Выборка/Обновление/Создание данных справочника "Пользователи"
     */
    public function usersAction() 
    {
        if(!$this->getRequest()->isPost()) {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }
    
        $request = $this->getRequest();
        $return = 0;
        
        $task = $request->getPost('task', '');
        if($task == '') {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }
        if($task == "LISTING" || $task == "SEARCH") {
            $start = $request->getPost('start', 0);
            $limit = $request->getPost('limit', 10);
            $sort = $request->getPost('sort', 'id');
            if(empty($limit)) {
                $limit = 10;
            }
            
            $users_model = new Model_Users();
            $db = Zend_Registry::getInstance()->dbAdapter;
            //$paginator = new Zend_Paginator(new Vida_Paginator_Adapter_DbSelect($users_model->select($sort)));
            $select = $users_model->select($sort);
            
            $login = $request->getPost('login', '');
            if(!empty($login)) {
                $login = strtolower($login);
                $select = $select
                    ->where(new Zend_Db_Expr($db->quoteIdentifier('login') . 'LIKE' . $db->quote($login . '%')));
            }
            //dump($select->__toString());
            
            $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($limit);
            $paginator->setCurrentPageNumber((integer) ceil($start / $limit) + 1);
            $users = $paginator->getCurrentItems();
            $return = array();
            $return['rows'] = $users->getArrayCopy();
            $return['total'] = $paginator->getAdapter()->count();
        } else if ($task == "FETCH") {
            $id = $request->getPost('id', '');
            if(!empty($id)) {
                $users_model = new Model_Users();
                $row = $users_model->fetchById($id, true);
                $return = array();
                if(!empty($row)) {
                    unset($row['password']);
                    $return = $row;
                }
                unset($users_model);
            }
        } else if ($task == "UPDATE") {
            $values = $request->getParams();
            $values = Vida_Helpers_AJAXHelper::decode($values);
            $data = array();
            if(array_key_exists('password', $values) && empty($values['password'])) {
                $data['password'] = $values['password'];
            }
            if(array_key_exists('login', $values)) {
                $data['username'] = $values['login'];
            }
            if(array_key_exists('email', $values)) {
                $data['email'] = $values['email'];
            }
            $data['fname'] = $values['fname']; 
            $data['lname'] = $values['lname']; 
            $data['state'] = $values['state'];
            $users_model = new Model_Users();
            if($values['id'] > 0) {
                $data['id'] = $values['id'];
                $users_model->update($data);
            } else {
                $users_model->save($data);
            }
            $return = 1;
        } else if ($task == "DELETE") {
            $ids = json_decode(stripslashes($request->getPost('ids', '')));
            if(count($ids) > 0) {
                $users_model = new Model_Users();
                foreach($ids as $id) {
                    $users_model->deleteById($id);
                }
                unset($users_model);
            }
            $return = 1;
        } else if ($task == "VALIDATE") {
            $login = $request->getPost('login', '');
            $email = $request->getPost('email', '');
            $return = array();
            if(!empty($login)) {
                $users_model = new Model_Users();
                $login = strtolower($login);
                $row = $users_model->fetchByLogin($login);
                if(!empty($row)) {
                    $return['errors'] = "Имя пользователя недоступно для регистрации";
                }
                unset($users_model, $row);
            } else if(!empty($email)) {
                $users_model = new Model_Users();
                $email = strtolower($email);
                $row = $users_model->fetchByEmail($email);
                if(!empty($row)) {
                    $return['errors'] = "Пользователь с таким email уже зарегистрирован в системе";
                }
                unset($users_model, $row);
            }
        }

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

