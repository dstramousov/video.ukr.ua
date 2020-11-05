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
            return '<li><a href="/profile">Добро пожаловать, ' . $username .  '</a></li>' . 
                '<li><a href="/login/logout">Выйти</a></li>';
        }

        return '<li><a href="/login">Авторизоваться</a></li><li><a href="/register">Зарегистрироваться</a></li>';
    }
}