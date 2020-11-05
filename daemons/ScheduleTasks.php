<?php

    require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'init.php' );

    # Make sure this script doesn't run via the webserver
    # @@@ This is a hack to detect php-cgi, there must be a better way.
    if ( isset( $_SERVER['SERVER_PORT'] ) ) {
        echo "ScheduleTasks.php is not allowed to run through the webserver.\n";
        exit( 1 );
    }

    echo "Processing tasks (" . Vida_Helpers_DateHelper::today() .")...\n";
    $logger = Zend_Registry::get('logger');
    
    //���������� ������
    $duration = new Vida_Helpers_Duration();
    $duration->start();
    
    $logger->log(sprintf("Processing tasks [Start]. ������: %s", Vida_Helpers_DateHelper::today()), Zend_Log::DEBUG);

    try {
    
        $tasks_model = new Model_Tasks();
        $workerspool_model = new Model_WorkersPool();
        $files_model = new Model_Files();
        
        //��������� ���������� ������
        $tasks = $tasks_model->fetchAllByState(Model_Tasks::SCHEDULED);
        foreach($tasks as $task) {
            $params = Model_Tasks::paramsDecode($task);
            if(array_key_exists('tmp_file', $params) && array_key_exists('fname', $params)) {
                $fsize = @filesize($params['fname']);
                $image = $params['fname'] . '.' . Model_Files::IMAGE_EXT;
                
                //��������������� ���������. ������ ����� �� �������������
                if($fsize > 0 && $params['fsize'] == $fsize && @file_exists($image)) {
                    //������� ��������� ����
                    if(@file_exists($params['tmp_file'])) {
                        unlink($params['tmp_file']);
                    }
                    
                    //�������� ��������� �����
                    $file = $files_model->fetchById($task['file_id']);
                    $file_params = Model_Files::paramsDecode($file);
                    
                    $file['state'] = Model_Files::ACTIVE;
                    
                    //������� ����������������� ������ �� ���� �����������
                    $log = $params['fname'] . '.log';
                    $str = @file_get_contents($log);
                    $file_params['duration'] = "00:00";
                    if(is_string($str)) {
                        $regexp="/Duration\: (\d{2})\:(\d{2})\:(\d{2})\.(\d{1,2})/i";
                        preg_match($regexp, $str, $res);
                        if( count($res) > 0) {
                            $file_params['duration'] = $res[2] . ':' . $res[3];
                        }
                    }
                    $file['params'] = $file_params;
                    
                    //TODO: ������� �������� �����-����
                    
                    $files_model->update($file);
                    
                    $logger->log(sprintf("����������� ����� Id=%d ���������. ����� ������: %.3f sec", $task['file_id'], filemtime($params['fname']) - $params['scheduled']), Zend_Log::DEBUG);
                    
                    //������� ������
                    $tasks_model->deleteById($task['id']);
                } else {
                    //���� ���� ��� � �� �������� ����� 10 �����, �� ��������� "������������ ����" ��� ������ �����������
                    $finishing = Vida_Helpers_DateHelper::utime_add(
                        $params['scheduled'],
                        0, 10, 0, 0, 0
                    );
                    if($finishing < mktime() && $params['fsize'] == 0) {
                        $logger->log(sprintf("������ ����������� ����� Id=%d! (��� ����� � ����: %s)", $task['file_id'], $params['fname']), Zend_Log::ERR);
                        
                        $file = $files_model->fetchById($task['file_id']);
                        $file_params = Model_Files::paramsDecode($file);

                        //TODO: ����������� ���������� ��������� �� ������ �����������
                        $messages_model = new Model_Messages();
                        $message = array();
                        $message['user_id'] = $file['user_id'];
                        $message['body'] = sprintf("������ ����������� ����� %s. ��������� ������� - ������������ ����������� ��������� �������� ��� ����� ������� �����.", $file_params['fname']);
                        $message['priority'] = Model_Messages::ST_PRIORITY_HIGHT;
                        $messages_model->save($message);
                        
                        //������� ��������� ����
                        if(@file_exists($params['tmp_file'])) {
                            unlink($params['tmp_file']);
                        }
                        //������� ����������� ���� �� ����� �������
                        $files_model->deleteById($task['file_id']);
                        
                    } else {
                        $params['fsize'] = $fsize;
                        $task['params'] = $params;
                        $tasks_model->update($task);
                    }
                }
            } else {
                $logger->log(sprintf("������ ��� ���������� (Id=%d). ������� �� �������!", $task['id']), Zend_Log::ERR);
                $files_model->deleteById($task['file_id']);
            }
        }
        unset($tasks);
        
        //��������� ����� �������� �����������
        $tasks = $tasks_model->fetchAllByState(Model_Tasks::CREATED);
        $counter = 0;
        foreach($tasks as $task) {
            $params = Model_Tasks::paramsDecode($task);
            $node = $workerspool_model->getWorker();
            if(is_array($node)) {
                if(array_key_exists('tmp_file', $params) && array_key_exists('fname', $params)) {
                    $log = $params['fname'] . '.log';
                    
                    //$cmd = "~/./conv.sh ". $params['tmp_file'] . " " . $params['fname'] ." >>~/./conv.log 2>&1 &";
                    //$cmd = "~/./conv.sh ". $params['tmp_file'] . " " . $params['fname'] ." >>".$log." 2>&1 &";
                    $cmd = "~/./conv.sh ". $params['tmp_file'] . " " . $params['fname'] ." 0</dev/null >>".$log." 2>&1 &";
                    
                    //FIXME: ������ ��� ������������ ��� Windows
                    if($_SERVER['WINDIR'] || $_SERVER['windir']) {
                        $tmp_l = Vida_Helpers_File::fix_path(Vida_Helpers_Config::prepare(Zend_Registry::getInstance()->configuration->file->tmp_l));
                        $storage_l = Vida_Helpers_File::fix_path(Vida_Helpers_Config::prepare(Zend_Registry::getInstance()->configuration->file->storage_l));
                        $tmp = Vida_Helpers_File::fix_path(Vida_Helpers_Config::prepare(Zend_Registry::getInstance()->configuration->file->tmp));
                        $storage = Vida_Helpers_File::fix_path(Vida_Helpers_Config::prepare(Zend_Registry::getInstance()->configuration->file->storage));
                        $cmd = str_replace($tmp, $tmp_l, $cmd);
                        $cmd = str_replace($storage, $storage_l, $cmd);
                    }
                    //END FIXME
                    
                    $res = $workerspool_model->_exec($node['ip'], $cmd);
                    $logger->log(sprintf("������ �����������. IP=%s, ������ �������:\n%s", $node['ip'], $cmd), Zend_Log::DEBUG);
                    
                    if($res !== '') {
                        $err = $workerspool_model->getErrors();
                        $logger->log(sprintf("������ �������. %s", implode(', ', $err)), Zend_Log::ERR);
                    } else {
                        //���������� ���������� ������
                        $task['state'] = Model_Tasks::SCHEDULED;
                        $params['scheduled'] = mktime();
                        $task['params'] = $params;
                        $tasks_model->update($task);
                    }
                    
                } else {
                    $logger->log(sprintf("������ ��� ���������� (Id=%d). ������� �� �������!", $task['id']), Zend_Log::ERR);
                    $files_model->deleteById($task['file_id']);
                }
            } else {
                $logger->log("��� ���������� ������� �����������. ���������� ��������", Zend_Log::INFO);
                break;
            }
            $counter++;
        }
    } catch(Exception $exp) {
        Vida_Helpers_Exception::processException($exp);
        $hasError = true;
    }

    //�������� ���
    Vida_Helpers_DB::clearCache();

    $duration->end();

    $logger->log(sprintf("Processing tasks [Done]. ��������� %d ���������. ����� ������ %s", $counter, $duration->toString()), Zend_Log::DEBUG);

    echo "Done (" . Vida_Helpers_DateHelper::today() .").\n";

    exit( 0 );

?>