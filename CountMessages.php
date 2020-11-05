<?php
/**
 * ¬ыводит признак того что у нас есть сообщени€ и их кол-во.
 *
 * Call as $this->profileLink() in your layout script
 */
class Vida_View_Helper_countMessages extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function countMessages($count_sight=false)
    {
    	$user = new Model_Users();
		$user_arr = $user->fetchCurrentUser();
		if(!$user_arr){return '';}

		$message_model = new Model_Messages();

		$new_mess_count = $message_model->getCountUsersMessages($user_arr['id'], true);

		if(!$new_mess_count){return '';}

		// <b>0</b>|
		if($count_sight){
	    	$ret = '&nbsp;(<i title="—истемные сообщени€">'.$new_mess_count.'</i>)';
		} else {
	    	$ret = '</a><a href="/pspace/mymessages" class="new_sys_message"></a>';
		}

		return $ret;
    }
}