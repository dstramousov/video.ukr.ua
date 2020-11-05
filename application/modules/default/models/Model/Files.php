<?php
class Model_Files extends Vida_Model
{
    protected $_className = "DbTable_Files";

    const CREATED		= 0;	// ����� ��������, ������� ������ �� ����������� ������
    const ACTIVE		= 1;	// ����� �������������� � ����� � ������������
    const USER_CLOSE	= 2;	// ����� �������������� �� ������ � ��������� �������������

    const ADMIN_CLOSE	= 100;	// ����� �������������� �� ������ � ��������� ������� ���������
    const DELETED		= 101;	// ����� ������ �� �������
    
    const IMAGE_EXT = 'jpg';    //���������� ����� ������ �����������
    const LOG_EXT = 'log';      //���������� ����� ���� �����������
    const PREVIEW_SUFFIX = '_preview';
    
    const THUMB_IMAGE = 0;      //�������� thumbs
    const PREVIEW_IMAGE = 1;    //�������� preview

	public static function paramsDecode($data) {
		$params = $data['params'];
		if(!isset($params)) {
			$params = array();
		} else {
			if(is_string($params)) {
				$params = unserialize($data['params']);
			}
		}
		return $params;
	}
	
	public static function paramsEncode($data) {
		$params = $data['params'];
		if(!isset($params)) {
			$params = array();
		}
		return serialize($params);
	}

    /**
     * �������� ������ ������ ������������
     * @param integer $user_id
     * @return Zend_DbTable_Row | null
     */
    public function getByUserId($user_id) {
        $table = $this->_getTable();
        $name = $table->info(Zend_Db_Table::NAME);
        $db = $table->getAdapter();
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('f' => $name), array('f.id as file_id'))
            ->where(new Zend_Db_Expr($db->quoteIdentifier('user_id') . '=' . $db->quote($user_id)))
            ->order('created DESC');
        return $select;        
    }

    
    /**
     * ����� ������� �� ���������
     * @param string $phrases ����� ��� ������
     * @param $integer $category_id ������ ����� ��������� ������
     * @return Zend_Db_Select
     */
    public function search($phrases, $category_id = null) {
        $helper = new Vida_Helpers_SearchHelper();
        
        //���������� ������ ��� ������
        $search_arr = $helper->prepareSearchText($phrases);
        
        $table = $this->_getTable();
        $db = $table->getAdapter();
        $name = $table->info(Zend_Db_Table::NAME);
        
        $selects = array();
        foreach($search_arr as $keys) {
            $select = Zend_Registry::getInstance()->dbAdapter->select();
            $select = $select
                ->from(array('f' => $name), array('f.id as file_id', 'f.key', 'f.title', 'f.created', 'f.user_id', 'f.category_id'))
                ->joinLeft(array('fp' => 'fileprop'), 'fp.file_id = f.id', array('fp.description'));
            foreach($keys as $key) {
                $select = $select
                    ->Orwhere('UPPER(f.title) LIKE ' . strtoupper($db->quote('%' . $key . '%')))
                    ->Orwhere('UPPER(fp.description) LIKE ' . strtoupper($db->quote('%' . $key . '%')));
            }
            $selects[] = $select;
        }
        $union = Zend_Registry::getInstance()->dbAdapter->select()    
            ->union($selects, Zend_Db_Select::SQL_UNION_ALL);

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('t' => $union), array('file_id', 'key', 'title', 'category_id', 'user_id'))
            ->order('created DESC');
            
        if(!empty($category_id)) {
            $select = $select
                ->where(new Zend_Db_Expr($db->quoteIdentifier('t.category_id') . '=' . $db->quote($category_id)));
        }
            
        return $select;
    }
    
    /**
     * ��������� ������� ������ �� �������� ���������
     * @param integer $category_id
     * @return Zend_Db_Select
     */
    public function getByCategoryId($category_id) {
        $table = $this->_getTable();
        $db = $table->getAdapter();
        $select = $this->select('created');
        $select = $select
            ->where(new Zend_Db_Expr($db->quoteIdentifier('category_id') . '=' . $db->quote($category_id)));
        return $select;        
    }
    
    /**
     * ���������� select ��� ���������� ������
     * @param integer @limit ���-�� ���������
     * @return array
     */
    public function fetchTopSelect1($limit, $user_id = null) {
        $table = $this->_getTable();
        $db = $table->getAdapter();
        $name = $table->info(Zend_Db_Table::NAME);
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('f' => $name), array('f.requested', 'f.fname', 'f.key', 'f.user_id'))
            ->order('f.requested DESC')
            ->order('f.created DESC');
        if(!empty($user_id)) {
            $select = $select
                ->where(new Zend_Db_Expr($db->quoteIdentifier('f.user_id') . '=' . $db->quote($user_id)));
        }
        if(!empty($limit)) {
            $select = $select->limit($limit);
        }
        $rows = Vida_Helpers_DB::fetchAll(null, $select);
        return $rows;
    }

    /**
     * ���������� ������ ��� ���������� ������
     * @return string base URL for download flv video
     */
    protected function _getWeb($data = null) {
        return Vida_Helpers_Config::fix_url(Zend_Registry::getInstance()->configuration->site->dl);
    }

    /**
     * ���������� ��������� ��� ����� ��� ���������� ��������� � �������
     * @param array $data - ������� ���������
     * @return string ������ ���� ���������� �����
     */
    protected function _getTemp($data) {
        $tmp = Vida_Helpers_File::fix_path(Vida_Helpers_Config::prepare(Zend_Registry::getInstance()->configuration->file->tmp));
        if(Vida_Helpers_File::check_dir($tmp)) {
            $p = $tmp . $data['id'];
            if(@file_exists($p)) {
                unlink($p);
            }
        }
        return $p;
    } 
    
    //FIXME: �������� ��� Windows
    //http://technet.microsoft.com/en-us/sysinternals/bb896768.aspx
    function _symlink( $target, $link ) {
      if ($_SERVER['WINDIR'] || $_SERVER['windir']) {
        exec('junction "' . $link . '" "' . $target . '"');
      } else {
        symlink($target,$link);
      }
    }

    /**
     * ���������� url ��� �������� ��������� �����
     * @param array $data
     * @return string
     */
    public function getDownloadLink($data) {
        return Vida_Helpers_Config::get_baseurl() . 'play/' . $data['key'];
    }
    
    /**
     * ���������� ���� ��� ���������� �����-�����
     * @param array $data 
     */
    public function genLink($data) {
        $url = $this->_getWeb($data) . $this->_getDirPrefix($data);
        return $url . $data['id'];
    }

    /**
     * ���������� ���� ��� embeded url
     * @param array $data 
     */
    public function genEmbededLink($data) {
    	$url = str_replace('//', '/',Vida_Helpers_Config::get_baseurl()."".$this->genLink($data));
        return $url.'.flv';
    }

    /**
     * ���������� ���� ��� ���������� ������ �����
     * @param array $data 
     */
    public function genImageLink($data, $type = self::THUMB_IMAGE) {
        if($data['state'] ==  self::CREATED) {  //���� ��� �� ��������� ��������
            return Vida_Helpers_Config::get_baseurl() . 'images/file_in_process_'. Vida_Helpers_Text::_L() .'.gif';
        }
        $url = $this->_getWeb($data) . $this->_getDirPrefix($data) . $data['id'];
        if($type == self::PREVIEW_IMAGE) {
            $url = $url . self::PREVIEW_SUFFIX;
        }
        return $url . '.' . self::IMAGE_EXT;
    }

    /**
     * ��������� ��������� ����� (���-�� �������� ������)
     * @param array $data
     * @return none
     */
    public function updateStat($data) {
        $row = $this->fetchById($data['id']);
        if(!empty($row)) {
            $row['requested'] = $row['requested'] + 1;
            $row['accessed'] = mktime();
            $this->update($row);
        }

        //�������� ���������� ��������� ������        
        $ch_model = new Model_ClickHistory();
        $ch_model->update($data['id']);
    }

    /**
     * Delete entry by id
     * @param integer $file_id ������������� �����
     * @return none
     */
    public function deleteById($file_id)
    {
        //������� ���� �� playlist-a
        $userplaylist = new Model_UserPlayList();
        $userplaylist->deleteByFileId($file_id);
        
        $tasks_model = new Model_Tasks();
        $tasks_model->deleteByFileId($file_id);
        
        $fileprop_model = new Model_FileProp();
        $fileprop_model->deleteByFileId($file_id);

        $filetags_model = new Model_FileTags();
        $filetags_model->deleteByFileId($file_id);

        $foldersfile_model = new Model_FoldersFile();
        $foldersfile_model->deleteByFileId($file_id);

        // ���������� �� �����        
        $ch_model = new Model_ClickHistory();
        $ch_model->deleteByFileId($file_id);

        // ������ �� �����        
        $ab_model = new Model_Abuses();
        $ab_model->deleteByFileId($file_id);
        
        // ����������� � �����
        $comment_model = new Model_Comment();
        $comment_model->deleteByFileId($file_id);
        
        $data = $this->fetchById($file_id);
        
        $dir = $this->_getDir($data);

        //������� ���� �����
        if(file_exists($dir . $data['path'])) {
            unlink($dir . $data['path']);
        }
        
        //������� ���� thumb
        $img = $dir . $data['path'] . '.' . self::IMAGE_EXT;
        if(file_exists($img)) {
            unlink($img);
        }

        //������� ���� preview
        $img = $dir . $data['path'] . self::PREVIEW_SUFFIX . '.' . self::IMAGE_EXT;
        if(file_exists($img)) {
            unlink($img);
        }
        
        //������� ���� ����
        $img = $dir . $data['path'] . '.' . self::LOG_EXT;
        if(file_exists($img)) {
            unlink($img);
        }

        // ���� � ����� ������ ��� ������, �� ������� � ��
        if(file_exists($dir)) {
            $files = Vida_Helpers_File::files_list($dir);
            if(count($files) == 0) {
                rmdir($dir);
            }
        }
        
        return parent::deleteById($file_id);
    }

    /**
     * ���������� ���������� �����
     * @param string $filename ��� �����
     * @ret string ���������� ��� ������ ������
     */
    protected function _getExtension($filename) {
        $path_info = pathinfo($filename);
        //dump($path_info);
        return strtolower($path_info['extension']);
    }

    /**
     * ������� ����� ����� (������ �� �����, ����������� ��������)
     * @param string $tags ������ �����, ����������� ��������
     * @return array ���������� ���� �� ������
     */
    protected function _parseTags($tags) {

        if(empty($tags)) {
            return null;
        }
        $res = array();
        $tags = strip_tags($tags);
        $regexp = '/([A-Z�-�a-z�-�]+[^A-Z�-�a-z�-�\,]*)+/i';
        preg_match_all($regexp, $tags, $matches);
        if(null!== $matches && count($matches[0]) > 0) {
            foreach($matches[0] as $phrase) {
                $res[] = $phrase;
            }
        }
        unset($matches);
        return $res;
    }
    
    /**
     * ��������� ���� �� ����� �����
     * @param sting $data �������� �����
     * @return string ����������� ����
     */
    protected function _getKey($data) {
        return $data['id'];
    }

    /**
     * ���������� ���������� ��� ��� ����� � ��������� �����
     * @param array $data
     */        
    protected function _getUnique($data) {
        $dir = $this->_getDir($data);
        $fname = md5($data['fname'] . $data['created']);
        if(Vida_Helpers_File::check_dir($dir)) {
            $c = 0;
            while(@file_exists($dir . $fname) && $c < 50 ) {
                $fname = md5($data['fname'] . $data['created'] . rand(1, 50));
                $c++;
            }
        }
        return $fname;
    }
    
    protected function _getDirPrefix($data) {
        return date('YW', $data['created']) . '/';
    }
    
    /**
    * ���������� ���������� ���� ��� �������� ����� ������� file_id
    * @param array $data
    * @return string
    */
    protected function _getDir($data) {
        $path = Vida_Helpers_File::fix_path(Vida_Helpers_Config::prepare(Zend_Registry::getInstance()->configuration->file->storage));
        return $path . $this->_getDirPrefix($data);
    }

    /**
     * ���������� ������ � �����
     */
    public function update(array $data)    {
        
        $file_id = $data['id'];
        
		if(array_key_exists('params', $data) && is_array($data['params'])) {
			$data['params'] = self::paramsEncode($data);
		}
        
        //���������� ����� �����
        if(array_key_exists('tags', $data)) {
            $tags = $this->_parseTags($data['tags']);
            if(!empty($tags)) {
                $tags_model = new Model_Tags();
                $filetags_model = new Model_FileTags();
                $filetags_model->deleteByFileId($file_id);
                $tmp = array();
                $tmp['file_id'] = $file_id;
                foreach($tags as $tag) {
                    $tmp['tag_id'] = $tags_model->getTagId($tag);;
                    $filetags_model->save($tmp);
                }
            }
        }
            
        //���������� ��-� (�������� � �.�.)
        if(array_key_exists('description', $data)) {
            $descr = substr(Vida_Helpers_Text::purge($data['description']), 0, 255);
            if(!empty($descr) && strlen($descr) > 0) {
                $fileprop_model = new Model_FileProp();
                $fileprop_model->deleteByFileId($file_id);
                $prop = array('file_id' => $file_id);
                $prop['description'] = $descr;
                $fileprop_model->save($prop);
            }
        }
        parent::update($data);
    }
    
    /**
    * Save a new entry
    * 
    * @param  array $data 
    * @return int|string
    */
    public function save(array $data, $tmp_name)
    {
        $data['created'] = mktime();
        $data['accessed'] = mktime();
        $data['user_id'] = $data['user_id'];
        $data['requested'] = 0;
        $data['category_id'] = $data['category_id'];
        $data['state'] = self::CREATED;
        $data['path'] = '';
        $data['key'] = '';
        
		if(array_key_exists('params', $data) && is_array($data['params'])) {
			$data['params'] = self::paramsEncode($data);
		}

        $file_id = parent::save($data);

        $data['id'] = $file_id;
        
        //����� ��������� ����� ��������� id        
        $data['path'] = $data['id'];
        $data['key'] = $this->_getKey($data);
        $this->update($data);

        //����������� ���� �� ��������� ���������        
        $tmp_f = $this->_getTemp($data);
        
        if(!Vida_Helpers_File::check_dir($this->_getDir($data)) || !move_uploaded_file($tmp_name, $tmp_f)) {
            throw new Zend_Exception('������ �������� ����� �� ������');
        }
        
        //������� ������ �� ���������������
        $task['file_id'] = $file_id;
        $task['params'] = array('tmp_file' => $tmp_f, 'fsize' => 0, 'fname' => $this->_getDir($data) . $data['path']);
        
        $tasks_model = new Model_Tasks();
        $tasks_model->save($task);
    
        //�������� ����� ������������
        $userstree_model = new Model_UsersTree();
        $foldersfile_model = new Model_FoldersFile();
        $ffile = array();
        $folder_id = null;
        if(array_key_exists('folder_id', $data) && $data['folder_id'] > 0) {
            $folder_id = $data['folder_id'];
        } else {
            $folder_id = $userstree_model->getDefaultFolder($data['user_id']);
        }
        if(!empty($folder_id)) {
            $ffile['folder_id'] = $folder_id;
            $ffile['file_id'] = $file_id;
            $foldersfile_model->save($ffile);
        }
        unset($userstree_model, $foldersfile_model);
        
        return $file_id;        
    }

    /**
    * Select all user files
    * 
    * @param  string $file_id file
    * @return null|Zend_Db_Table_Row_Abstract
    */
    public function select($orderBy = null)
    {
        if(null === $orderBy) {
            $orderBy = "id";
        }
        $table = $this->_getTable();
        $select = $table->select();
        $select = $select->order(new Zend_Db_Expr($orderBy));

        return  $select;
    }

    /**
     * Fetch an individual entry
     * 
     * @param  string $key
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByKey($key)
    {
        $row = $this->fetchRowByCol('key', $key);
        return $row;
    }

    /**
     * Fetch an path
     * 
     * @param  string $key
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchByPath($path)
    {
        $row = $this->fetchRowByCol('path', $path);
        return $row;
    }
    
    /**
     * ������� ��� ������ �� ������������ ����
     * @param integer $requested
     * @return array
     */
    public function selectAllToDate($date)
    {
        $table = $this->_getTable();
        $select = $table->select()
            ->where('accessed <= ?', $date);
        return $select;
    }


    /**
     * ���������� ���������� � �����-������ (human readable)
     * ������������ ��� ����������� ���������� � ����� �����
     * @param integer $file_id
     * @return array
     */
    public function format($file_id, $keys = null) {

        $data = array();
        $file = $this->fetchById($file_id);
        if(!empty($file)) {
            $params = self::paramsDecode($file);
            
            $data['file_id'] = $file_id;
            $data['key'] = $file['key'];
            $data['title'] = $file['title'];
            $data['alt'] = Vida_Helpers_Text::preview($file['title']);
            $data['created'] = $file['created'];
            $data['state'] = $file['state'];
            
            //����� ����������, ������, �������� ���������
            $data['image_url'] = $this->genImageLink($file);
            $data['preview_image_url'] = $this->genImageLink($file, self::PREVIEW_IMAGE);
            
            $data['video_url'] = $this->getDownloadLink($file);
            $data['stream_url'] = $this->genLink($file) . '.flv';
            
            //������������� streamer ��� seek �������                
//            $data['streamer'] = $this->_getWeb($file);
//            $data['streamer'] = preg_replace('/(.*)\/$/i', '$1', $data['streamer']);
//            $data['file'] = $this->_getDirPrefix($file) . $file['id'] . '.flv';
            
            //���������� � ��������� ������
            $data['owner_id'] = $file['user_id'];
            $users_model = new Model_Users();
            $user = $users_model->fetchById($data['owner_id']);
            if(empty($user)) {
               throw new Zend_Exception(sprintf("��������� ����������� ��. ������������ %d �� ������", $data['owner_id']));
            }
            $data['owner_login'] = $user['login'];
            $data['owner_lname'] = $user['lname'];
            $data['owner_fname'] = $user['fname'];
            $data['owner_email'] = $user['email'];
            $data['owner_url'] = '/index/search/user/' . $user['login'];
            unset($user, $users_model);
            
            //���������� ����������
            $model_filerate = new Model_FileRate();
            $rate_val = $model_filerate->getRateByFileID($file_id);
            if(empty($rate_val)) {
                $rate_val = 0;
            }
            $data['rate_percent'] = $rate_val * 20;
            $data['reviewed'] = $file['requested'];
            unset($model_filerate);
            
            //���� � ��������
            $fileprop_model = new Model_FileProp();
            $prop = $fileprop_model->fetchByFileId($file_id);
            if(is_array($prop)){
                $data['description'] = $prop['description'];
            } else {
                $data['description'] = '';
            }
            unset($fileprop_model, $prop);
    
            //���� �����
            $filetags_model = new Model_FileTags();
            $tags = $filetags_model->fetchByFileId($file_id);
            if(!empty($tags)) {
                $tag_model = new Model_Tags();
                $tag_descr = array();
                foreach($tags as $t) {
                    $tag = $tag_model->fetchById($t['tag_id']);
                    $tag_descr[] = $tag['tag'];
                    unset($tag);
                }
                $data['filetags'] = implode(', ', $tag_descr);
            } else {
                $data['filetags'] = '';
            }
            unset($filetags_model, $tags);
            
            //���������
            $category_model = new Model_Category();
            $data['category_id'] = $file['category_id'];
            $data['category'] = $category_model->fetchCategoryDepLang($file['category_id']);
            $_helper = new Zend_Controller_Action_Helper_Url();
            $data['category_url'] = $_helper->url(
                array(
                    'controller' => 'index',
                    'action'     => 'category',
                    'module'     => 'default',
                    'id'         => $data['category_id']
                )
            );
            unset($category_model);
            
            //���-�� ������������
            $comment_model = new Model_Comment();
            $comment_model->getCountComments($file_id);
            $data['comments_count'] = 0;

            $data['related_xml'] = Vida_Helpers_Config::get_baseurl() . 'storage/related/id/' . $data['file_id'];
            
            unset($comment_model);
            
            //���� ������
            if(is_array($params) && array_key_exists('duration', $params)) {
                $data['duration'] = $params['duration'];
            } else {
                $data['duration'] = '00:00';
            }
            unset($file, $params);
            
            //��������� ����������� ������
            if(!empty($keys)) {
                $helper = new Vida_Helpers_SearchHelper();
                $data['title'] = $helper->highlightWords($data['title'], $keys);
                $data['description'] = $helper->highlightWords($data['description'], $keys);
                unset($helper);
            }
        }
        return $data;
    }
    
    /**
     * 
     * @param inetger $id
     * @return array @ret ������ � �����
     */
    public function fetchInfoByID($id) {


    	$ret = array();
    	$ret = $this->fetchById($id);

		$fileprop_model = new Model_FileProp();

		$_arr = $fileprop_model->fetchByFileId($ret['id']);
    	if(is_array($_arr)){
			$ret['description'] = $_arr['description'];
		} else {
			$ret['description'] = '';
		}


        //���� �����
        $filetags_model = new Model_FileTags();
        $tags = $filetags_model->fetchByFileId($ret['id']);
        if(!empty($tags)) {
            $tag_model = new Model_Tags();
            $tag_descr = array();
            foreach($tags as $t) {
                $tag = $tag_model->fetchById($t['tag_id']);
                $tag_descr[] = $tag['tag'];
                unset($tag);
            }
            $ret['filetags'] = implode(', ', $tag_descr);
        } else {
            $ret['filetags'] = '';
        }

        return $ret;
    }


    /**
     * ���������� select ��� ���������� ������
     * @param integer @limit ���-�� ���������
     * @return array
     */
    public function fetchTopSelect($limit) 
    {
    	$_arr = array();
		$model_ch = new Model_ClickHistory();
	    $_arr = $model_ch->fetchByInterval($limit, CONST_COUNT_TOP_VIDEO);
        return $_arr;
    }

    /**
     * ���������� select ��� ������� ����������� ������
     * @param integer @limit ���-�� ���������
     * @return array
     */
    public function fetchNearestSelect($limit) 
    {
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('f' => 'files'))
            ->where('f.state=?', Model_Files::ACTIVE)
			->joinInner(array('u' => 'users'), 'u.id = f.user_id', array('u.lname', 'u.fname'))
			->order('f.created DESC')
			->limit($limit);

        $rows = Vida_Helpers_DB::fetchAll(null, $select);

        return $rows;
    }

    /**
     * ���������� key ��� ����������� �����
     * @param 
     * @return string $key     */
    public function fetchRandomTopFile() {

    	$file_data = null;
    	
	    $_arr = self::fetchTopSelect(ST_REQUEST_TOP_TYPE_ALL);
        
        if(!empty($_arr)) {
            $rand_keys = array_rand($_arr, 1);
            $file_id = $_arr[$rand_keys]['file_id'];
    
            $file_data = self::fetchInfoByID($file_id);
    
            return $file_data;
        } else {
            return null;
        }
    }
    
    

}