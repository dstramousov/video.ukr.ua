<?php

class Model_Comment extends Vida_Model
{
    protected $_className = "DbTable_Comment";

    const ST_COMMENT_POSTED		= 1;	// нерассмотренный
    const ST_COMMENT_ACCEPTED	= 2;	// разрешен
    const ST_COMMENT_DECLINED	= 3;	// отклонен

    /**
    * Удалить все записи тегов по идентификатору файла
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
     * Сгенерирует готовый html блок для вставки на страницу
     * 
     * @param  int $file_id file
     * @return $ret - string with ready HTML
     */
    public function generateHTMLCommentsBlock($file_id)
    {
    	$ret = '';

		$auth = Zend_Auth::getInstance();
		$comment_answer_possible = $auth->hasIdentity();

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('cm' => 'comment'), array('*'))
            ->where('cm.file_id=?', $file_id)
            ->where('cm.stay=?', Model_Comment::ST_COMMENT_ACCEPTED)
			->joinLeft(array('u' => 'users'), 'u.id = cm.user_id', array('u.login'))
            ->where('cm.parent_id=?', 0)
            ->order('cm.created DESC');

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);

   		$recursion_level = 0;
        $count_comments_for_form = 0;
        foreach($rows as $iterator=>$comment){

	        $count_comments_for_form++;

		    // проверим есть ли у данного комментария подкомментарии
	        $select_int = Zend_Registry::getInstance()->dbAdapter->select();
	        $select_int = $select_int
	            ->from(array('cmm' => 'comment'), array('count(*) as count'))
	            ->where('cmm.stay=?', Model_Comment::ST_COMMENT_ACCEPTED)
	            ->where('cmm.parent_id=?', $comment['id']);

	        $rows = Vida_Helpers_DB::fetchRow(null, $select_int, true);

		    if($rows['count'] != 0) {

			    // У комментарий есть ответы и мы их выводим
				// сначала главный комент.
			    $ret .= $this->get_html_for_comment(&$comment, $comment_answer_possible);

			    // затем подкомментарии.
			    $ret .= $this->get_recurse_comments($file_id, $comment['id'], $recursion_level, $count_comments_for_form, $comment_answer_possible);

		    } else { 
				// Комментарий без ответов.
				$ret .= $this->get_html_for_comment(&$comment, $comment_answer_possible);
		    }

        } // конец цикла по коментам.

        return $ret;
    }

    /**
     * Рекурсивная выборка коментариев
     */
    private function get_recurse_comments($file_id, $comment_id, $recursion_level, $count_comments_for_form, $comment_answer_possible=false){

    	if($recursion_level>10){dump($comment_id);}

		$recursion_level++;
		$css_level = $recursion_level + 1;

    	$return_html = '';
    	$sub_comment_count = 0;

        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('cm' => 'comment'), array('*'))
            ->where('cm.file_id=?', $file_id)
            ->where('cm.stay=?', Model_Comment::ST_COMMENT_ACCEPTED)
            ->where('cm.parent_id=?', $comment_id)
			->joinLeft(array('u' => 'users'), 'u.id = cm.user_id', array('u.login'))
            ->order('cm.created DESC');

        $rows = Vida_Helpers_DB::fetchAll(null, $select, true);

        foreach($rows as $iterator=>$sub_comment){

            $sub_comment_count++;
			$css_level = $recursion_level+1;

            $return_html .= '<li>
								<div class="comment_block c_b_lvl'.$css_level.'">
									<div class="comment_username">'.Vida_Helpers_Text::_T('wrote').'&nbsp;<a href="'.Vida_Helpers_Config::get_baseurl().'index/search/user/'.$sub_comment['login'].'">'.$sub_comment['login'].'</a></div>
									<div class="comment_date">'.Vida_Helpers_DateHelper::mysql_to_unix_date_customize($sub_comment['created']).'</div>
									<div class="comment_text">'.$sub_comment['body'].'</div>
									'.($comment_answer_possible ? '<a href="javascript:void(0);" onclick="makeAnswer(\''.$sub_comment['id'].'\');" class="comment_answer">'.Vida_Helpers_Text::_T('reply').'</a>' : '').'
								</div>
							 </li>';

		    // проверим есть ли у данного комментария подкомментарии
	        $select_int = Zend_Registry::getInstance()->dbAdapter->select();
	        $select_int = $select_int
	            ->from(array('cmm' => 'comment'), array('count(*) as count'))
	            ->where('cmm.stay=?', Model_Comment::ST_COMMENT_ACCEPTED)
	            ->where('cmm.parent_id=?', $sub_comment['id']);

	        $rows_int = Vida_Helpers_DB::fetchRow(null, $select_int, true);

		    if($rows_int['count'] != 0) {
	            $return_html .= $this->get_recurse_comments($file_id, $sub_comment['id'], $recursion_level, $count_comments_for_form, $comment_answer_possible);
			}
		}

        return $return_html;
    }

    /**
     * Генерация блока по коментам.
     */
    private function get_html_for_comment(&$row, $comment_answer_possible){
    	
        $ret = '<li>
					<div class="comment_block">
						<div class="comment_username">'.Vida_Helpers_Text::_T('wrote').'&nbsp;<a href="'.Vida_Helpers_Config::get_baseurl().'index/search/user/'.$row['login'].'">'.$row['login'].'</a></div>
						<div class="comment_date">'.Vida_Helpers_DateHelper::mysql_to_unix_date_customize($row['created']).'</div>
						<div class="comment_text">'.$row['body'].'</div>'.
						($comment_answer_possible ? '<a href="javascript:void(0);" onclick="makeAnswer(\''.$row['id'].'\');" class="comment_answer">'.Vida_Helpers_Text::_T('reply').'</a>' : '').'
					</div>
				</li>';

        return $ret;
    }

    /**
     * ПОлучить кол-во комментов для данного файла.
     * 
     * @param int $file_id file
     * @return $ret - int - count comments
     */
    public function getCountComments($file_id)
    {
        $select = Zend_Registry::getInstance()->dbAdapter->select();
        $select = $select
            ->from(array('cm' => 'comment'), array('count(id) as count'))
            ->where('cm.file_id=?', $file_id)
            ->where('cm.stay=?', Model_Comment::ST_COMMENT_ACCEPTED);

        $rows = Vida_Helpers_DB::fetchRow(null, $select, true);

        return $rows['count'];
    }
}
