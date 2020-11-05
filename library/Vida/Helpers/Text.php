<?
class Vida_Helpers_Text
{
    protected $_logger;
    
    /**
     * Конструктор класса
     * 
     * @return Vida_Helpers_TextHelper
     */
    public function Vida_Helpers_Text()
    {
        $this->_logger = Zend_Registry::get('logger');
    }

    /**
    * Очищает текст от html разметки
    */
    public function purge($text) {
       $text = strip_tags($text);
       $text = htmlspecialchars_decode($text);

       //убираем все спецсимволы вида &nbsp; (двойная кодировка)
       $text = preg_replace('/\&[a-z]{2,6}\;/si', " ", $text);

       return $text;
    }

    /**
    * Кодирует спецсиволы в сущности
    * @param string $text
    * @return string
    */
    public static function htmlencode($text) {
        return htmlspecialchars($text, ENT_COMPAT);
    }

    /**
     * Очищает текст от разметки и возвращает первые n символов текста
     * @param  string   $text Текст для обработки
     * @return string
     */
    public static function preview($text, $length = 200)
    {
        $text = strip_tags($text);
        
        //декодируем спецсимволы
        $text = htmlspecialchars_decode($text);

        //убираем все спецсиволы вида &nbsp; (двойная кодировка)
        $text = preg_replace('/\&[a-z]{2,6}\;/si', " ", $text);
        $length = min(strlen($text), $length);
        $cut = "";
        if($length < strlen($text)) {
           $cut = "...";  
        }
        return substr($text, 0, $length) . $cut;
    }
    
    /**
     * Очищает текст от лишней разметки и знаков препинания
     * 
     * @param  string   $text Текст для обработки
     * @return string
     */
    public function sanitize($content)
    {
        $content = strip_tags($content);
        $content = strtolower($content);
        
        //декодируем спецсимволы
        $content = htmlspecialchars_decode($content);

        //убираем все спецсиволы вида &nbsp; (двойная кодировка)
        $content = preg_replace('/\&[a-z]{2,6}\;/si', " ", $content);
       
        preg_match_all('/([a-zа-я0-9]+)/', $content, $bulk_words);
        
        $content = '';
        for ($i=0; $i < count($bulk_words[1]); $i++)
        {
            $content .= ' ' . $bulk_words[1][$i];
        }
        
        return $content;
    }

    // XML Entity Mandatory Escape Characters
    protected static function xmlentities($string) {
       return str_replace ( array ( '&', '"', "'", '<', '>', '`' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string );
    } 
    /**
     * Перевод строковых ресурсов
     * @param string $code
     * @return string
     */
    public static function _T($code, $args = null) {
        $translate = Zend_Registry::get('translate');
        
        $code = self::xmlentities($code);
        
        $text = $code;
        $code = iconv("windows-1251", "utf-8", $code);
        
        if(!empty($translate) && $translate->isTranslated($code)) {
            $text = iconv("utf-8", "windows-1251", $translate->_($code));
            //$text = self::xmlentities($text);
        }
        if(strpos($text, '%') > 0 && empty($args)) {
            $text = sprintf($text, $args);
        }
        return $text;
    }

    public static function _L() {
        $translate = Zend_Registry::get('translate');
        $locale = 'en';
        if(!empty($translate)) {
            $locale = $translate->getAdapter()->getLocale();
        }
        return $locale;
    }

    /**
     * Очищает текст от лишней разметки и знаков препинания и разбивает текст на слова
     * 
     * @param  string   $text Текст для обработки
     * @param  bool     $destinct Оставлять дубликаты слов
     * @return array
     */
    public function prepare($text)
    {
        //очищаем текст от разметки
        $text = $this->sanitize($text);
        
        //выделяем слова и формируем индекс
        $s = split(" ", $text);

        //initialize array
        $k = array();
       
        //iterate inside the array
        foreach( $s as $key => $val )
        {
            $val = trim($val);
            if(strlen($val) > 0) {
                $k[] = $val;
            }
        }
        return $k;
    }

    /*
    Method to detect user language
    */
    public function detectLanguage() {
        if ($_SERVER['HTTP_ACCEPT_LANGUAGE']) {
            $languages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $language = substr($languages,0,2);
            return $language;
        }
        else if ($_SERVER['HTTP_USER_AGENT']) {
            $user_agent = explode(";" , $_SERVER['HTTP_USER_AGENT']);

            for ($i=0; $i < sizeof($user_agent); $i++) {
                $languages = explode("-",$user_agent[$i]);
                if (sizeof($languages) == 2) {
                    if (strlen(trim($languages[0])) == 2) {
                        $size = sizeof($language);
                        $language[$size]=trim($languages[0]);
                    }
                }
            }
            return $language[0];
        }
        else {
            return '';
        }
    }

    
}