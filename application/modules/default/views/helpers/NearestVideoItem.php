<?php
/**
 * ProfileLink helper
 *
 * Call as $this->profileLink() in your layout script
 */
class Vida_View_Helper_NearestVideoItem extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function NearestVideoItem($_fileinfo)
    {
    	$nicetime = Vida_Helpers_DateHelper::nicetime(Vida_Helpers_DateHelper::toDate($_fileinfo['created']));

    	$file_id = $_fileinfo['id'];
        
        $files_model = new Model_Files();
        $data = $files_model->format($file_id);
        
        $dec_str = strtolower(declension($data['reviewed'], ''.Vida_Helpers_Text::_T('view').' '.Vida_Helpers_Text::_T('views').' '.Vida_Helpers_Text::_T('viewss').''));
        
		$ret = '<div class="best_v_item video_'.$data['file_id'].'">
					<a href="javascript:void(0);" onclick="toPL(this, '.$data['file_id'].');" class="to_playlist playlist"></a>
					<a href="'.$data['video_url'].'" title="'.$data['title'].'" class="v_image"><img alt="'.$data['title'].'" src="'.$data['image_url'].'" /><span>'.$data['duration'].'</span></a>
					<a href="'.$data['video_url'].'" title="'.$data['title'].'" class="recent_link">'.get_shortened($data['title'], 25).'</a>
					<span class="added_time">'.$nicetime.'</span>
					<span class="recent_views">'.$dec_str.'</span>
					<a href="'.$data['owner_url'].'" class="recent_owner">'.$data['owner_login'].'</a>
				</div>';

    	return $ret;
    }
}