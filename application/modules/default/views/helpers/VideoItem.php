<?php
/**
 * ProfileLink helper
 *
 * Call as $this->profileLink() in your layout script
 */
class Vida_View_Helper_VideoItem extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
    
    /**
     * Форматирует описание видеофайла в результатах поисков
     */
    public function videoItem($file, $keys = null, $style = VI_DEFAULT_STYLE)
    {
        $file_id = $file['id'];
        $files_model = new Model_Files();
        $data = $files_model->format($file_id, $keys);
        $dec_str = strtolower(declension($data['reviewed'], ''.Vida_Helpers_Text::_T('view').' '.Vida_Helpers_Text::_T('views').' '.Vida_Helpers_Text::_T('viewss').''));
        
        if($data['state'] != Model_Files::CREATED) {
            $checkbox_html = ($style == VI_MYVIDEOS_STYLE) ? '<input class="vi_check" type="checkbox" onclick="toList(this, '.$data['file_id'].')"/>' : '';
            $buttons_html = ($style == VI_MYVIDEOS_STYLE) ? '<div class="message_misc_buttons"><a href="/pspace/video/id/'.$data['file_id'].'">'.Vida_Helpers_Text::_T('Редактировать описание').'</a></div>' : '';
            $duration_html = '<span>'.$data['duration'].'</span>';
        } else {
            $checkbox_html = '';
            $buttons_html = '';
            $duration_html = '';
        }
        
        $ret =
                '<div class="s_r_item video_'.$data['file_id'].'">
                    <h1>'. $checkbox_html . '<a href="'.$data['video_url'].'" class="s_r_link">'.$data['title'].'</a></h1>
                    <div class="s_r_v_image">
                    <a href="javascript:void(0);" onclick="toPL(this, '.$data['file_id'].');" class="to_playlist playlist"></a>
                    <a href="'.$data['video_url'].'" title="'.$data['alt'].'" class="v_image"><img alt="'.Vida_Helpers_Text::_T('duration').'" src="'.$data['image_url'].'" />'.$duration_html.'</a>
                    </div>
                   <div class="s_r_info">
                   '. Vida_Helpers_Text::_T('Просмотров') . ': <b>'.$dec_str.'</b><br />
                   '. Vida_Helpers_Text::_T('Владелец') . ': <a href="'.$data['owner_url'].'" class="s_r_username">'.$data['owner_login'].'</a><br />
                   '. Vida_Helpers_Text::_T('Категория') . ': <a href="'.$data['category_url'].'">'.$data['category'].'</a><br />
                   <a href="'.$data['video_url'].'#comments">'. Vida_Helpers_Text::_T('Комментариев') . ': ' . $data['comments_count'] . '</a><br />
                   </div>
                    <div class="stars_counter search_coutner"><div style="width:'.$data['rate_percent'].'%"></div></div>
                    '. $buttons_html. '
                </div>';
        unset($data);
        return $ret;
    }
}