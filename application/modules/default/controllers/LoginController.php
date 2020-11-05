<?php

class LoginController extends Vida_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('authorize', 'json');
        $ajaxContext->initContext();
    }
    
    public function preDispatch()
    {
        /*
        if (Zend_Auth::getInstance()->hasIdentity()) {
            // If the user is logged in, we don't want to show the login form;
            // however, the logout action should still be available
            if ('logout' != $this->getRequest()->getActionName()) {
                $this->_helper->redirector('index', 'index');
            }
        }
        */
        parent::preDispatch();
    }

    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->redirector('index');
    }

    public function authorizeAction()
    {
        $request = $this->getRequest();
        $res = array();
        $res['result'] = 0;
        // Check if we have a POST request
        if ($request->isPost()) {
            $values = $request->getPost();
            // Get our authentication adapter and check credentials
            if(false !== ($ret = Model_Users::authorize($values))) {
                $res['result'] = 1;
                $res['sessionId'] = $ret['sessionId'];
            } else {
                $res['result'] = 0;
            }
            /*
            $adapter = $this->getAuthAdapter($values);
            if(array_key_exists('password', $values) && array_key_exists('username', $values)) {
                $values['password'] = md5($values['password']);
                $auth    = Zend_Auth::getInstance();
                $result  = $auth->authenticate($adapter);
                $res['result'] = $result->isValid() ? 1 : 0;
            } else {
                $res['result'] = 0;
            }
            */
        }
        $this->_helper->json->sendJson($res);
    }
    
    /**
    *
    */
    private function getAuthAdapter($values) {
        $db = Zend_Registry::getInstance()->dbAdapter;
        $authAdapter = new Zend_Auth_Adapter_DbTable($db);
        $authAdapter
            ->setIdentity($values['username'])
            ->setCredential($values['password'])
            ->setTableName('users')
            ->setIdentityColumn('login')
            ->setCredentialColumn('password')
            ->setCredentialTreatment('? AND state=1');
//            ->setCredentialTreatment('MD5(?) AND state=1');
        return $authAdapter;
    }

    public function logoutAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Auth::getInstance()->clearIdentity();
        Model_Users::logout();
        $this->_helper->redirector('index', 'index'); // back to login page
    }
}