<?php

class Vida_Helpers_DateHelper
{
    public static function toDate($unixtimestamp, $format="Y-m-d H:i:s")
    {
        //return self::toDate_ex($unixtimestamp);
        return date($format, $unixtimestamp);
    }

    public static function toInt($date)
    {
        return strtotime($date);
    }

    public static function today()
    {
        return date("Y-m-d H:i:s");
    }

    public static function short_today()
    {
        return date("Y-m-d");
    }

    public static function int_today()
    {
        return strtotime(date("Y-m-d"));
    }

    function mysql_to_unix_date_customize($str) {
        $date_regexp = '/^(\d+)-(\d+)-(\d+)(\s+(\d+))?(:(\d+))?(:(\d+))?$/';
        $date_values = array();
        $result = 0;
        if (preg_match($date_regexp, $str, $date_values)) {
            $year   = $date_values[1];
            $month  = $date_values[2];
            $day    = $date_values[3];

            $hr    = $date_values[5];
            $mn    = $date_values[7];
            $se    = $date_values[9];
        
            $result = mktime(0,0,0, $month, $day, $year);
        }

        $m = self::getrightdate($month);

        return (preg_replace("/^0/i", "", $day).' '.$m.' '. $year . '.&nbsp;'.$hr.':'.$mn);
    }

    function getrightdate($num) {

        $myMonthesArray = array(
            "",
            Vida_Helpers_Text::_T('jan'),
            Vida_Helpers_Text::_T('feb'),
            Vida_Helpers_Text::_T('mar'),
            Vida_Helpers_Text::_T('apr'),
            Vida_Helpers_Text::_T('may'),
            Vida_Helpers_Text::_T('jun'),
            Vida_Helpers_Text::_T('jul'),
            Vida_Helpers_Text::_T('aug'),
            Vida_Helpers_Text::_T('sep'),
            Vida_Helpers_Text::_T('okt'),
            Vida_Helpers_Text::_T('nov'),
            Vida_Helpers_Text::_T('dec'),
        );

         $month = (int)$num;
         return $myMonthesArray[$month];
    }

    protected static function _getmonth($m) {
        $monthes = array(
            "€нвар€", "феврал€", "марта", "апрел€", "ма€", "июн€", 
            "июл€", "августа", "сент€бр€", "окт€бр€", "но€бр€", "декабр€"
        );
        $m = (int)$m;
        if($m >= 0 && $m < count($monthes)) {
            return $monthes[$m];
        }
        return "";
    }

    public static function toDate_ex($unixtime) {
        $dt = localtime($unixtime, true);
        //dump($dt);
        $str =  $dt['tm_hour'] . ':' . sprintf("%02d", $dt['tm_min']) . ' ' . $dt['tm_mday'] . ' ' . self::_getmonth($dt['tm_mon']) . ' ' . ($dt['tm_year']+1900) . 'г.';
        return $str;
    }

    public static function utime_add($unixtime, $hr=0, $min=0, $sec=0, $mon=0, $day=0, $yr=0) {
        $dt = localtime($unixtime, true);
        $unixnewtime = mktime(
            $dt['tm_hour']+$hr, $dt['tm_min']+$min, $dt['tm_sec']+$sec,
            $dt['tm_mon']+1+$mon, $dt['tm_mday']+$day, $dt['tm_year']+1900+$yr);
        return $unixnewtime;
    } 


    public static function nicetime($date)
    {
        if(empty($date)) {
            throw new Zend_Exception("ѕуста€ дата");
        }
    
        $periods         = array(
                                    "second", 
                                    "minute", 
                                    "hour", 
                                    "day", 
                                    "week", 
                                    "month", 
                                    "year", 
                                    "decade"
                                );


        $lengths         = array("60","60","24","7","4.35","12","10");
    
        $now             = time();
        $unix_date       = strtotime($date);
    
        if(empty($unix_date)) {    
            throw new Zend_Exception("ќшибка генерации даты");
        }

        $difference     = $now - $unix_date;
        $tense         = Vida_Helpers_Text::_T("ago");
    
        for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }
    
        $difference = round($difference);

        $dec_str = strtolower(declension($difference, ''.Vida_Helpers_Text::_T($periods[$j]).' '.Vida_Helpers_Text::_T($periods[$j].'s').' '.Vida_Helpers_Text::_T($periods[$j].'ss').''));

        $str = strtolower("$dec_str {$tense}");
    
        return $str;
    }

}