<?php
/**
 * ProfileLink helper
 *
 * Call as $this->profileLink() in your layout script
 */
class Vida_View_Helper_ProfileLink extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function profileLink()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
            return '<li><a href="/profile">����� ����������, ' . $username .  '</a></li>' . 
                '<li><a href="/login/logout">�����</a></li>';
        }

        return '<li><a href="/login">��������������</a></li><li><a href="/register">������������������</a></li>';
    }
}