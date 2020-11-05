<?php
/**
 * ProfileLink helper
 *
 * Call as $this->profileLink() in your layout script
 */
class Vida_View_Helper_TopVideoItem extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function TopVideoItem($_fileinfo)
    {
    	$file_id = $_fileinfo['file_id'];
        $files_model = new Model_Files();
        $data = $files_model->format($file_id);
        
        $dec_str = strtolower(declension($data['reviewed'], ''.Vida_Helpers_Text::_T('view').' '.Vida_Helpers_Text::_T('views').' '.Vida_Helpers_Text::_T('viewss').''));
		$ret =
				'<div class="best_v_item video_'.$data['file_id'].'">
					<a href="javascript:void(0);" onclick="toPL(this,'.$data['file_id'].');" class="to_playlist playlist"></a>
					<a href="'.$data['video_url'].'" title="'.$data['title'].'" class="v_image"><img alt="'.Vida_Helpers_Text::_T('duration').'" src="'.$data['image_url'].'" /><span>'.$data['duration'].'</span></a>
					<a href="'.$data['video_url'].'" title="'.$data['title'].'" class="v_link">'.get_shortened($data['title'], 20).'</a>
					<span class="v_views">'.$dec_str.'</span>
					<div class="stars_counter"><div style="width:'.$data['rate_percent'].'%"></div></div>
				</div>';

    	return $ret;
    }
}