<?php

/**
 * IndexController is the default controller for this application
 * 
 * Notice that we do not have to require 'Zend/Controller/Action.php', this
 * is because our application is using "autoloading" in the bootstrap.
 *
 * @see http://framework.zend.com/manual/en/zend.loader.html#zend.loader.load.autoload
 */
class IndexController extends Vida_Controller_Action
{
    const COUNT_ITEMS_IN_QUICK_PLAYLIST	= 10;	// отклонен

    public function preDispatch()
    {
        $action = strtolower($this->getRequest()->getActionName());
        if('index' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Главная страница'));
        } else if('tag' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Просмотр файлов по тегу'));
        } else if('category' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Просмотр файлов категории'));
        } else if('search' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Результат поиска файлов'));
        } else if('play' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Воспроизведение видео'));
        } else if('playlist' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Мой список воспроизведения'));
        } else if('upload' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Загрузка видео'));
            //загружать могут только авторизованные пользователи
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('register', 'index');
            }
        } else if('register' == $action) {
            $this->view->headTitle(Vida_Helpers_Text::_T('Регистрация'));
            if (Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'index');
            }
        }
        
        if('play' != $action) {
            $this->view->headMeta("Видео сервис и видео хостинг Video.ukr.ua. Видеоролики, запись своих роликов. Аниме, мультфильмы и видеоклипы онлайн.", "description");
            $this->view->headMeta("видео, видеоролики, видео ролики, спорт, музыка, юмор, приколы, мульты, аниме, мультфильмы, anime", "keywords");
        }
        
        //авторизация пользователя по Cookie
        Model_Users::authenticate();

        parent::preDispatch();
    }

    public function postDispatch()
    {
        $action = strtolower($this->getRequest()->getActionName());
        $cloud_segment = 'postcontent';
        if(in_array($action, array('tag', 'search', 'category', 'upload', 'register', 'play', 'playlist', 'success'))) {
            $cloud_segment = 'rightsidebar';
        }
        $category_segment = 'postcontent';
        if(in_array($action, array('tag', 'search', 'category', 'upload', 'register', 'play', 'playlist', 'success'))) {
            $category_segment = 'leftsidebar';
        }
        $recently = false;
        if(in_array($action, array('index'))) {
            $recently = true;
        }
        if(!in_array($action, array('topswitcher', 'agreement', 'success'))) {
            // вывести cloud
            $this->_helper->actionStack('cloud', 'content', 'default', array('seg' => $cloud_segment));
            if($recently) {
                // вывести nearestadd
                $this->_helper->actionStack('nearestadd', 'content', 'default');
            }
            // вывести categorylist
            $this->_helper->actionStack('category', 'content', 'default', array('seg' => $category_segment));
        }
        
        parent::postDispatch();
    }
    
    public function topswitcherAction() {
        Zend_Layout::getMvcInstance()->setLayout("empty");
        $this->_helper->viewRenderer->setNoRender(true);
        
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $sw_type = ST_REQUEST_TOP_TYPE_ALL;
        switch($this->getRequest()->getParam('switcher_id')) {
            case 'swday':
                $sw_type = ST_REQUEST_TOP_TYPE_TODAY;
                break;
            case 'swweek':
                $sw_type = ST_REQUEST_TOP_TYPE_WEEK;
                break;
            case 'swmonth':
                $sw_type = ST_REQUEST_TOP_TYPE_MONTH;
                break;
            case 'swall':
                $sw_type = ST_REQUEST_TOP_TYPE_ALL;
                break;
            default:
        }

        $model_files = new Model_Files();
        $_arr = $model_files->fetchTopSelect($sw_type);

        $_str = '';
        if(count($_arr)>0) {
            foreach ($_arr as $fileinfo){
                $_str .= $this->view->TopVideoItem($fileinfo); 
            }   
        }
        
        echo $_str;
    }
    
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('abuse', 'json');
        $ajaxContext->addActionContext('setrating', 'json');
        $ajaxContext->addActionContext('deletemessage', 'json');
        $ajaxContext->addActionContext('postcomment', 'json');

        $ajaxContext->initContext();
    }

    /**
     * Генерирует новую каптчу
     * @return void
     */
    public function captchaAction() {
        $captcha = new Zend_Captcha_Image(array(  
            'captcha' => 'Image', 'wordLen' => 4, 'timeout' => 300, 'font' => 'NewtonC.ttf', 'width' => 100,  'height' => 40,
            'dotNoiseLevel' => 0,  'lineNoiseLevel' => 0
        ));
        $captcha->setName('captcha');
        
        $res = array(
            'captcha_id' => $captcha->generate(),
            'captcha' => $captcha->render($this->view)
        );
        $this->_helper->json->sendJson($res);
    }
    
    public function registerAction() {
        // Using single captcha key:
        $captcha = new Zend_Captcha_Image(array(  
                'captcha' => 'Image', 'wordLen' => 4, 'timeout' => 300, 'font' => 'NewtonC.ttf', 'width' => 100,  'height' => 40,
                'dotNoiseLevel' => 0,  'lineNoiseLevel' => 0
        ));
        $captcha->setName('captcha');
        
        $values = array();
        
        if($this->getRequest()->isPost()) {
            $values = $this->getRequest()->getPost();
            if(!$captcha->isValid($_POST['captcha'], $_POST)) {
                $this->view->captcha_error = true;
            } else {
                //dump($this->getRequest()->getPost());
                //проверить имя пользователя
                $user_model = new Model_Users();
                $error = false;

                $error = Model_Users::register($values);
                if(!$error) {
                    $this->view->user_error = Vida_Helpers_Text::_T("Пользователь с таким именем или email уже зарегистрирован в системе");
                } else {
                    $this->_helper->redirector('success', 'index');
                }
            }
        }
        
        $this->view->values = json_encode($values);
        $this->view->captcha_id = $captcha->generate();
        $this->view->captcha = $captcha->render($this->view);
    }

    public function successAction() {
        $this->_helper->viewRenderer->setScriptAction('success-' . Vida_Helpers_Text::_L());
    }
    
    public function agreementAction() {
        $this->_helper->viewRenderer->setScriptAction('agreement-' . Vida_Helpers_Text::_L());
    }

    public function postcommentAction() {

        $model_user = new Model_Users();
        $user_row = $model_user->fetchCurrentUser();

    	$model_comment = new Model_Comment();

        $data['user_id']	= $user_row['id'];
        $data['created']	= Vida_Helpers_DateHelper::today();
        $data['parent_id']	= $this->_getParam('parent_id');
        $data['file_id']	= $this->_getParam('file_id');
		$data['body']		= iconv("utf-8", "windows-1251", Vida_Helpers_Text::purge($this->_getParam('body_comment')));
		$data['stay']		= Model_Comment::ST_COMMENT_ACCEPTED;
		$data['rate']		= 0;

        $model_comment->save($data);
        $this->_helper->json->sendJson(null);
    }

    public function deletemessageAction() {

	    $message_id = $this->_getParam('mess_id');

        $model_user = new Model_Users();
        $user_row = $model_user->fetchCurrentUser();

    	$model_message = new Model_Messages();

        if($model_message->isPossibleDelete($message_id, $user_row['id'])) { 
        	$model_message->deleteByMessageId($message_id);
        } else {
            throw new Zend_Controller_Dispatcher_Exception('Permission error');
        }

        $this->_helper->json->sendJson(null);
    }
    
    public function setratingAction() {

        $model_user = new Model_Users();
        $user_row = $model_user->fetchCurrentUser();

    	$model_rate = new Model_FileRate();
        $data['user_id'] = $user_row['id'];
        $data['file_id'] = $this->_getParam('file_id');
        $data['rateval'] = $this->_getParam('rating_value');

        $model_rate->save($data);
        $this->_helper->json->sendJson(null);
    }
    
    public function abuseAction() {

        if(!$this->getRequest()->isPost()) {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }
        $abuse_model =  new Model_Abuses();
        $data = array();
        $data['file_id'] = $this->_getParam('file_id');
        $data['reason'] = $this->_getParam('reason');
        $data['descr'] = $this->_getParam('reason_text');
        if($data['reason'] == 0) {
            $data['descr'] = "Нарушение правил лицензионного соглашения";
        } else if($data['reason'] == 1) {
            $data['descr'] = "Битый файл";
        }

        $abuse_model->save($data);
        $this->_helper->json->sendJson(null);
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
        // Рандом видео из лучших
        $model_files = new Model_Files();

        $_arr = $model_files->fetchRandomTopFile();
        $this->view->model_file_obj = $_arr;
        
        // Топ (видео)
        $_arr = $model_files->fetchTopSelect(ST_REQUEST_TOP_TYPE_ALL);
        $this->view->top_video_array = $_arr;
        
        unset($model_files);
        
//        dump($this->_helper->viewRenderer->getResponse()->getBody());

    }

    /**
     * 
     * @return void
     */
    public function progressAction()
    {
        if(!$this->getRequest()->isPost()) {
            throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        }
                   
        require_once('UploadProgressManager.class.php');
        $tmpdir = 'C:\\Temp\\video';
        $UPM = new UploadProgressManager($tmpdir);          // new UploadProgressManager with temporary upload folder
        if(($filesize = $UPM->getTemporaryFileSize()) === false) {
            $filesize = 0;
        }
        $data = array();
        $data['filesize'] = $filesize;
        if(is_array($data)) {
            $data = Vida_Helpers_AJAXHelper::convert($data);
        }
        $this->_helper->json->sendJson($data);
    }

    /**
     * 
     * @return void
     */
    public function uploadAction()
    {
        $values = array();
        $errors = array();
        
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
            
            //throw new Zend_Controller_Dispatcher_Exception('Internal Error');
        } else {

            $values = $this->getRequest()->getPost();
            
            //dump($values);
    
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator('FilesSize',
                          false,
                          array('min' => 1,
                                'max' => '400MB'));
    
            $upload->addValidator('Count',
                          false,
                          array('min' => '1',
                                'max' => '1'));
            
            $agent = $_SERVER['HTTP_USER_AGENT'];
            $supported = false;
            if(eregi("safari", $agent)) {
                $tmp = $upload->getFileInfo();
                $info = $tmp['videofile'];
                $path_info = pathinfo($info['name']);
                $ext = strtolower($path_info['extension']);
                if(in_array($ext, array('flv', '3g', 'avi', 'mov'))) {
                    $supported = true;
                }
            }

            //$tmp = $upload->getFileInfo();
            //$info = $tmp['videofile'];
            //dump($info);
            
            if(!$supported) {
                $upload->addValidator('MimeType',
                    false,
                    array(
                      'video/3gpp',
                      'video/mpeg',
                      'video/quicktime',
                      'video/avi',
                      'video/x-flv',
                      'video/x-mng',
                      'video/x-ms-asf',
                      'video/x-ms-wmv',
                      'video/x-msvideo',
                      'video/x-la-asf',
                      'video/x-sgi-movie',
                      'application/octet-stream',    //flv
                      'application/x-flash-video'
                    )
                );
            }
            
    
            $files_model = new Model_Files();
            $file_id = -1;
            if(!$upload->isValid()) {
               //$messages = $upload->getMessages();
               //dump($upload->getMessages());
               $errors['file'] = Vida_Helpers_Text::_T('Не задан файл для загрузки или файл не подходит по критериям');
            }

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
                $tmp = $upload->getFileInfo();
                
                $info = $tmp['videofile'];
                
                $data = array();
                $data['tags'] = Vida_Helpers_Text::purge($values['tags']);
                if(array_key_exists('folder_id', $values)) {
                    $data['folder_id'] = $values['folder_id'];
                }
                $data['description'] = Vida_Helpers_Text::purge($values['description']);
                $data['title'] = Vida_Helpers_Text::purge($values['title']);
                
                //Выбрать текущего пользователя
                $model_users = new Model_Users();
                $user = $model_users->fetchCurrentUser();
                if(empty($user)) {
                    throw new Video_Session_ExpiredException();
                }
                
                $data['user_id'] = $user['id'];
                $data['category_id'] = $values['category_id'];
                
                //имя файла для сообщения
                $data['params'] = array('fname' => $info['name']);
    
                $file_id = $files_model->save($data, $info['tmp_name']);
                
                /*
                $link = '';
                if($file_id > 0) {
                    $row = $files_model->fetchById($file_id);
                    if(!empty($row)) {
                        $link = $files_model->getDownloadLink($row);
                    }
                }
                $this->_helper->redirector->gotoUrl($link);
                */
                $this->_helper->redirector('videos', 'pspace', 'default');
                
            }
            //$this->_helper->redirector('index', 'index', 'default');
        }
        
        //dump($errors);
        if(count($errors) == 0) {
            $errors['tmp'] = '';
        }
        if(count($values) == 0) {
            $values['tmp'] = '';
        }
        
        $values = Vida_Helpers_AJAXHelper::convert($values);
        $this->view->values = json_encode($values);
        $errors = Vida_Helpers_AJAXHelper::convert($errors);
        $this->view->errors = json_encode($errors);
    }

    /**
     * Страница просмотра файла пользователем.
     */
    public function playAction() {

        $key = $this->getRequest()->getParam('key');

        // сам ролик
    	$model_files = new Model_Files();
    	$_arr = $model_files->fetchByKey($key);
        $this->view->model_file_obj = $_arr;
        $this->view->file_id = $_arr['id'];

        // playlist
		$playlist = new Model_UserPlayList();
		$user_playlist_files = $playlist->getPlayList();

		$played_file_id = $_arr['id'];

		// а есть ли вообще плейлист?
		if($user_playlist_files){

			if (in_array($played_file_id, $user_playlist_files)) {

				// Проигрываемый файл в плейлисте
				// берем элементы ПЕРЕД и ДО проигрываемого.
				list($next_file_id, $prev_file_id) = getNeededValuesFromArray(&$user_playlist_files, $played_file_id);
				$this->view->inplaylist = '<a id="atab1" href="javascript:void(0);" class="misc_current">'.Vida_Helpers_Text::_T('quickplaylist').'</a>';

			} else {
				// Проигрываемый файл НЕ в плейлисте.
				// берем первый и последний добавленные.
				list($next_file_id, $prev_file_id) = getNeededValuesFromArray(&$user_playlist_files, $played_file_id);
				$this->view->inplaylist = '<a id="atab1" href="javascript:void(0);" onclick="toQuickPL(this, '.$_arr['id'].');" class="misc_current">'.Vida_Helpers_Text::_T('inplaylist').'</a>';
			}

			// Заполним быстрый плейлист ////////////
			$quick_playlist_files = array();
			if(count($user_playlist_files) > 10){
				$quick_playlist_files = array_slice($user_playlist_files, 0, self::COUNT_ITEMS_IN_QUICK_PLAYLIST);
			} else {
				$quick_playlist_files = $user_playlist_files;
			}

			$quick_html = '<ul class="quickplaylist">';
			if(count($quick_playlist_files)){
				foreach($quick_playlist_files as $file_id){
					$file_data = $model_files->fetchInfoByID($file_id);
					$quick_html .= '<li id="file_container_'.$file_data['id'].'">
										<div class="video_'.$file_data['id'].'">
											<a class="playlist to_playlist_in miscplaylist" onclick="removeFromQuickPL(this,'.$file_data['id'].');" href="javascript:void(0);"/></a>
										</div>
										<a href="'.$model_files->getDownloadLink($file_data).'" class="s_r_link">'.$file_data['title'].'</a>
								   </li>';
				}
			}
			$quick_html .= '</ul>';
			$this->view->quickplaylist = $quick_html;
			/////////////////////////////////////////

	    	$model_files = new Model_Files();
	    	$__arr_next	= $model_files->fetchById($next_file_id);
   	        $_url_next	= $model_files->getDownloadLink($__arr_next);
	    	$__arr_prev = $model_files->fetchById($prev_file_id);
   	        $_url_prev	= $model_files->getDownloadLink($__arr_prev);

		} else {
			// плелиста вообще нет.
			$_url_next	= 'javascript:void(0);';
			$_url_prev	= 'javascript:void(0);';
			$__arr_prev['title'] = $__arr_next['title'] = Vida_Helpers_Text::_T('needaddfile');

			$this->view->inplaylist = '<a id="atab1" href="javascript:void(0);" onclick="toQuickPL(this, '.$_arr['id'].');" class="misc_current">'.Vida_Helpers_Text::_T('inplaylist').'</a>';
		}

    	$prev_button_playlist = '<a id="prev_area_id" href="'.$_url_prev.'" class="playlist_rew"><b></b><i>'.Vida_Helpers_Text::_T('previousmove').'</i></a>';
		$this->view->prev_button_playlist = $prev_button_playlist;

    	$next_button_playlist = '<a id="next_area_id" href="'.$_url_next.'" class="playlist_ff"><i>'.Vida_Helpers_Text::_T('nextmovie').'</i><b></b></a>
								<script type="text/javascript">                      
								//<![CDATA[
									new Tip(\'next_area_id\', "'.$__arr_next['title'].'",{effect: \'blind\', className: \'silver\', delay: 0.2, hook: {target: \'bottomRight\', tip: \'topLeft\'}}); 
								//]]>
								</script>
								<script type="text/javascript">
								//<![CDATA[
									new Tip(\'prev_area_id\', "'.$__arr_prev['title'].'",{effect: \'blind\', className: \'silver\', delay: 0.2, hook: {target: \'bottomLeft\', tip: \'topRight\'}}); 
								//]]>
								</script>								
								';
		$this->view->next_button_playlist = $next_button_playlist;
		// end playlist

        // кол-во просмотров
        $clickhistory = new Model_ClickHistory();
        $total_csi = $clickhistory->fetchTotalCSIByFileID($key)+1;
        $dec_str = strtolower(declension($total_csi, ''.Vida_Helpers_Text::_T('view').' '.Vida_Helpers_Text::_T('views').' '.Vida_Helpers_Text::_T('viewss').''));
        $this->view->csi = $dec_str;

        // кол-во комментов
        $model_comment = new Model_Comment();
        $total_com = $model_comment->getCountComments($key);
        $dec_str = strtolower(declension($total_com, ''.Vida_Helpers_Text::_T('comment').' '.Vida_Helpers_Text::_T('comments').' '.Vida_Helpers_Text::_T('commentss').''));
        $this->view->com = $dec_str;

        // Блок комментариев
        if($total_com >0){
	        $comment_html = $model_comment->generateHTMLCommentsBlock($key);
	        $this->view->comments_html_block = $comment_html;
        } else {
	        $this->view->comments_html_block = '<li></li>';
        }

        // рейтинг 
        $model_fr		= new Model_FileRate();
        $rate_val		= $model_fr->getRateByFileID($key);
        $rate_percent	= $rate_val*20;
        $this->view->rval = $rate_val;
        $this->view->rper = $rate_percent;

        // embedurl                                                                                                                                                                                                                                                     
        $this->view->videourl1 = $model_files->getDownloadLink($_arr);
		$_embedvideour2 = "<embed src='".Vida_Helpers_Config::get_baseurl()."player-viral.swf' height='376' width='474' allowscriptaccess='always' allowfullscreen='true' flashvars='image=".urlencode($model_files->genImageLink($_arr))."&file=".
							urlencode($model_files->genEmbededLink($_arr))
							."&plugins=viral-1d'/>";
        $this->view->videourl2 = $_embedvideour2;

        // duration 
        $data = $model_files->format($_arr['id']);
        $this->view->duration = $data['duration'];

		$this->view->headTitle($data['category']);
		$this->view->headTitle($data['title']);
        $this->view->headMeta($data['description'], "description");
        $this->view->headMeta($data['filetags'], "keywords");

        $file = null;

        if(!empty($key)) {
            $files_model = new Model_Files();
            $file = $files_model->fetchByKey($key);
        }

        if(empty($file)) {
            throw new Zend_Exception("Файл не существует или удален");
        }

        if($file['state'] != Model_Files::ACTIVE){
            throw new Zend_Exception("Файл обрабатывается системой. Пожалуйста, повторите запрос через некоторое время");
        }

        $this->view->video_url  = $files_model->genLink($file);
        
        //Обновить статистику использования файла
        $files_model->updateStat($file);
    }

    /**
     * Реализует поиск
     */
    public function searchAction() {
        //dump($this->getRequest()->getPost());
        
        $q = $this->getRequest()->getParam('q');
        $category_id = $this->getRequest()->getParam('category_id', '-1');
        if((int)$category_id <= 0) {
            $category_id = null;
        }
        
        $user = $this->getRequest()->getParam('user', '');
        $files_model = new Model_Files();
        $select = null;
        if(!empty($user)) {
            $this->view->phrases = $user;
            $users_model = new Model_Users();
            $row = $users_model->fetchByLogin($user);
            if(!empty($row)) {
                $select = $files_model->getByUserId($row['id']);
            }
        } else {
            $this->view->phrases = $q;
            
            //обработать строку для поиска
            $helper = new Vida_Helpers_SearchHelper();
            $search_arr = $helper->prepareSearchText($q);
            
            if(count($search_arr) > 0) {
                //склеить массив ключевых слов для подсветки слов
                $keys = array();
                foreach($search_arr as $s) {
                    $keys = array_merge($keys, $s);
                }
                $this->view->keys = $keys;
                $select = $files_model->search($q, $category_id);
            }
        }
        
        if(!empty($select)) {
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
        } else {
            $this->view->count = 0;
        }
        
        unset($files_model);
        
    }

    /**
     * Отображение файлов заданной категории
     */
    public function categoryAction() {
        $id = $this->getRequest()->getParam('id');
        $category = null;
        if(!empty($id)) {
            $category_model = new Model_Category();
            $category = $category_model->fetchCategoryDepLang($id);
        }
        if(empty($category)) {
            throw new Zend_Exception("Категория незарегистрирована в системе");
        }
        
        $this->view->category = $category;
        $this->view->headTitle($category);
        
        $files_model = new Model_Files();

        $select = $files_model->getByCategoryId($id);
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
        $entries = array();
        foreach($files as $file)
        {
            $entry = array();
            $entry['id'] = $file['id'];
            $entries[] = $entry;
        }

        $this->view->paginator = $paginator;
        $this->view->entries = $entries;
        
    }
    
    /**
     * Отображение списка воспроизведения пользователя
     */
    public function playlistAction() {
        $playlist_model = new Model_UserPlayList();
        $pl = $playlist_model->getPlayList();
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($pl));
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(
            'my_pagination_control.phtml'
        );
        $paginator->setItemCountPerPage(5);
        $paginator->setView($this->view);
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $this->view->count = $paginator->getAdapter()->count();
        
        $files = $paginator->getCurrentItems();
        $entries = array();
        foreach($files as $file)
        {
            $entry['id'] = $file;
            $entries[] = $entry;
        }

        unset($playlist_model);
        
        $this->view->paginator = $paginator;
        $this->view->entries = $entries;
        
    }

    /**
     * Отображение содержимого облака файлов
     */
    public function tagAction() {
        $id = $this->getRequest()->getParam('id');
        $tag = null;
        if(!empty($id)) {
            $tag_model = new Model_Tags();
            $tag = $tag_model->fetchById($id);
        }
        if(empty($tag)) {
            throw new Zend_Exception("Тег незарегистрирован в системе");
        }
        
        $this->view->tag = $tag['tag'];
        $this->view->headTitle($this->view->tag);
        
        $filetags_model = new Model_FileTags();

        $select = $filetags_model->getFilesByTag($id);
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
        $entries = array();
        foreach($files as $file)
        {
            $entry = array();
            $entry['id'] = $file['file_id'];
            $entries[] = $entry;
        }

        //dump($entries);
        
        $this->view->paginator = $paginator;
        $this->view->entries = $entries;
        
    }
    
    
}

