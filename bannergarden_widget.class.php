<?php


	
	class BannerGardenWidgetClass {
		var $title;
		
		function BannerGardenWidgetClass() {
			//Constructor	
		}
		
		/********************************/
		/******* WIDGET FUNCTIONS *******/
		/********************************/
		function BannerGardenRegisterWidget() {
			register_sidebar_widget('Banner Garden', array($this, 'BannerGardenWidget'));
			register_widget_control('Banner Garden', array($this, 'BannerGardenControl'));
		}
		
		function BannerGardenWidget ($args) {
			$options 	= get_option('bannergardenwidget');
			if ($options) {
				$title 		= $options['bg_widget_title'];
				$c_id 		= $options['bg_widget_campaign'];
				
				if (strlen($c_id) > 0 && is_numeric($c_id) && $c_id > 0) {
					echo $args['before_widget'];
    			if (!empty($title)) {
    				echo $args['before_title'] . $title . $args['after_title'];
    			}
    			echo '[bannergarden id="'.$c_id.'"]';
    			echo $args['after_widget'];
    		}
    	}
		}
		
		function BannerGardenControl() {
			$options 	= get_option('bannergardenwidget');
			$msg 			= '';
			
			if (isset($_POST["bg_op"])) {
				$msg = $this->ValidateWidgetForm();
			}
			
			if (!empty($msg)) {
				echo $this->ShowError($msg);
			}
?>
  	<div class="bg_label"><?php _e('Title','bannergarden')?></div>
  	<div class="bg_input"><input name="bg_widget_title" type="text" value="<?php echo $options['bg_widget_title']; ?>" /></div>
  	<div class="bg_label"><?php _e('ID of Campaign','bannergarden')?></div>
  	<div class="bg_input"><input name="bg_widget_campaign" type="text" value="<?php echo $options['bg_widget_campaign']; ?>" /></div>
  	<input type="hidden" name="bg_op" value="bg_update">
<?php
   		if (isset($_POST['bg_op']) && $_POST["bg_op"] == 'bg_update' && empty($msg)){
    		$options['bg_widget_title'] = attribute_escape($_POST['bg_widget_title']);
    		$options['bg_widget_campaign'] = attribute_escape($_POST['bg_widget_campaign']);
    		update_option('bannergardenwidget', $options);
  		}
		}
		
		function ValidateWidgetForm() {
			$msg ='';
			if (strlen($_POST["bg_widget_campaign"]) < 1) {
				$msg = __('Please fill the ID of campign','bannergarden');
			} else {
				if (!is_numeric($_POST["bg_widget_campaign"])) {
					$msg = __('ID of campaign should be number','bannergarden');
				} else {
					if ($_POST["bg_widget_campaign"] < 1) {
						$msg = __('ID of campaign should be more than 0','bannergarden');
					}
				}
			}
			return $msg;
		}
		
		function ShowError($msg) {
			$html = $msg;
			return $msg;
			
		}
		
	} //CLASS END
?>