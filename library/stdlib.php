<?php

function getNeededValuesFromArray(&$_arr, $played_id)
{
	$ret = array();

	$f_index = 0;
	$l_index = count($_arr)-1;
	$key = array_search($played_id, $_arr);  

	if(count($_arr) == 1){
		array_push($ret, $_arr[0]);
		array_push($ret, $_arr[0]);
		return $ret;
	}

	if(count($_arr) == 2 && $key == $l_index){
		array_push($ret, $_arr[0]);
		array_push($ret, $_arr[1]);
		return $ret;
	}

	if(count($_arr) == 2 && $key == $f_index){
		array_push($ret, $_arr[1]);
		array_push($ret, $_arr[0]);
		return $ret;
	}

	if($key != $f_index && $key!= $l_index){
		array_push($ret, $_arr[$key-1]);
		array_push($ret, $_arr[$key+1]);
		return $ret;
	}

	if($key == $f_index){
		array_push($ret, array_pop($_arr));
		array_push($ret, $_arr[$key+1]);
		return $ret;
	}

	if($key == $l_index){
		array_push($ret, $_arr[$key-1]);
		array_push($ret, array_shift($_arr));
		return $ret;
	}

}


function get_shortened($str, $len, $more=0)
{
    $short = '';
    //если в тексте есть разделитель, то использовать его, а не указанный параметр $len
	if ( $more > 0 && preg_match('/<!--more(.*?)?-->/', $str, $matches)) {
		$content = explode($matches[0], $str, 2);
		$short = strip_tags($content[0]);
		$short .= '...';
    } else {
		$short = strip_tags($str);
		$short = htmlspecialchars_decode($short, ENT_QUOTES);
		if (strlen($short) > $len) {

			$n = strpos($short, ' ', $len);

			if($n){ 
				$short = substr($short, 0, $n); 
				$short .= '...';
			} 

		} else {
			$short = $str;
		}

	}    
    // Strip leading and trailing whitespace
    $short = preg_replace('/^[\s]*(.*)[\s]*$/', '\\1', $short);

    return $short;
}


// Функция предназначена для вывода численных результатов с учетом
// склонения слов, например: "1 ответ", "2 ответа", "13 ответов" и т.д.
//
// $digit - целое число
// можно вместе с форматированием, например "<b>6</b>"
//
// $expr - массив, например: array("ответ", "ответа", "ответов").
// можно указывать только первые 2 элемента, например для склонения английских слов
// (в таком случае первый элемент - единственное число, второй - множественное)
//
// $expr может быть задан также в виде строки: "ответ ответа ответов", причем слова разделены
// символом "пробел"
//
// $onlyword - если true, то выводит только слово, без числа;
// необязательный параметр
//
// echo 'Мне уже '.declension('<b>20</b>','год года лет').'!';
function declension($digit,$expr,$onlyword=false)
{
	if(!is_array($expr)) $expr = array_filter(explode(' ', $expr));

	if(empty($expr[2])) $expr[2]=$expr[1];

	$i=preg_replace('/[^0-9]+/s','',$digit)%100;
	if($onlyword) $digit='';
	if($i>=5 && $i<=20) $res=$digit.' '.$expr[2];
	else
	{
		$i%=10;
		if($i==1) $res=$digit.' '.$expr[0];
		elseif($i>=2 && $i<=4) $res=$digit.' '.$expr[1];
		else $res=$digit.' '.$expr[2];
	}

	return trim($res);
}


// Standard library functions.

function header_no_cache() {
    // This function sent raw HTTP header for no cache HTML page.
    // For more detail realization - see PHP manual, HTTP function.
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

    $server_protocol = $_SERVER['SERVER_PROTOCOL'];
    if ($server_protocol == 'HTTP/1.1') {
        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    } else {
        header("Pragma: no-cache");                          // HTTP/1.0
    }

    header("Cache-Control: post-check=0, pre-check=0", false);
}


function param($name) {

	$res = "";

    if (isset($_GET[$name])) {
        $res = $_GET[$name];
    } else if (isset($_POST[$name])) {
	    $res = $_POST[$name];
    } else {
        return null;
    }
    
    if (get_magic_quotes_gpc()) {
        $res = stripslashes_deep($res);
    }

    return $res;
}

function param_cookie($name) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
}

function params($params2sel = array() ) {
    if ( empty($params2sel) ) {
        return array_merge($_POST, $_GET);
    } else {
        $result = array();
        foreach ( $params2sel as $name ) {
            $param = param($name);
            if ( isset($param) ) $result[$name] = $param;
        }
        return $result;
    }
}


function if_null($variable, $value) {
    return is_null($variable) ? $value : $variable;
}


function pipe_sendmail($msg, $queue = true) {
    // Send email message using local sendmail program.

    // $sendmail_path = '/usr/lib/sendmail';
    // $sendmail_keys = '-oi -t' . ($queue ? ' -odq' : '');

    $from = $msg['from'];
    $from_name = isset($msg['from_name']) ? $msg['from_name'] : '';
    $to = $msg['to'];
    $to_name = isset($msg['to_name']) ? $msg['to_name'] : '';
    $subj = isset($msg['subj']) ? $msg['subj'] : '';
    $text = $msg['text'];

    $headers =
        "From: $from_name <$from>\n" .
        "To: $to_name <$to>\n" .
        "Subject: $subj\n" .
        "\n";

//	mail($to, $subj, $text, $headers);

}

function is_empty($value) {
    return !preg_match('/\w+/', $value);
}


// dump_array() takes one array as a parameter
// It iterates through that array, creating a string
// to represent the array as a set

function dump_array($array)
{

	$_str = "<table bgcolor = '%s'>
					<tr>
						<td>
							%s
						</td>
					</tr>
				</table><br>";

	if(is_array($array)){

		$size = count($array);
		$string = "";

		if($size) {

			$string .= "{ <br>";

			foreach($array as $a => $b) {

				if(is_array($b)) { $b = dump_array($b); }
				if(is_object($b)) { $b = dump_array(object_to_array($b)); }
				$string .= "&nbsp;&nbsp;&nbsp;&nbsp;<b>$a = '$b'</b><br>";

      		}

			$string .= " }<br>";
		}

		$r = sprintf($_str, '#DACE0B', $string);

		return $r;

  } else { return $array; }
}

function dump($res) { 
	$_print = $res;
	if(is_array($res)) { $_print = dump_array($res); }
	if(is_object($res)) { $_print = dump_array(object_to_array($res)); }
	die(var_dump($_print)); 
}

function &object_to_array($obj) {

	$_arr_vars = is_object($obj) ? get_object_vars($obj) : $obj;
	$_arr_methods = is_object($obj) ? get_class_methods($obj) : $obj;
	
	foreach ($_arr_methods as $method_name) {
		$arr['FUNCTION&nbsp;&nbsp;&nbsp;->&nbsp;&nbsp;&nbsp;'.$method_name] = $method_name;
	}

	foreach ($_arr_vars as $key => $val) {
		$val = (is_array($val) || is_object($val)) ? get_class_methods($val) : $val;
		$arr[$key] = $val;
	}

	return $arr;
}

function html_quote_all($str) {
    return htmlspecialchars($str, ENT_QUOTES);
}

function unix_to_mysql_time($time) {
    return date('Y-m-d H:i:s', $time);
}

function timestamp2unix($stamp)
{
    if (strlen($stamp) < 14) {
        return 0;
    }

    $year = substr($stamp, 0, 4);
    $month = substr($stamp, 4, 2);
    $day = substr($stamp, 6, 2);
    $hour = substr($stamp, 8, 2);
    $min = substr($stamp, 10, 2);
    $sec = substr($stamp, 12, 2);

    $date = "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}";
    return strtotime($date);
}
