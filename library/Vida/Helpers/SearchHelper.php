<?
class Vida_Helpers_SearchHelper
{
    protected $_wordLengthMin = 1;
    protected $_wordLengthMinLog = 2;   //Логировать слова длина которых после штеммера стала меньше чем ...
    protected $_maxAnnotationLength = 150;
    protected $_logger;
    protected $_stop_words;
    protected $_stemmer;
    const INDEX_MODE = 1;
    const SEARCH_MODE = 0;
    
    /**
     * Конструктор класса
     * 
     * @return Vida_Helpers_SearchHelper
     */
    public function Vida_Helpers_SearchHelper()
    {
        $this->_logger = Zend_Registry::get('logger');
    }
    
    /**
     * Возвращает объект штеммера
     * 
     * @return RussianStemmer
     */
    protected function getStemmer()
    {
        if (null === $this->_stemmer) {
            $this->_stemmer = new Vida_IR_Stemmer();
        }
        return $this->_stemmer;
    }

    /**
     * Возвращает список стоп-слов (слова которые не нужно включать в индекс)
     * 
     * @return array
     */
    protected function getStopWords()
    {
        //FIXME: реализовать хранение во внешнем файле для удобства редактирования
        if($this->_stop_words == null)
        {
            $this->_stop_words = array('и', 'в', 'во', 'не', 'что', 'он', 'на', 'я', 'с', 'со', 'как', 'а', 'то', 'все', 'она', 'так', 'его', 'но', 'да',
              'ты', 'к', 'у', 'же', 'вы', 'за', 'бы', 'по', 'только', 'ее', 'мне', 'было', 'вот', 'от', 'меня', 'еще', 'нет', 'о', 'из', 'ему', 'теперь',
              'когда', 'даже', 'ну', 'вдруг', 'ли', 'если', 'уже', 'или', 'ни', 'быть', 'был', 'него', 'до', 'вас', 'нибудь', 'опять', 'уж', 'вам',
              'сказал', 'ведь', 'там', 'потом', 'себя', 'ничего', 'ей', 'может', 'они', 'тут', 'где', 'есть', 'надо', 'ней', 'для', 'мы', 'тебя', 'их',
              'чем', 'была', 'сам', 'чтоб', 'без', 'будто', 'человек', 'чего', 'раз', 'тоже', 'себе', 'под', 'жизнь', 'будет', 'ж', 'тогда', 'кто',
              'этот', 'говорил', 'того', 'потому', 'этого', 'какой', 'совсем', 'ним', 'здесь', 'этом', 'один', 'почти', 'мой', 'тем', 'чтобы', 'нее',
              'сейчас', 'были', 'куда', 'зачем', 'сказать', 'всех', 'никогда', 'сегодня', 'можно', 'при', 'наконец', 'два', 'об',
              'другой', 'хоть', 'после', 'над', 'больше', 'тот', 'через', 'эти', 'нас', 'про', 'всего', 'них', 'какая', 'много', 'разве', 'сказала',
              'три', 'эту', 'моя', 'впрочем', 'хорошо', 'свою', 'этой', 'перед', 'иногда', 'лучше', 'чуть', 'том', 'нельзя', 'такой', 'им', 'более',
              'всегда', 'конечно', 'всю', 'между',
              'хотя', 'таких',
              'считаю', 
              'сразу', 'является', 'напоминает',
              'существует', 'пришли', 'сказали', 'произошли',
              'поэтому', 'который',
              'стать', 'могут', 'нужно', 'вести', 'думаете', 
              'начать', 'какие', 'вообще', 'будут', 'принято', 'похоже',
              'почему', 'иначе', 'обязан', 'считаем', 'одной', 'никаких',
              'против', 'придется', 'прошлый', 'которые', 
              'очень', 'нашей', 'думаю', 'скорее', 'второй', 'значит', 'говори',
              'кажется', 'этому', 'тому',
              'мм', 'м2', 'млн', 'км', 'млрд',
              'этих', 'это', 'этим',
              'oб', 'мое', 'моим', 'моему',
              'те', 'сей', 'кем', 'кого', 'всем', 'ой', 'го', 'хо'
            );
        }
        return $this->_stop_words;
    }

    /**
     * Делает подсветку ключевых слов в строке
     * 
     * @param  string   $text Текст для обработки
     * @param  array   $words Массив слов
     * @return string
     */
    public function highlightWords($text, $words)
    {
        /*** loop of the array of words ***/
        foreach ($words as $word)  {
            /*** quote the text for regex ***/
            $word = preg_quote($word);
            
            /*** highlight the words ***/
            //$text = preg_replace("/\b($word)\b/i", '<span class="highlight_word">\1</span>', $text);
            $regexp = "/\b(".$word."[a-zA-Zа-яА-Я]*)\b/i";
            //$regexp = "/\b($word)\b/i";
            //$text = preg_replace($regexp, '<strong style="background-color: #999;">\1</strong>', $text);
            $text = preg_replace($regexp, '<strong>\1</strong>', $text);
        }
        return $text;
    }

    /**
     * Находит окружение слова в тексте
     * 
     * @param  string   $text Текст для обработки
     * @param  int      $pos Позиция начала слова в тексте
     * @param  string   $word Слово для которого находим окрестность
     * @param  int      $radius Количество слов от указанного
     * @return array
     */
    protected function getWordNeighborhood($text, $pos, $word, $radius)
    {
        //символы пунктуации
        $punctuations = array(' ', ',', ')', '(', '.', "'", '"', '<', '>', ';', '!', '?', '/', '-', '_', '[', ']', ':', '+', '=', '#', '%', '$', chr(10), chr(13));
        $length = strlen($text);

        //поиск левой границы
        $_p1 = $pos - 1;
        if($_p1 > 0)
        {
            $c = 0;
            $p_pos = $_p1 - 1;
            while($_p1 > 0)
            {
                if(in_array($text[$_p1], $punctuations))
                {
                    if($_p1 != $p_pos - 1)
                    {
                        $c++;
                        if($c > $radius)
                        {
                            $_p1++;
                            break;
                        }
                    }
                    $p_pos = $_p1;
                }
                $_p1--;
            }
        }
        if($_p1 < 0)
        {
            $_p1 = 0;
        }
        
        //поиск правой границы области
        $_p2 = $pos + strlen($word);
        if($_p2 < $length)
        {
            //dump(substr($text, $_p2, 50));
            $c = 0;
            $p_pos = $_p2 + 1;
            while( $_p2 < $length )
            {
                if( in_array($text[$_p2], $punctuations) )
                {
                    //if($c == 0) dump(substr($text, $_p2, 50));
                    if($_p2 != $p_pos + 1)
                    {
                        $c++;
                        if($c > $radius)
                        {
                            $_p2--;
                            break;
                        }
                    }
                    $p_pos = $_p2;
                }
                $_p2++;
            }
        }
        if($_p2 == $length)
        {
            $_p2 = $length - 1;
        }
        
        //$tmp = substr($text, $_p1, $_p2 - $_p1);
        //dump($tmp);
        
        return array($_p1, $_p2);
    }
    
    /**
     * Создает аннотацию из текста по заданному набору ключевых слов
     * 
     * @param  string   $text Текст для обработки
     * @param  array    $keys Набор ключевых слов
     * @return string
     */
    public function textAnnotation($text, $keys, $quantity)
    {
        $text = strip_tags($text);
        
        //заменяем спецсимволы на текстовые эквиваленты
        $enc_punctuations = array(
                           '&quot;'     => '"',
                           '&nbsp;'     => ' ',
                           '&ndash;'    => '-',
                           '&mdash;'    => '-',
                           '&raquo;'    => '"',
                           '&laquo;'    => '"'
                           );
        $text = str_replace(array_keys($enc_punctuations), array_values($enc_punctuations), $text);
        
        $text = htmlspecialchars_decode($text, ENT_QUOTES);
        // replace multiple gaps
        $text = preg_replace('/ {2,}/si', " ", $text);
        
        //выделяем ключевые слова из текста
        $keys_offsets = array();
        foreach($keys as $key)
        {
            $key = preg_quote($key);
            //$regexp = "/\b($key)\b/i";
            $regexp = "/\b(".$key."[a-zA-Zа-яА-Я]*)\b/i";
            preg_match_all($regexp, $text, $matches, PREG_OFFSET_CAPTURE);
            
            $keys_offsets[$key] = $matches[1];
        }
        
        //находим окружение каждого ключевого слова +- заданное кол-во слов
        $annotations = array();
        $counter = null;
        foreach($keys_offsets as $key => $val)
        {
            foreach($val as $pos)
            {
                $annotations[] = $this->getWordNeighborhood($text, $pos[1], $pos[0], 7, $counter);
            }
        }
        
        //найти пересечения множеств
        //dump($this->compactArray($annotations, 0));
        if(count($annotations))
        {
            array_multisort($annotations);
            $annotations = $this->compactArray($annotations, 0);
            array_multisort($annotations);
        }
        else
        {
            $annotations[] = array(0, min($this->_maxAnnotationLength, strlen($text)));
        }
        
        if(!isset($quantity) || !is_numeric($quantity))
        {
            $quantity = count($annotations);
        }
        
        //формируем текст аннотации
        $res = "";
        $counter = 0;
        foreach($annotations as $annotation)
        {
            if($counter >= $quantity)
            {
                break;
            }
            $res .= ($annotation[0] > 0 ? "..." : "") . substr($text, $annotation[0], $annotation[1] - $annotation[0] + 1) . ($annotation[1] != strlen($text) - 1 ? "..." : "") . "<br />";
            $counter ++;
        }
        unset($annotations);
        unset($keys_offsets);
        
        return $res;
    
    }

    /**
     * Рекурсивная функция вычисления пересечения множеств
     * 
     * @param  string   $text Текст для обработки
     * @return string
     */
    protected function compactArray($array)
    {
        for($i = 0; $i < count($array); $i++)
        {
            for($j = $i + 1; $j < count($array); $j++)
            {
                if($array[$i][0] <= $array[$j][0] && $array[$j][0] <= $array[$i][1])
                {
                    $array[] = array($array[$i][0], $array[$j][1]);
                    unset($array[$j]);
                    unset($array[$i]);
                    $copy = array();
                    foreach($array as $c) {
                        $copy[] = $c;
                    }
                    unset($array);
                    array_multisort($copy);
                    return $this->compactArray($copy);
                }
            }
        }
        return $array;
    }

    
    /**
     * Очищает текст от лишней разметки и знаков препинания
     * 
     * @param  string   $text Текст для обработки
     * @return string
     */
    protected function sanitize_text($content, $mode)
    {
        $phrases = array();
        
        $content = strip_tags($content);
        $content = strtolower($content);
        $content = htmlspecialchars_decode($content);
        //убираем все спецсиволы вида &nbsp;
        $content = preg_replace('/\&[a-z]{2,6}\;/si', " ", $content);
        
        //висячие laquo; гraquo; без амперсанда впереди
        $content = preg_replace('/(laquo|raquo)+\;/si', " ", $content);
       
        $tmp = array();
        if($mode == self::SEARCH_MODE) {
            //поиск фраз, заключенных в кавычки, запятые
            //$regexp = '/\"(\w+[^\"]*)\"/i';
            $regexp = '/([a-zа-я]+[^a-zа-я\,\"]*)+/i';
            preg_match_all($regexp, $content, $matches);
            if(null!== $matches && count($matches[0]) > 0) {
                foreach($matches[0] as $phrase) {
                    $tmp[] = $phrase;
                }
                //убираем найденные фразы из текста поиска
                $content = preg_replace($regexp, " ", $content);
            }
            unset($matches);
            
        }
        $tmp[]=$content;
        
        foreach($tmp as $t) {
            $content = '';
            preg_match_all('/([a-zа-я]+)/', $t, $bulk_words);
            for ($i=0; $i<count($bulk_words[1]); $i++)
            {
                $content .= ($i == 0? '':' ') . $bulk_words[1][$i];
            }
            if(strlen($content) > 0) {
                $phrases[] = $content;
            }
        }
        unset($tmp);
        
        /*
         //такой вид выборки слов не эффективен

        $punctuations = array(',', ')', '(', '.', "'", '"',
        '<', '>', ';', '!', '?', '/', '-',
        '_', '[', ']', ':', '+', '=', '#',
        '%',
        '$', 
        '&quot', '&raquo', '&laquo', '&nbsp', '&copy', '&gt', '&lt', '&ndash', '№', '&ldquo', '&mdash', '&rsquo',
        chr(10), chr(13), chr(9), chr(171), chr(187), chr(150), chr(151), chr(133), chr(8470));

        $content = str_replace($punctuations, " ", $content);
        
        $content = str_replace("&", " ", $content);

        // replace multiple gaps
        $content = preg_replace('/ {2,}/si', " ", $content);
        */
        //dump($phrases);

        return $phrases;
    }

    /**
     * Строит матрицу поиска для заданного массива ключевых слов
     * a, b, c     b, c     c
     * a, b     => b     =>
     * a
    */
    public function prepareSearchMatrix($search_arr)
    {
        $search_matrix = array();
        $count = count($search_arr);
        for($j=0; $j < $count; $j++)
        {
            $tmp = $count - $j;
            for($i=$tmp; $i >= 1; $i--)
            {
                $search_matrix[] = array_slice($search_arr, $j, $i);
            }
        }
        return $search_matrix;
    }

    /**
     * Строит матрицу поиска для заданного массива ключевых слов
     * {a, b, c} =>  {{a}, {b}, {c}}
    */
    public function prepareSearchMatrix_1($search_arr)
    {
        $search_matrix = array();
        $count = count($search_arr);
        foreach($search_arr as $key)
        {
            $search_matrix[] = array($key);
        }
        return $search_matrix;
    }

    /**
     * Очищает текст от лишней разметки и знаков препинания, чисел
     * 
     * @param  string   $text Текст для обработки
     * @param  bool     $destinct Оставлять дубликаты слов
     * @return array
     */
    public function prepareSearchText($text, $destinct = true, $mode = self::SEARCH_MODE)
    {
        //загружаем список часто употребляемых слов
        $stop_words = $this->getStopWords();
        
        //очищаем текст от разметки
        $text_arr = $this->sanitize_text($text, $mode);
        $retval = array();
        
        foreach($text_arr as $text ) {
            //выделяем слова и формируем индекс
            $s = split(" ", $text);
    
            $stemmer = $this->getStemmer();
    
            //initialize array
            $k = array();
           
            //iterate inside the array
            foreach( $s as $key => $val )
            {
                //delete single or two letter words and
                //Add it to the list if the word is not
                //contained in the common words list.
                $val = trim($val);
                if($val != "" && strlen($val) > $this->_wordLengthMin && !in_array($val, $stop_words) && !is_numeric($val))
                {
                    $pseudo_root = $stemmer->stemWord($val);
                    if(is_string($pseudo_root) && $pseudo_root != "")
                    {
                        if(strlen($pseudo_root) <= $this->_wordLengthMinLog)
                        {
                            $this->_logger->log(sprintf("После штеммера \"%s\" получилось слишком короткое слово \"%s\"", $val, $pseudo_root), Zend_Log::DEBUG);
                        }
                        $val = $pseudo_root;
                    }
                    else
                    {
                        //FIXME: Логирование слов, которые не смог обработать штеммер
                        $this->_logger->log(sprintf("Штеммеру не удалось обработать слово \"%s\"", $val), Zend_Log::DEBUG);
                    }
                    
                    if($destinct)
                    {
                        if(!in_array($val, $k))
                        {
                            $k[] = $val;
                        }
                    }
                    else
                    {
                        $k[] = $val;
                    }
                }
            }
            if(count($k) > 0) {
                $retval[] = $k;
            }
        }
        return $retval;
    }
    
}