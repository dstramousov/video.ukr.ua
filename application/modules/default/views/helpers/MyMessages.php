<?php
/**
 * Выводит сообщения пользователя.
 * pspace/mymessages
 * 
 * return
 */
class Vida_View_Helper_MyMessages extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function MyMessages()
    {
    	$ret = '';

    	$user = new Model_Users();
		$user_arr = $user->fetchCurrentUser();
		if(!$user_arr){return '';}

		$message_model = new Model_Messages();
		$new_mess_count = $message_model->getUsersMessages($user_arr['id'], false);

		$_cm = count($new_mess_count);
		if($_cm == 0){
			$ret = '<div id="zerromassages">'.Vida_Helpers_Text::_T('zerromassages').'</div>';
		} else { $ret = '<div id="zerromassages"></div>'; }

		$ret .= '<div id="hiddencountmessages" style="visibility:hidden;">'.$_cm.'</div>';

		foreach($new_mess_count as $iterator=>$message){

			$readed_sight = ($message['status']==Model_Messages::ST_MESSAGE_READED)?'s_m_readed':'';

			$ret .= '<div class="system_message s_pr'.$message['priority'].' '.$readed_sight.'" id="message_'.$message['id'].'">
						<h1>'.Vida_Helpers_Text::_T('systemmessages').'</h1><a href="javascript:void(0);" onclick="deleteMessage('.$message['id'].')" class="s_m_close">'.Vida_Helpers_Text::_T('deletemess').'</a>
						<div class="system_m_date">'.Vida_Helpers_DateHelper::mysql_to_unix_date_customize($message['created']).'</div>
						<div>'.$message['body'].'</div>
					</div>
					';
		}

		return $ret;
    }
}