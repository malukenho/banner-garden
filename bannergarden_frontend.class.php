<?php
	class BannerGardenFrontend {
		var $plugin_dir;
		var $plugin_url;
		var $default_banner;
		var $name;
		var $c_table;
		var $b_table;
		var $sdb; //Show Defaul Banner
		
		function BannerGardenFrontend() {
			global $wpdb;
			$options = get_option('bannergarden');
			$this->plugin_dir 					= WP_PLUGIN_DIR;	
			$this->plugin_url 					= WP_PLUGIN_URL;
			$this->name 								= 'bannergarden';
			$this->default_banner_path	= WP_PLUGIN_DIR."/bannergarden/media/default_banner.jpg";
			$this->default_banner_url		= WP_PLUGIN_URL."/bannergarden/media/default_banner.jpg";
			$this->c_table 							= $wpdb->prefix.$this->name."_".'campaigns';
			$this->b_table 							= $wpdb->prefix.$this->name."_".'banners';
			$this->sdb 									= $options["show_default_banner"];
		}
		
		function BGProcessor($matches) {
			$text_to_replace = $matches[0];
			$c_id = $matches[1];
			if ($this->CheckCampaignExists($c_id)) {
				//Yes, campaign exists
				if ($this->CheckCampaignIsActive($c_id)) {
					//Yes, it's active
					if ($this->CheckCampaignDate($c_id)) {
						//Yes, date's are ok
						$media = $this->GetTheCampaignBanner($c_id);
					}
				} else {
					//No, show default banner
					$media = $this->GetDefaultBannerCode();	
				}
			} else {
				//Campaign not exists, show default banner
				$media = $this->GetDefaultBannerCode();	
			}
			return $media;
		}
		
		function BannerGardenFilter($buffer) {
  	  //Start and replace all the [bannergarden id="xxx"] shortcode.
  	  $pattern = '/\[bannergarden[ ]+id="(\d+)"\]/i';
  	  $buffer = preg_replace_callback($pattern,array($this,"BGPRocessor"),$buffer);
  	  return $buffer;
		}
  	
		function BGBufferStart() {
  	  //Process the html output
  	  ob_start(array($this,"BannerGardenFilter"));
		}
  	
		function BGBufferEnd() {
  	  ob_end_flush();
		}
		
		function RegisterJavaScripts(){
			//Registering required javascripts
			wp_enqueue_script('jquery');
			wp_deregister_script('swfobject');
			wp_register_script('swfobject', $this->plugin_url.'/bannergarden/js/swfobject/swfobject.js', false, '2.2',false);
			wp_enqueue_script('swfobject');
		}
		
		function BannerGardenControl () {
			echo 'I am a control panel';
		}
		
		function GetTheCampaignBanner($c_id) {
			//Get the banner row
			global $wpdb;
			
			$sql = "SELECT *,rand() as rnd FROM ".$this->b_table." where b_campaign = ".$c_id." ORDER BY RND LIMIT 1";
			$banner = $wpdb->get_row($sql);
			
			//These 2 lines is for developing, you can delete it.
			//$sql = "SELECT *,rand() as rnd FROM ".$this->b_table." where b_id = 9 ORDER BY RND LIMIT 1";
			//$banner = $wpdb->get_row($sql);
			
			if (empty($banner)) {
				return $this->GetDefaultBannerCode();
			} else {
				return $this->GetBannerCode($banner);
			}
		}
		
		function GetBannerCode($banner) {
			//Get the banner code what we show on the frontpage
			$code = '';
			switch ($banner->b_type) {
				case "code":
					$code = stripslashes($banner->b_media);
					break;
					
				case "pic":
					$target = '';
					$code = '<img src="'.stripslashes($banner->b_media).'" alt="" />';
					if (!empty($banner->b_link)) {
						if ($banner->b_new_window == 1) {
							$target = ' target="_blank"';	
						}
						$code = '<a href="'.$banner->b_link.'"'.$target.'">'.$code.'</a>';
					}
					break;
				
				case "flash":
					$random_id = 'BannerGarden_'.$banner->b_id."_".md5(date("U").rand(1,1000000));
					$code = '<div id="'.$random_id.'"></div>'."\n";
					$code .= '<script type="text/javascript">'."\n";
					$code .= 'var params = {
  									version: \'5\',
  									type: \'movie\',
  									bg_color: \'#FFFFFF\'
										};
										
										var flashvars = false;
										var attributes = {};'."\n";
					$code .='swfobject.embedSWF("'.$banner->b_media.'", "'.$random_id.'", "'.$banner->b_width.'", "'.$banner->b_height.'", "9.0.24", "", flashvars, params, attributes);';
					$code .= '</script>'."\n";
					break;
					
				default:
					//If somehow (what surely not happens) the type of the banner is wrong
					$code = $this->GetDefaultBannerCode();
					break;
			}
			$back_code = '<!-- Banner Garden Code Start -->'."\n";
			$back_code .= $code."\n";
			$back_code .= '<!-- Banner Garden Code End -->'."\n";
			return $back_code;
		}
		
		//This function is check if the campaing exists
		function CheckCampaignExists($c_id) {
			global $wpdb;
			$count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$this->c_table." WHERE c_id = ".$c_id));
			if ($count > 0) {
				return true;	
			} else {
				return false;	
			}
		}
		
		//This function is check if the campaing is active
		function CheckCampaignIsActive($c_id) {
			global $wpdb;
			$count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$this->c_table." WHERE c_id = ".$c_id." and c_active = 1"));
			if ($count > 0) {
				return true;	
			} else {
				return false;	
			}
		}
		
		function CheckCampaignDate($c_id) {
			global $wpdb;
			$sql = "SELECT COUNT(*) FROM ".$this->c_table." WHERE c_id = ".$c_id." AND
							((c_from <=NOW() AND c_from != '0000-00-00 00:00:00' AND c_until = '0000-00-00 00:00:00') OR 
							(c_from = '0000-00-00 00:00:00' AND c_until >= NOW() AND c_until != '0000-00-00 00:00:00') OR
							(c_from = '0000-00-00 00:00:00' AND c_until >= '0000-00-00 00:00:00') OR
							(c_from <=NOW() AND c_until >= NOW()))";
			$count = $wpdb->get_var($wpdb->prepare($sql));
			if ($count > 0) {
				return true;	
			} else {
				return false;	
			}
		}
		
		//Give you the default banner if the file exists and this option is enabled.
		function GetDefaultBannerCode() {
			$code = '';
			if (file_exists($this->default_banner_path) && $this->sdb == 'yes') {
				$code = '<img src="'.$this->default_banner_url.'" alt="" title="" />';	
			}
			return $code;	
		}
	} //CLASS END
?>