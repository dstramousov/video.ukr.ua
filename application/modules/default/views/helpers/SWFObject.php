<?php
/**
 * SWFObject helper
 *
 * Call as $this->swfObject() in your layout script
 */
class Vida_View_Helper_SWFObject extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * Возвращает js код для запрашиваемого файла. 
     * @param $_file_data: object of Model_Files
     * @param $_full_mode_sight: Признак того что выводится плеер для главной страницы true или false для страницы просмотра видео.
     * @return  */
    public function swfObject($_file, $_full_mode_sight=true)
    {
        if(!$_file) {
            $ret =  '
                <div class="big_v_header"><div class="header_img"></div><h1></h1><div class="stars_counter"><div style="width:0%"></div></div>
                <div class="b_v_duration"></div><div class="b_v_views"></div></div>
                <div class="player_container">';
                return $ret ;
        }

        if($_file['state'] == Model_Files::ADMIN_CLOSE || $_file['state'] == Model_Files::USER_CLOSE){
            throw new Zend_Exception("Файл к просмотру запрещен. Обратитесь в службу поддержки");
        }

        if($_file['state'] == Model_Files::CREATED){
            throw new Zend_Exception("Файл обрабатывается системой. Пожалуйста, повторите запрос через некоторое время.");
        }

        $file_id = $_file['id'];
        
        $files_model = new Model_Files();
        $data = $files_model->format($file_id);

        $dec_str    = declension($data['reviewed'], ''.Vida_Helpers_Text::_T('view').' '.Vida_Helpers_Text::_T('views').' '.Vida_Helpers_Text::_T('viewss').'');

        if($_full_mode_sight){

            $ret =  '<div class="big_v_header">
                        <div class="header_img"></div>
                        <h1><a href="'.$data['video_url'].'">'.$data['title'].'</a></h1>
                        <div class="stars_counter"><div style="width:'.$data['rate_percent'].'%"></div></div>
                        <div class="b_v_duration">'.Vida_Helpers_Text::_T('duration') .': '.$data['duration'].'</div>
                        <div class="b_v_views">'.$dec_str.'</div>
                    </div>';
        } else {

            $ret =  '<div class="big_v_header">
                        <div class="header_img"></div>
                        <h1>'.$data['title'].'</h1>      
                        <span class="s_v_owner">'.Vida_Helpers_Text::_T('Владелец').'&nbsp;<a href="'.Vida_Helpers_Config::get_baseurl().'index/search/user/'.$data['owner_login'].'">'.$data['owner_login'].'</a></span>
                    </div>';
        }

        //http://www.longtailvideo.com/players/jw-flv-player/
        
        $ret .= '<div class="player_container">
                    <div id="video_container" class="media"></div>
                        <script type="text/javascript">
                        <!--                                                    
                            var s1 = new SWFObject(\'/player.swf\',\'player\',\''.Zend_Registry::getInstance()->configuration->flv->width.'\',\''.Zend_Registry::getInstance()->configuration->flv->height.'\',\'9\',\'#000000\');
                            s1.addParam(\'allowfullscreen\',\'true\');
                            s1.addParam(\'allowscriptaccess\',\'always\');
                            s1.addParam(\'resizing\',\'false\');
                            s1.addParam(\'wmode\', \'transparent\');
                            s1.addParam(\'flashvars\',\'start=0&file='.$data['stream_url'].'&streamer=lighttpd&image='.$data['preview_image_url'].'&skin='.Vida_Helpers_Config::get_baseurl().'v_player_' . Vida_Helpers_Text::_L() . '.swf&plugins='.Vida_Helpers_Config::get_baseurl().'drelated-1&drelated.dxmlpath='.$data['related_xml'].'&drelated.dposition=bottom&drelated.dskin=/grayskin.swf&drelated.dtarget=_self\');
                            s1.write(\'video_container\');
                        // -->
                        </script>';

        return $ret;
    }
}