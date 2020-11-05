<?php
    class protocolWrapper {

        var $ch;            // curl object
        var $base_url;      // base url for request 
        var $BR = "\n";
    //  var $BR = "<br/>";


        function __construct($_base_url) {
            $this->connection_init($_base_url);
        }

        ///////////////////////////////////////////////////////////////////////////
        protected function connection_init($_base_url){

            $this->base_url = $_base_url;

            $this->ch = curl_init();  
//            curl_setopt($this->ch, CURLOPT_PROXY, 'proxy:3128'); 
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Content-Type=\"text/html\"; charset=\"utf-8\"")); 
        }

        /**
        * Отправка запроса и получение результатов.
        * $params -> array()
        * return -> array()

                isAuthorized(sessionid)  return string UserObj or '';
                login(login, password) return string or '';
                updateUser(string User object) return true/false;
                createUser(string User object) return integer (ID FROM DB);

        */
        protected function fetchData(&$params){
            
            $ret = array();

            $url = $this->base_url.$params['method'].'?'.$params['params'];

//            echo "Request: ".$url.$this->BR;
            curl_setopt($this->ch, CURLOPT_URL,$url);

            $request_result = curl_exec($this->ch);
//            echo "RESULT: ".$request_result.$this->BR;

            return $request_result;
        }

        protected function packObjectJSONStyle($_arr){
            
            if(is_array($_arr)){
                foreach($_arr as $k=>$v){
                    $_arr[$k] = iconv("Windows-1251", "UTF-8", $v);
                }
            }

            $ret_string = json_encode($_arr);
            if(!$ret_string){$ret_string = false;}

            return $ret_string;
        }

        protected function unpackObjectJSONStyle($_stringJSON){

            $ret_obj = json_decode($_stringJSON, true);

            if(is_array($ret_obj)){
                foreach($ret_obj as $k=>$v){

                    if(is_bool($v)){
                        if($v)  {$v = "true";}
                        if(!$v) {$v = "false";}
                    }

                    $ret_obj[$k] = iconv("UTF-8", "Windows-1251", urldecode($v));
                }
            }

            return $ret_obj;
        }
        ////////////////////////////////////////////////////////////////////////


        // Wrappers method for interface method ////////////////////////////////
        // return string SESSIONID or false
        public function loginWrapper($_params){
            $ret = array();

            $ext_params = array(
                                'method'=>'login.xhtml',
                                'params'=>'login='.$_params['login'].'&password='.$_params['password'],
                               );

            $ret = $this->fetchData(&$ext_params);
            $ret = $this->unpackObjectJSONStyle($ret);
            if(count($ret)==0){$ret = false;}

            return $ret;
        }


        // return array with user fields OR false.
        public function isAuthorizedWrapper($_params){
            $ret = array();

            $ext_params = array(
                                'method'=>'isAuthorized.xhtml',
                                'params'=>'sessionid='.$_params['sessionid'],
                               );

            $ret = $this->fetchData(&$ext_params);
            $ret = $this->unpackObjectJSONStyle($ret);

            if(count($ret)==0){$ret = false;}
    
            return $ret;
        }


        public function canCreateWrapper($_login){
            $ret = array();

            $ext_params = array(
                                'params'=>'login='.$_login,
                                'method'=>'canCreate.xhtml',
                               );

            $ret = $this->fetchData(&$ext_params);
            $ret = $this->unpackObjectJSONStyle($ret);
            
            return $ret['result'];
        }

        public function createUserWrapper($_params){
            $ret = array();
                                                            //  ZALIPUHA
            if($this->canCreateWrapper($_params['_login']) != "false"){

                $__strjson = $this->packObjectJSONStyle($_params);

                $ext_params = array(
                                    'params'=>'userobj='.$__strjson,
                                    'method'=>'createUser.xhtml',
                                   );

                $ret = $this->fetchData(&$ext_params);
            } else {
                return false;
            }

            return $ret;
        }

        public function updateUserWrapper($_params){
            $ret = array();

            $__strjson = $this->packObjectJSONStyle($_params);
                                                 
            $ext_params = array(
                                'params'=>'userobj='.$__strjson,
                                'method'=>'updateUser.xhtml',
                               );

            $_params = array_merge($_params, $ext_params);
            $ret = $this->fetchData(&$_params);

            return $ret;
        }
        ////////////////////////////////////////////////////////////////////////


    } // end of class

?>