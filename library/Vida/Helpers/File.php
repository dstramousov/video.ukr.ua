<?php

class Vida_Helpers_File
{
    /**
     * Returns a human readable filesize
     */
    public static function humanReadableFilesize($size) {
        $mod = 1024;
        $units = explode(' ','B KB MB GB TB PB');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
    
    public static function fix_slash($dirPath) {
       //return dirname($dirPath);
       //$dirPath = preg_replace('/\//', '/\\/', $dirPath);
       $dirPath = str_replace('/', '\\', $dirPath);
       return $dirPath;
    }

    public static function fix_path($dirPath) {
       //return dirname($dirPath);
       $dirPath = preg_replace('|//+$|', '/', $dirPath);
       $dirPath = preg_replace('|\\\/+|', '/', $dirPath);
       return $dirPath;
    }

    /**
    * Возвращает имя файла вместе с установленным revision
    *
    */
    public static function get_rev($filename)
    {
       $revision = Zend_Registry::getInstance()->configuration->site->revision;
       if(!empty($revision)) {
          $filename .= '?revision='.$revision;
       }
       return $filename;
    }

    /**
     * Производит загрузку афйла по HTTP
     * @param string $file_url url файла
     * @param string $local_path физический путь папки для файла
     * @param string $filename имя файла
     * @return bool результат выполнения операции
     */
    public static function get_file($file_url, $local_path, $filename)
    {
        $res = false;
        $out = fopen($local_path . $filename, 'wb');
        if ($out == FALSE){
            Vida_Helpers_Exception::processException(
                new Exception(sprintf("Ошибка открытия файла \"%s\"", $local_path . $filename))
            );
        } else {
   
            $ch = curl_init();
           
            curl_setopt($ch, CURLOPT_FILE, $out);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, $file_url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
               
            curl_exec($ch);

            if(curl_errno($ch) > 0) {
                Vida_Helpers_Exception::processException(new Exception(curl_error( $ch )), Zend_Log::DEBUG);
            } else {
                $res = true;
            }
   
            curl_close($ch);
            //fclose($out);
        }
        return $res;

    }

    /**
     * Проверяет сущестование папки. Создает ее если она не существует
     * @param $str string Путь к папке для проверки
     * @return bool
     */
    public static function check_dir($dir)
    {
        if(!file_exists($dir)) {
           $res = mkdir( $dir, 0777, true );
           if($res) {
                chmod($dir, 0775);
           }
        }
        return true;
    }

    /**
     * Возвращает список файлов директории
     * @param $str string Путь к папке для сканирования
     * @return none
     */
    public static function files_list($dir)
    {
        $files = array();
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) { 
                if ($file != "." && $file != "..") { 
                    $files[] = $file;   //echo "$file\n"; 
                } 
            }
            closedir($handle);
        }
        return $files;    
    }
}