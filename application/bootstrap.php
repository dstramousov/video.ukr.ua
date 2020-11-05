<?php
// Step 1: APPLICATION CONSTANTS - Set the constants to use in this application.
// These constants are accessible throughout the application, even in ini 
// files. We optionally set APPLICATION_PATH here in case our entry point 
// isn't index.php (e.g., if required from our test suite or a script).

defined('APPLICATION_PATH')
    or define('APPLICATION_PATH', dirname(__FILE__));

defined('PUBLIC_PATH')
    or define('PUBLIC_PATH', APPLICATION_PATH . '/../public/' );

defined('APPLICATION_ENVIRONMENT')
    or define('APPLICATION_ENVIRONMENT', 'development');
//    or define('APPLICATION_ENVIRONMENT', 'production');

require_once "stdlib.php";

require_once Vida_Helpers_File::fix_path(APPLICATION_PATH) . "/constants.php";

// Step 2: FRONT CONTROLLER - Get the front controller.
// The Zend_Front_Controller class implements the Singleton pattern, which is a
// design pattern used to ensure there is only one instance of
// Zend_Front_Controller created on each request.
defined('DAEMON_MODE') or
    define('DAEMON_MODE', false);

defined('ADMIN_AREA') or
    define('ADMIN_AREA', false);

ini_set("memory_limit","512M");
setlocale(LC_CTYPE, array('ru_RU.CP1251', 'Russian_Russia.1251'));

if( !DAEMON_MODE ) {
    $frontController = Zend_Controller_Front::getInstance();
    
    // Step 3: CONTROLLER DIRECTORY SETUP - Point the front controller to your action
    // controller directory.
    //$frontController->setControllerDirectory(APPLICATION_PATH . '/controllers');
    
    // Добавляем каталог с модулями
    $frontController->addModuleDirectory(APPLICATION_PATH . '/modules');
    
    $config = array( 
        'accept_schemes' => 'digest',
        'realm' => 'xxx', 
        'digest_domains' => '/admin',
        'nonce_timeout' => 3600 //время жизни сессии
    );

    $passwd   = APPLICATION_PATH . '/security/htpasswd';
    $resolver = new Zend_Auth_Adapter_Http_Resolver_File();
    $resolver->setFile($passwd);
     
    $adapter = new Zend_Auth_Adapter_Http($config);
    //$adapter->setBasicResolver($resolver);
    $adapter->setDigestResolver($resolver);
    
    /*
    $storage = new Zend_Auth_Storage_NonPersistent; 
    Zend_Auth::getInstance() 
             ->setStorage($storage);
    */
    
    $frontController->registerPlugin(new Vida_Plugins_ModelsIncludePath($adapter));
    
    // Step 4: APPLICATION ENVIRONMENT - Set the current environment.
    // Set a variable in the front controller indicating the current environment --
    // commonly one of development, staging, testing, production, but wholly
    // dependent on your organization's and/or site's needs.
    $frontController->setParam('env', APPLICATION_ENVIRONMENT);
    
    $frontController->setParam('noErrorHandler', false);
    $frontController->throwExceptions(false);
    
    // LAYOUT SETUP - Setup the layout component
    // The Zend_Layout component implements a composite (or two-step-view) pattern
    // With this call we are telling the component where to find the layouts scripts.
    Zend_Layout::startMvc(APPLICATION_PATH . '/layouts/scripts');
    
    // VIEW SETUP - Initialize properties of the view object
    // The Zend_View component is used for rendering views. Here, we grab a "global" 
    // view instance from the layout object, and specify the doctype we wish to 
    // use. In this case, XHTML1 Strict.
    $view = Zend_Layout::getMvcInstance()->getView();
    //$view->doctype('XHTML1_STRICT');
    $view->doctype('XHTML1_TRANSITIONAL');
    //$view->doctype('HTML4_LOOSE');
    // Tell the view where it finds Zend_Dojo ViewHelper
    $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper_');

    if(ADMIN_AREA) {
        //dump($frontController->getRequest());
        //$frontController->setBaseUrl('/');
      //$frontController->setDefaultModule('admin');
      //dump();
      //->setModuleName('admin');
      //$router = $frontController->getRouter();
      //$route = new Zend_Controller_Router_Route(
      //  'admin/:params',
      //  array('module' => 'admin')
      //);
      //$router->addRoute('login', $route);
      //unset($router);
    }
    $router = $frontController->getRouter();
    $route = new Zend_Controller_Router_Route(
        'play/:key',
        array(
            'controller' => 'index',
            'action'     => 'play'
        )
    );
    $router->addRoute('play', $route);

} else {
    $frontController = null;
}// if( !DAEMON_MODE )

// CONFIGURATION - Setup the configuration object
// The Zend_Config_Ini component will parse the ini file, and resolve all of
// the values for the given section.  Here we will be using the section name
// that corresponds to the APP's Environment
$configuration = new Zend_Config_Ini(
    APPLICATION_PATH . '/config/app.ini', 
    APPLICATION_ENVIRONMENT
);

// MAIL TRANSPORT
$tr = new Zend_Mail_Transport_Smtp( $configuration->mail->host, $configuration->mail->toArray() );
Zend_Mail::setDefaultTransport($tr);


// DATABASE ADAPTER - Setup the database adapter
// Zend_Db implements a factory interface that allows developers to pass in an 
// adapter name and some parameters that will create an appropriate database 
// adapter object.  In this instance, we will be using the values found in the 
// "database" section of the configuration obj.
$dbAdapter = Zend_Db::factory($configuration->database);
$dbAdapter->query( 'SET NAMES \'' . $configuration->charset .'\'');

$dbAdapter->query("SET @@local.wait_timeout=900;");
$dbAdapter->query("SET @@wait_timeout=900;");
$dbAdapter->query("SET @@local.interactive_timeout=900;");
$dbAdapter->query("SET @@interactive_timeout=900;");

//$dbAdapter->query( 'SET CHARACTER SET '. $configuration->charset);

//FIXME: ПФЛМАЮБФШ ОБ production
if(APPLICATION_ENVIRONMENT != 'production') {
    $dbAdapter->getProfiler()->setEnabled(true);
}

$cache = Zend_Cache::factory('Core', 'File', 
    array('automatic_serialization' => true, 'lifetime' => 600), 
    array('cache_dir' => APPLICATION_PATH . '/../tmp/'));
Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

// DATABASE TABLE SETUP - Setup the Database Table Adapter
// Since our application will be utilizing the Zend_Db_Table component, we need 
// to give it a default adapter that all table objects will be able to utilize 
// when sending queries to the db.
Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);

// LOG
$logger = new Zend_Log();
//$writer = new Zend_Log_Writer_Stream('php://output');
//$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../logs/app.log');
$writer = new Vida_Log_Writer_Stream(APPLICATION_PATH . '/../logs/app.log');
$logger->addWriter($writer);
if(!is_null($configuration->log->priority)) {
    $logger->addFilter((int)$configuration->log->priority);
}
unset($writer);

// REGISTRY - setup the application registry
// An application registry allows the application to store application 
// necessary objects into a safe and consistent (non global) place for future 
// retrieval.  This allows the application to ensure that regardless of what 
// happends in the global scope, the registry will contain the objects it 
// needs.
$registry = Zend_Registry::getInstance();
$registry->configuration = $configuration;
$registry->dbAdapter     = $dbAdapter;
$registry->set('cache', $cache);
$registry->set('logger', $logger);

// default language when requested language is not available
$language = 'uk';

//if(null !== ($request = Zend_Controller_Front::getInstance()->getRequest())) {
if( !DAEMON_MODE ) {
$tmp = '';
$set_cookie = false;
if(array_key_exists('lang', $_COOKIE) && null !==  $_COOKIE['lang']) {
    //$tmp = $request->__get('lang');
    $tmp = strtolower($_COOKIE['lang']);
} else {
    $tmp = Vida_Helpers_Text::detectLanguage();
    $set_cookie = true;
}

if(!empty($tmp) && in_array($tmp, array("ru", "en", "uk"))) {
  $language = $tmp;
  if($set_cookie) {
    setcookie('lang', $language, mktime() + 7200, '/', '', false);
  }
}

}
//dump($language);

$translate = new Zend_Translate('tmx', APPLICATION_PATH . '/../languages/translation.tmx', $language);
$translate->setLocale($language);
$registry->set('translate', $translate);

// CLEANUP - remove items from global scope
// This will clear all our local boostrap variables from the global scope of 
// this script (and any scripts that called bootstrap).  This will enforce 
// object retrieval through the Applications's Registry
unset($frontController, $view, $configuration, $dbAdapter, $registry, $logger, $cache);

setlocale(LC_CTYPE, array('ru_RU.CP1251', 'Russian_Russia.1251'));


