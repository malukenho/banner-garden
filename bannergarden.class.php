<?php
/*******************************/
/****** BannerGardenClass ******/
/*******************************/

class BannerGarden {
	var $name;
	var $ver;	
	var $title;	
	var $base_dir;
	var $media_dir;
	var $c_table;
	var $b_table;
	var $plugin_url;
	var $url;
	var $msg;
	var $bg_action;
	var $default_banner;

	function BannerGarden() {
		global $wpdb;
		$this->name 					= 'bannergarden';
		$this->ver 						= '0.1.3';
		$this->title 					= 'Banner Garden';
		$this->base_dir 			= dirname (__FILE__);
		$this->media_dir 			= $this->base_dir.BGDS.'media';
		
		$this->c_table 				= $wpdb->prefix.$this->name."_".'campaigns';
		$this->b_table 				= $wpdb->prefix.$this->name."_".'banners';
		$this->plugin_url 		= get_bloginfo('siteurl').'/wp-content/plugins/banner-garden';
		$this->media_url 			= get_bloginfo('siteurl').'/wp-content/plugins/banner-garden/media';
		$this->default_banner	= $this->media_url.'/default_banner.jpg';
		$this->url 						= admin_url('options-general.php?page=banner_garden_page');
		$this->msg						= '';
		$this->bg_action 			= $_GET["bg_action"];
	}
	
	/********************************/
	/******* PLUGIN FUNCTIONS *******/
	/********************************/
	
	function BannerGardenInit() {
		//Process actions
		load_plugin_textdomain( 'bannergarden', false, 'banner-garden/localization');
		
		switch ($this->bg_action) {
			case "create_campaign":
				if ($_POST["op"] == 'save' || $_POST["op"] == 'modify') {
					$this->msg = $this->ValidateCampaignForm();
					if (empty($this->msg)) {
						//Created the campaign successfully, let's save it.	
						if ($_POST["op"] == 'save') {
							$this->SaveCampaign();
						} elseif ($_POST["op"] == 'modify') {
							$this->UpdateCampaign();
						}
						
						//Go back to main page
						wp_redirect($this->url);
					}
				}
				break;
				
			case "create_banner":
				if ($_POST["op"] == 'save' || $_POST["op"] == 'modify') {
					$this->msg = $this->ValidateBannerForm();
					if (empty($this->msg)) {
						//Created the campaign successfully, let's save it.	
						if ($_POST["op"] == 'save') {
							$this->SaveBanner();
						} elseif ($_POST["op"] == 'modify') {
							$this->UpdateBanner();
						}
						//Go back to banner list page
						wp_redirect($this->url.'&bg_action=list_banners&c_id='.(int)$_POST["c_id"]);
					}
				}
				break;
				
			case 'delete_banner':
				$b_id = (int)$_GET["b_id"];
				$c_id = (int)$_GET["c_id"];
				$this->DeleteBanner($b_id);
				//Go back to banner list page
				wp_redirect($this->url.'&bg_action=list_banners&c_id='.$c_id);
				break;
			
			case 'delete_campaign':
					$c_id = (int)$_GET["c_id"];
					$this->DeleteCampaign($c_id);
					//Go back to main page
					wp_redirect($this->url);
				break;
				
			case "update_options":
				$options = get_option('bannergarden');
				$sdb = 'no'; //Show Default Banner
				if (isset($_POST["default_banner"]) && $_POST["default_banner"] == 1) {
					$sdb = 'yes';
				}
				$options["show_default_banner"] = $sdb;
				update_option('bannergarden',$options);
				wp_redirect($this->url);
				break;
		}	
		
	}
	
	function BannerGardenAdminMain() {
  	add_options_page($this->title." version: ".$this->ver, 'Banner Garden', '10', 'banner_garden_page', array($this, 'BannerGardenOptionPage'));
	}
	
	function BannerGardenOptionPage() {
		//Echo wrapper div and title;
		echo $this->HtmlElements('divwrapper');
		echo $this->HtmlElements('title');
		
		switch ($this->bg_action) {
			case "create_campaign":
				$this->ShowCampaignForm($this->msg,'save');
				break;
				
			case "modify_campaign":
				global $bgfe;
				$c_id = (int)$_GET["c_id"];

				if ($bgfe->CheckCampaignExists($c_id)) {
					$this->ShowCampaignForm($this->msg,'modify',$c_id);
				} else {
					$message = sprintf(__('This campaign is not exists. Maybe you deleted.<br /><a href="%1$s"><< Go Back</a>','bannergarden'),$this->url);
					$this->ShowError($message);	
				}
				break;
				
			case "list_banners":
				global $bgfe;
				$c_id = (int)$_GET["c_id"];

				if ($bgfe->CheckCampaignExists($c_id)) {
					$this->ListBanners($c_id);
				} else {
					$message = sprintf(__('This campaign is not exists. Maybe you deleted.<br /><a href="%1$s"><< Go Back</a>','bannergarden'),$this->url);
					$this->ShowError($message);	
				}
				break;
				
			case "create_banner":
				$this->ShowBannerForm($this->msg,'save');
				break;
			case "modify_banner":
				$b_id = $_GET["b_id"];
				$this->ShowBannerForm($this->msg,'modify',$b_id);
				break;
				
			default:
				$this->ShowBgMainOptionsForm();
				$this->ListCampaigns();
				echo $this->HtmlElements('bannergarden_logo');
				break;
		}
		echo $this->HtmlElements('divclose');
	}
	
	function RegisterJavaScripts() {
		
		wp_register_style('bg-css', $this->plugin_url.'/css/bannergarden.css');
		wp_enqueue_style( 'bg-css');
		
		wp_register_style('bg-jquery-ui-css', $this->plugin_url.'/js/jquery-ui/css/le-frog/jquery-ui-1.8.custom.css');
		wp_enqueue_style( 'bg-jquery-ui-css');
		
		wp_deregister_script('jquery');
		wp_register_script('jquery', $this->plugin_url.'/js/jquery-1.4.2.min.js',false,'1.4.2', false);
		wp_enqueue_script('jquery');

		wp_deregister_script('jquery-ui-core');
		wp_register_script('bg-jquery-ui', $this->plugin_url.'/js/jquery-ui/js/jquery-ui-1.8.custom.min.js',false,'1.8',false);
		wp_enqueue_script('bg-jquery-ui');
		
		wp_register_script('bg-datetimepicker_addon', $this->plugin_url.'/js/jquery-ui-timepicker-addon.min.js',false,'0.1',false);	
		wp_enqueue_script('bg-datetimepicker_addon');
		
		wp_register_script('bg-bannergarden_js', $this->plugin_url.'/js/bannergarden.js',false,'0.1',false);	
		wp_enqueue_script('bg-bannergarden_js');
		
		//For media library
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
	}
	
	function BannerGardenInstall() {
		$options = array();
		$options = get_option('bannergarden');
		if (empty($options['serverid'])) {
			$options['serverid'] = md5(get_bloginfo('siteurl'));
			$options['ver'] = '0.1';
			$options['show_default_banner'] = 'yes';
			
			add_option('bannergarden', $options, '', 'yes');
			
			if (class_exists('BannerGardenWidgetClass')) {
				$options = array();
				$options['c_id'] = 0;
				$options['title'] = 'Banner Garden';
				add_option('bannergardenwidget', $options, '', 'yes');
			}
			
			$this->InstallSqlTables();
		}
	}
	
	function BannerGardenUnInstall() {
		delete_option('bannergarden');
		delete_option('bannergardenwidget');
	}
	
	function InstallSqlTables() {
		global $wpdb;
   	if($wpdb->get_var("show tables like '".$this->c_table."'") != $this->c_table) {
      $sql = "CREATE TABLE `".$this->c_table."` (
							  `c_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  							`c_name` varchar(255) NOT NULL,
  							`c_active` tinyint(4) NOT NULL DEFAULT '0',
  							`c_from` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  							`c_until` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  							PRIMARY KEY (`c_id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
   	}
   	
   	if($wpdb->get_var("show tables like '".$this->b_table."'") != $this->b_table) {
      $sql = "CREATE TABLE `".$this->b_table."` (
							  `b_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
							  `b_campaign` int(10) unsigned NOT NULL,
							  `b_name` varchar(255) NOT NULL,
							  `b_type` varchar(6) NOT NULL,
							  `b_width` int(11) unsigned DEFAULT NULL,
							  `b_height` int(11) unsigned DEFAULT NULL,
							  `b_new_window` tinyint(4) unsigned DEFAULT NULL,
							  `b_media` text,
							  `b_link` text,
							  PRIMARY KEY (`b_id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
   	}
	}
	
	
	/****************************/
	/******* MAIN OPTIONS *******/
	/****************************/
	
	function ShowBgMainOptionsForm() {
		$options = get_option('bannergarden');
		$checked = '';
		if (array_key_exists('show_default_banner',$options)) {
			if ($options["show_default_banner"] == 'yes') {
				$checked = ' checked="checked"';	
			}
		}
?>		
		<form method="post" action="<?php echo $this->url; ?>&bg_action=update_options">
			<div class="bg_label"><input name="default_banner" type="checkbox" value="1"<?php echo $checked; ?>><?php _e('Show default banner, if something fail.','bannergarden'); ?></div>
			<div><input type="submit" value="<?php _e('Save','bannergarden'); ?>" /></div>
			<div><small><?php _e('If your campaign is expired, the media not found, etc...','bannergarden'); ?></small></div>
			<div><small><?php _e('The default banner is here, so you can change it if you want:','bannergarden'); ?> <?php echo $this->default_banner;?></small></div>
			<div><img src="<?php echo $this->default_banner;?>" alt="<?php _e('banner Garden default banner','bannergarden'); ?>" title="<?php _e('banner Garden default banner','bannergarden'); ?>"> <-- <?php _e('Default banner.','bannergarden');?></div>
			<input type="hidden" name="op" value="update_options">
		</form>
		<hr class="bg_hr" />

<?		
	}
	
	
	
	/**********************************/
	/******* CAMPAIGN FUNCTIONS *******/
	/**********************************/
	function DeleteCampaign($c_id) {
		global $wpdb;
		$sql = "DELETE FROM ".$this->b_table." where b_campaign = ".$c_id;
		$wpdb->query($sql);
		$sql = "DELETE FROM ".$this->c_table." where c_id= ".$c_id;
		$wpdb->query($sql);
	}
	
	
	function SaveCampaign()  {
		global $wpdb;
		$name 	= $wpdb->escape($_POST["name"]);
		$from 	= $wpdb->escape($_POST["from"]);
		$until 	= $wpdb->escape($_POST["until"]);
		if ($_POST["active"] == 1) {
			$active = 1;
		} else {
			$active = 0;
		}
		
		if (empty($from)) {
			$from = '0000-00-00 00:00:00';
		} else {
			$from = date("Y-m-d H:i",strtotime($from));
		}
		if (empty($until)) {
			$until = '0000-00-00 00:00:00';
		} else {
			$until = date("Y-m-d H:i",strtotime($until));	
		}
		
		$sql = "INSERT INTO ".$this->c_table." 
						(c_name,c_active,c_from,c_until)
						values
						('".$name."', ".$active.", '".$from."','".$until."')";
		$wpdb->query($sql);
	}
	
	function UpdateCampaign()  {
		global $wpdb;
		$name 	= $wpdb->escape($_POST["name"]);
		$from 	= $wpdb->escape($_POST["from"]);
		$until 	= $wpdb->escape($_POST["until"]);
		if ($_POST["active"] == 1) {
			$active = 1;
		} else {
			$active = 0;
		}
		if (empty($from)) {
			$from = '0000-00-00 00:00:00';
		} else {
			$from = date("Y-m-d H:i",strtotime($from));
		}
		if (empty($until)) {
			$until = '0000-00-00 00:00:00';
		} else {
			$until = date("Y-m-d H:i",strtotime($until));	
		}
		$sql = "UPDATE ".$this->c_table." 
						set c_name = '".$name."',
						c_active = ".$active.",
						c_from = '".$from."',
						c_until = '".$until."'
						where c_id = ".(int)$_POST["c_id"];
		$wpdb->query($sql);
	}
	
	function ValidateCampaignForm() {
		global $wpdb;
		
		$msg = '';
		
		$name 	= $wpdb->escape($_POST["name"]);
		$from 	= $wpdb->escape($_POST["from"]);
		$until 	= $wpdb->escape($_POST["until"]);
		
		if (empty($name)) {
			$msg .= __('Name is required','bannergarden').'<br />';	
		}
		if (!empty($from) && !strtotime($from)) {
			$msg .= __('Bad start time format','bannergarden').'<br />';
		}
		if (!empty($until) && !strtotime($until)) {
			$msg .= __('Bad start time format','bannergarden').'<br />';
		}
		
		if (!empty($from) && strtotime($from) && !empty($until) && strtotime($until) && strtotime($from) > strtotime($until)) {
			$msg .= __('The start time can not be bigger then end time','bannergarden').'<br />';
		}
		if (!empty($msg)) {
			$msg = rtrim ($msg, '<br />');	
		}
		return $msg;
	}
	
	
	function GetCampaignName($c_id) {
		global $wpdb;
		$name = $wpdb->get_var($wpdb->prepare("SELECT c_name FROM ".$this->c_table." WHERE c_id = ".$c_id)); 
		return $name;
	}
	
	function ListCampaigns() {
		global $wpdb;
		$camps = $wpdb->get_results("SELECT * FROM ".$this->c_table." ORDER BY c_id");
		if (count($camps)) {
			
			echo '<p><a href="'.$this->url.'&bg_action=create_campaign">'.__('Create a new campaign','bannergarden').'</a></p>';
			//List campaigns
			echo $this->HtmlElements('tableliststart');
			echo $this->HtmlElements('tablelistcampaignheader');
			$i = 0;
			foreach ($camps as $camp) {
				if ($i%2==0) {
					$rowclass = "BGRow1";
				} else {
					$rowclass = "BGRow2"; 
				}
				$del_text = sprintf(__("Are you sure you want to delete this campaign?\\nAll the attached banners will be deleted.\\n\\nName of campaign: %s\\nID: %s",'bannergarden'),$camp->c_name,$camp->c_id);
				$count_of_banners = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$this->b_table." WHERE b_campaign = ".$camp->c_id));
?>
			<tr class="<?php echo $rowclass; ?>" onmouseover="this.className='BGRowActive';" onmouseout="this.className='<?php echo $rowclass; ?>'">
				<td><strong><?php echo $camp->c_id; ?></strong></td>
				<td><?php echo $camp->c_name; ?></td>
				<td><?php echo $this->FormatTime($camp->c_from); ?></td>
				<td><?php echo $this->FormatTime($camp->c_until); ?></td>
				<td><?php echo $this->GetEnumValue($camp->c_active,'active'); ?></td>
				<td><?php echo $count_of_banners; ?></td>
				<td><a href="<?php echo $this->url.'&bg_action=list_banners&c_id='.$camp->c_id; ?>"><?php _e('List banners','bannergarden');?></a><br /><a href="<?php echo $this->url.'&bg_action=create_banner&c_id='.$camp->c_id; ?>"><?php _e('Add new banner','bannergarden');?></a></td>
				<td><a href="<?php echo $this->url.'&bg_action=modify_campaign&c_id='.$camp->c_id; ?>"><?php _e('Modify','bannergarden');?></a></td>
				<td><a onclick="return confirm('<?php echo $del_text ?>');" href="<?php echo $this->url.'&bg_action=delete_campaign&c_id='.$camp->c_id; ?>"><?php _e('Delete','bannergarden');?></a></td>
			</tr>
<?php				
				$i++;
			}
			echo $this->HtmlElements('tableclose');
		} else {
			//No campaign
			_e('<p>You have no campaign. ','bannergarden');
			echo '<a href="'.$this->url.'&bg_action=create_campaign">';
			_e('create one</a>.</p>','bannergarden');
		}
	}
	
	function ShowCampaignForm($msg,$mode,$c_id = 0) {
		global $wpdb;
		
		//Show the campaign form
		if ($mode == 'save') {
			$btn_txt = __("Save",'bannergarden');	
		} else {
			if (!isset($_POST["op"])) {
				//Get the values of the campaign
				$campaign = $wpdb->get_row("SELECT * FROM ".$this->c_table." WHERE c_id = ".$c_id);	
				$_POST["name"] = stripslashes($campaign->c_name);
				
				if ($campaign->c_from != '0000-00-00 00:00:00') {
					$tmp = strtotime(str_replace("-","/",$campaign->c_from));
					$_POST["from"] = date("m/d/Y H:i",$tmp);
				}
				if ($campaign->c_until != '0000-00-00 00:00:00') {
					$tmp = strtotime(str_replace("-","/",$campaign->c_until));
					$_POST["until"] = date("m/d/Y H:i",$tmp);
				}
				if ($campaign->c_active == 1) {
					$_POST["active"] = 1;	
				}
			}
			$btn_txt = __("Modify",'bannergarden');	
		}
		
		if (!empty($msg)) {
			$this->ShowError($msg);
		}

?>			
		<p><a href="<?php echo $this->url?>"><< <?php _e('Back to list of campaigns','bannergarden'); ?></a></p>
		<form method="post" action="<?php echo $this->url?>&bg_action=create_campaign">
			<div class="bg_label"><?php _e('Name of campaign','bannergarden'); ?></div>
			<div class="bg_input"><input style="width: 140px;" type="text" name="name" value="<?php echo $_POST["name"] ?>"></div>
			<div class="bg_label"><?php _e('Start time','bannergarden'); ?></div>
			<div class="bg_input"><input style="width: 140px;" type="text" id="from" name="from" value="<?php echo $_POST["from"] ?>"><a href="javascript: void(0);" onclick="ClearInput('from');"><?php _e('clear','bannergarden'); ?></a></div>
			<div class="bg_label"><?php _e('End time','bannergarden'); ?></div>
			<div class="bg_input"><input style="width: 140px;" type="text" id="until" name="until" value="<?php echo $_POST["until"] ?>"><a href="javascript: void(0);" onclick="ClearInput('until');"><?php _e('clear','bannergarden'); ?></a></div>
			<div class="bg_label"><?php _e('Is Active','bannergarden'); ?></div>
			<div class="bg_input"><input type="checkbox" name="active" value="1" <?php if ($_POST["active"] == 1) { echo '"checked=checked"'; } ?>"></div>
			<div><input type="submit" value="<?php echo $btn_txt?>"></div>
			<input type="hidden" name="op" value="<?php echo $mode?>">
			<input type="hidden" name="c_id" value="<?php echo $c_id?>">
		</form>
		
<?php 
			//Localization for timepicker
			//TODO: Should test it with other languages, and give back the datetime based on that.
			/*
			$lang = strtolower(substr(WPLANG,0,2));
			$js_lang_path = $this->base_dir.BGDS.'js'.BGDS.'jquery-ui'.BGDS.'development-bundle'.BGDS.'ui'.BGDS.'i18n'.BGDS.'jquery.ui.datepicker-'.$lang.'.js';
			$js_lang_url = $this->plugin_url.'/js/jquery-ui/development-bundle/ui/i18n/jquery.ui.datepicker-'.$lang.'.js';
			if (file_exists($js_lang_path)) {
				echo '<script type="text/javascript" src="'.$js_lang_url.'"></script>'."\n";
			}
			*/
?>		
		<script type="text/javascript">
			jQuery(function() {
				jQuery('#from').datetimepicker();
				jQuery('#until').datetimepicker();
			});
		</script>
<?		
	}
	
	/********************************/
	/******* BANNER FUNCTIONS *******/
	/********************************/	
	
	function DeleteBanner($b_id) {
		global $wpdb;
		$sql = "DELETE FROM	".$this->b_table." where b_id = ".$b_id;
		$wpdb->query($sql);
	}
	
	function ListBanners($c_id) {
		global $wpdb;
		
		
		$sql = "SELECT * FROM ".$this->b_table." where b_campaign = ".$c_id." order by b_id";	
		$banners = $wpdb->get_results($sql);
		if (count($banners)) {
			echo '<p><a href="'.$this->url.'">'.__(' << Back to campaigns','bannergarden').'</a></p>';
			echo '<h3>'.__('Name of campaign','bannergarden').': '.$this->GetCampaignName($c_id).'</h3>';
			echo '<p><a href="'.$this->url.'&bg_action=create_banner&c_id='.$c_id.'">'.__('Create a new banner','bannergarden').'</a></p>';
			//List campaigns
			echo $this->HtmlElements('tableliststart');
			echo $this->HtmlElements('tablelistbannerheader');
			$i = 0;
			foreach ($banners as $banner) {
				if ($i%2==0) {
					$rowclass = "BGRow1";
				} else {
					$rowclass = "BGRow2"; 
				}
				$del_text = sprintf(__("Are you sure you want to delete this banner?\\n\\nName of banner: %s",'bannergarden'),$banner->b_name);
				
				$width 			= 'N/A';
				$height 		= 'N/A';
				$media			= '';
				$link 			= 'N/A';
				$new_window	= 'N/A';
				
				switch ($banner->b_type) {
					case "code":
						break;
					
					case "flash":
						$width 	= $banner->b_width;
						$height = $banner->b_height;
						break;
						
					case "pic":
						$new_window = $this->GetEnumValue($banner->b_new_window,'new_window');
						if (!empty($banner->b_link)) {
							$link = $banner->b_link;
						}
						break;	
				}
				
				
?>
			<tr class="<?php echo $rowclass; ?>" onmouseover="this.className='BGRowActive';" onmouseout="this.className='<?php echo $rowclass; ?>'">
				<td><?php echo $banner->b_name; ?></td>
				<td><?php echo $this->GetEnumValue($banner->b_type,'banner_type'); ?></td>
				<td><?php echo $width; ?></td>
				<td><?php echo $height; ?></td>
				<td><?php echo $link; ?></td>
				<td><?php echo $new_window; ?></td>
				<td><a href="<?php echo $this->url.'&bg_action=modify_banner&c_id='.$c_id.'&b_id='.$banner->b_id; ?>"><?php _e('Modify','bannergarden');?></a></td>
				<td><a onclick="return confirm('<?php echo $del_text ?>');" href="<?php echo $this->url.'&bg_action=delete_banner&c_id='.(int)$_GET["c_id"].'&b_id='.$banner->b_id; ?>"><?php _e('Delete','bannergarden');?></a></td>
			</tr>
<?php				
				$i++;
			}
			echo $this->HtmlElements('tableclose');
		} else {
			echo '<p><a href="'.$this->url.'">'.__(' << Back to campaigns','bannergarden').'</a></p>';
			echo '<h3>'.__('Name of campaign','bannergarden').': '.$this->GetCampaignName($c_id).'</h3>';
			printf(__('<p>You have no banner in this campaign. <a href="%s&bg_action=create_banner&c_id=%s">Create one</a>.</p>','bannergarden'),$this->url,$c_id);
		}
	}
	
	function UpdateBanner() {
		global $wpdb;
		$c_id = (int)$_POST["c_id"];

		$name = $wpdb->escape($_POST["name"]);
		$width = 0;
		$height = 0;
		$media = '';
		$new_window = 0;
		$link = '';
		
		switch ($_POST["type"]) {
			case "code":
				$media = $wpdb->escape($_POST["adcode"]);
				break;
				
			case "flash":
				$media = $wpdb->escape($_POST["adflash"]);
				$width = $wpdb->escape($_POST["adwidth"]);
				$height = $wpdb->escape($_POST["adheight"]);
				break;
				
			case "pic":
				$media = $wpdb->escape($_POST["adpicture"]);
				if (isset($_POST["adnewwindow"]) && $_POST["adnewwindow"]) {
					$new_window = 1;	
				}
				$link = $wpdb->escape($_POST["adlink"]);
				break;
				
		}
		$sql = "UPDATE ".$this->b_table."
								set b_name = '".$name."',
								b_width = ".$width.",
								b_height = ".$height.",
								b_new_window = ".$new_window.",
								b_media = '".$media."',
								b_link = '".$link."'
								where b_id = ".(int)$_POST["b_id"];
		$wpdb->query($sql);
	}
	
	function SaveBanner() {
		global $wpdb;
		$c_id = (int)$_POST["c_id"];

		$name = $wpdb->escape($_POST["name"]);
		$width = 0;
		$height = 0;
		$media = '';
		$new_window = 0;
		$link = '';
		
		
		
		switch ($_POST["type"]) {
			case "code":
				$media = $wpdb->escape($_POST["adcode"]);
				$sql = "INSERT INTO ".$this->b_table."
								(b_name,b_campaign,b_type,b_width,b_height,b_new_window,b_media,b_link)
								VALUES ('".$name."',".$c_id.",'code',0,0,0,'".$media."','')";
				break;
				
			case "flash":
				$media = $wpdb->escape($_POST["adflash"]);
				$width = $wpdb->escape($_POST["adwidth"]);
				$height = $wpdb->escape($_POST["adheight"]);
				$sql = "INSERT INTO ".$this->b_table."
								(b_name,b_campaign,b_type,b_width,b_height,b_new_window,b_media,b_link)
								VALUES ('".$name."',".$c_id.",'flash',".$width.",".$height.",0,'".$media."','')";
				break;
				
			case "pic":
				$media = $wpdb->escape($_POST["adpicture"]);
				if (isset($_POST["adnewwindow"]) && $_POST["adnewwindow"]) {
					$new_window = 1;	
				}
				$link = $wpdb->escape($_POST["adlink"]);
				
				$sql = "INSERT INTO ".$this->b_table."
								(b_name,b_campaign,b_type,b_width,b_height,b_new_window,b_media,b_link)
								VALUES ('".$name."',".$c_id.",'pic',0,0,".$new_window.",'".$media."','".$link."')";
				break;
				
		}
		$wpdb->query($sql);
	}

	function ShowBannerForm($msg,$mode,$b_id = 0) {
		global $wpdb;
		
		//Show the banner form
		if ($mode == 'save') {
			$btn_txt = __("Save",'bannergarden');	
		} else {
			if (!isset($_POST["op"])) {
				$banner = $wpdb->get_row("SELECT * FROM ".$this->b_table." WHERE b_id = ".$b_id);	
				$_POST["name"] = stripslashes($banner->b_name);
				$_POST["type"] = $banner->b_type;
				
				switch ($_POST["type"]) {
					case "code":
						$_POST["adcode"] = stripslashes($banner->b_media);
						break;
					
					case "pic":
						$_POST["adpicture"] = stripslashes($banner->b_media);
						$_POST["adlink"] = stripslashes($banner->b_link);
						$_POST["adnewwindow"] = $banner->b_new_window;
						break;
						
					case "flash":
						$_POST["adflash"] = stripslashes($banner->b_media);
						$_POST["adwidth"] = stripslashes($banner->b_width);
						$_POST["adheight"] = stripslashes($banner->b_height);
						break;	
				}
			}
			$btn_txt = __("Modify",'bannergarden');	
		}
		if (!empty($msg)) {
			$this->ShowError($msg);
		}
		$c_id = (int)$_GET["c_id"];
		$type = $_POST["type"];
		
		require_once($this->base_dir.'/js/bannergarden_banners.js.php');
		
?>		
		<h3><?php _e('Name of campaign','bannergarden'); echo ': '.$this->GetCampaignName($c_id);?></h3>
		<p><a href="<?php echo $this->url?>&bg_action=list_banners&c_id=<?php echo $c_id?>"><< <?php _e('Back to banners of ','bannergarden'); echo ' '.$this->GetCampaignName($c_id);?></a></p>
		<p><a href="<?php echo $this->url?>"><< <?php _e('Back to list of campaigns','bannergarden'); ?></a></p>
		<form method="post" id="bannerform" action="<?php echo $this->url?>&bg_action=create_banner&c_id=<?php echo $c_id; ?>">
			<div class="bg_label"><?php _e('Name of banner','bannergarden'); ?></div>
			<div class="bg_input"><input type="text" id="adname" name="name" value="<?php echo $_POST["name"] ?>"></div>
			<div class="bg_label"><?php _e('Type','bannergarden'); ?></div>
			<div class="bg_input">
				<input <?php if ($_POST["type"] == "pic") { echo 'checked="checked"'; } ?> class="m_type" type="radio" name="type" id="pic" value="pic" /> <?php _e('Picture (gif,png,jpg)','bannergarden'); ?><br />
				<input <?php if ($_POST["type"] == "flash") { echo 'checked="checked"'; } ?> class="m_type" type="radio" name="type" id="flash" value="flash" /> <?php _e('Flash (swf, flv, fla)','bannergarden'); ?><br />
				<input <?php if ($_POST["type"] == "code") { echo 'checked="checked"'; } ?> class="m_type" type="radio" name="type" id="code" value="code" /> <?php _e('Ad Code, js, HTML, etc...','bannergarden'); ?>
			</div>
			<div id="media_ajax"><?php _e('Please select your type of media.','bannergarden')?></div>
			<div><input type="submit" value="<?php echo $btn_txt?>"></div>
			<input type="hidden" name="op" value="<?php echo $mode?>">
			<input type="hidden" name="b_id" value="<?php echo $b_id?>">
			<input type="hidden" name="c_id" value="<?php echo $c_id?>">
		</form>
<?php
	}
	
	function ValidateBannerForm() {
		global $wpdb;
		$msg = '';
		$type = $_POST["type"];
		$name 	= $wpdb->escape($_POST["name"]);
		
		if (empty($name)) {
			$msg .= __('Name is required.','bannergarden').'<br />';	
		}
		
		if (empty($type)) {
			$msg .= __('Please select a media type.','bannergarden').'<br />';	
		} else {
			
			switch ($type) {
				case 'code':
					if (empty($_POST["adcode"])) {
						$msg .= __('Fill the Ad Code field.','bannergarden').'<br />';	
					}
					break;
					
				case 'flash':
					$allowed_ext = array('swf','flw','fla');
					if (empty($_POST["adflash"])) {
						$msg .= __('URL of the flash movie is required.','bannergarden').'<br />';	
					} else {
						//Check the extension
						if (!$this->CheckValidExtension($_POST["adflash"],$allowed_ext)) {
							$msg .= __('Invalid file extension. You can youse only swf, flv, fla','bannergarden').'<br />';
						}
					}
					if (strlen($_POST["adwidth"]) < 1) {
						$msg .= __('Width of the movie is required.','bannergarden').'<br />';	
					} else {
						if (!is_numeric($_POST["adwidth"])) {
							$msg .= __('The width of the movie could be only number.','bannergarden').'<br />';
						} elseif ($_POST["adwidth"] < 1) {
							$msg .= __('The width of the movie should be more than 0px.','bannergarden').'<br />';
						}
					}
					if (strlen($_POST["adheight"]) < 1) {
						$msg .= __('Height of the movie is required.','bannergarden').'<br />';	
					} else {
						if (!is_numeric($_POST["adheight"])) {
							$msg .= __('The heigh of the movie could be only number.','bannergarden').'<br />';
						} elseif ($_POST["adheight"] < 1) {
							$msg .= __('The height of the movie should be more than 0px.','bannergarden').'<br />';
						}
					}
					break;
					
				case 'pic':
					$allowed_ext = array('jpeg','jpg','png','gif');
					if (empty($_POST["adpicture"])) {
						$msg .= __('URL of the picture is required.','bannergarden').'<br />';	
					} else {
						/*
						TODO: 
						Maybe we should check the format of url.
						But what happen, if it is not in absolute uri?
						Maybe enough, if we check is that file exists or not, if it isn't an url.
						In this lite version of bannergarden we allow only jpg, gif and png extension without any extra parameter.
						*/
						if (!$this->CheckValidExtension($_POST["adpicture"],$allowed_ext)) {
							$msg .= __('Invalid file extension. You can youse only jpg or jpeg, png, gif','bannergarden').'<br />';
						}
					}
					if (!empty($_POST["adlink"])) {
						if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $_POST["adlink"])) {
							$msg .= __('The format of Link URL is bad.','bannergarden').'<br />';	
						}
					}
					break;
			}
		}
		return $msg;
	}
	
	function GetBannerAjaxForm($type,$values = array()) {
		switch ($type) {
			/**** Picture ****/
			case "pic":
				$picture = '';
				$link = '';
				$nw_checked = '';
				
				if (array_key_exists('picture',$values)) {
					$picture = $values['picture'];
				}
				
				if (array_key_exists('link',$values)) {
					$link = $values['link'];
				}
				
				if (array_key_exists('new_window',$values)) {
					$new_window = $values['new_window'];
					if ($new_window == 1) {
						$nw_checked = ' checked="checked"';
					}
				}
				
				$html = '<div class="bg_label">'.__('URL of picture:','bannergarden').'</div>';
				$html .= '<small>'.__('Type an image url, or upload / select and image for the banner.','bannergarden').'</small>';
				$html .= '<div class="bg_input"><input type="text" id="adpicture" name="adpicture" value="'.$picture.'" /> <input id="upload_button" type="button" value="'.__('Upload / Select Image','bannergarden').'" /></div>';
				
				$html .= '<div class="bg_label">'.__('Link on click:','bannergarden').'</div>';
				$html .= '<small>'.__('Enter a URL where we redirect the user. Use full URL start with "http://" or "https://"','bannergarden').'</small>';
				$html .= '<div class="bg_input"><input type="text" id="adlink" name="adlink" value="'.$link.'" /></div>';
				
				$html .= '<div class="bg_label">'.__('Open in new window?','bannergarden').'</div>';
				$html .= '<small>'.__('Check this box if you want the link to open in a new window.','bannergarden').'</small>';
				$html .= '<div class="bg_input"><input type="checkbox" name="adnewwindow" value="1"'.$nw_checked.' /></div>';
				break;
			
			/**** Flash ****/	
			case "flash":
				$width = '';
				$height = '';
				$flash = '';
				
				if (array_key_exists('flash',$values)) {
					$flash = $values['flash'];
				}
				if (array_key_exists('width',$values)) {
					$width = $values['width'];
				}
				if (array_key_exists('width',$values)) {
					$height = $values['height'];
				}
				
				
				$html = '<div class="bg_label">'.__('URL of flash movie:','bannergarden').'</div>';
				$html .= '<small>'.__('Type a flash movie url, or upload / select and movie for the banner.','bannergarden').'</small>';
				$html .= '<div class="bg_input"><input type="text" id="adflash" name="adflash" value="'.$flash.'" /> <input id="upload_button" type="button" value="'.__('Upload / Select Flash','bannergarden').'" /></div>';
				
				$html .= '<div class="bg_label">'.__('Width:','bannergarden').'</div>';
				$html .= '<small>'.__('Enter the width of movie in pixels. Only numbers allowed.','bannergarden').'</small>';
				$html .= '<div class="bg_input"><input type="text" id="adwidth" name="adwidth" value="'.$width.'" />px</div>';
				
				$html .= '<div class="bg_label">'.__('Height:','bannergarden').'</div>';
				$html .= '<small>'.__('Enter the height of movie in pixels. Only numbers allowed.','bannergarden').'</small>';
				$html .= '<div class="bg_input"><input type="text" id="adheight" name="adheight" value="'.$height.'" />px</div>';
				break;
			
			/**** Ad Code ****/	
			case "code":
				$code = '';
				if (array_key_exists('code',$values)) {
					$code = $values['code'];
				}
				$valid_ext = array();
				$html = '<div>'.__('Copy/Paste your ad code here:','bannergarden').'<div>'."\n";
				$html .= '<textarea id="adcode" name="adcode" class="ta_adcode">'.stripslashes($code).'</textarea>';
				break;
			
			default:
				$html = 'Invalid type of media'; 
				break;
		}
		$html .= '<input type="hidden" name="type" value="'.$type.'" />';
		return $html;
	}
	
	/******************************/
	/******* TOOL FUNCTIONS *******/
	/******************************/
	
	function ShowError($msg) {
		echo $this->HtmlElements('diverror');
		echo '<p>'.$msg.'</p>';
		echo $this->HtmlElements('divclose');
	}
	
	function GetEnumValue($val,$item) {
		//Get the text of a value
		switch ($item) {
			case "active":
				switch ($val) {
					case 1: return __('Active','bannergarden'); break;	
					case 0: return __('Non active','bannergarden'); break;	
					default: return $val; break;
				}
				break;
			case "new_window":
				switch ($val) {
					case 0: return __('No','bannergarden'); break;	
					case 1: return __('Yes','bannergarden'); break;	
					default: return $val; break;
				}
				break;

			case "banner_type":
				switch ($val) {
					case 'pic': return __('Picture','bannergarden'); break;	
					case 'flash': return __('Flash','bannergarden'); break;	
					case 'code': return __('Ad Code','bannergarden'); break;	
					default: return $val; break;
				}
				break;
				
		

			default:
				return $val;
				break;	
		}	
	}
	
	function FormatTime($date) {
		if ($date == '0000-00-00 00:00:00') {
			return 'N/A';	
		} else {
			//TODO: Format datetime based on localization
			return $date;
		}
	}
	
	function HtmlElements($mode) {
		//Print some html element, like wrapper div, table header, table footer, etc...
		$html = '';
		switch ( $mode ) {
			case "title":
				$html = '<h2>Banner Garden</h2>'."\n";
				$html .='<hr class="bg_hr" />'."\n";
				break;
				
			case "divclose":
				$html = '</div>';
				break;
				
			case "divwrapper":
				$html = '<div class=wrap>' ."\n";
				break;
				
			case "tableliststart":
				$html = '<table cellspacing="0" cellpadding="0" border="0" class="BGTable">'."\n";
				break;
				
			case "tableclose":
				$html = '</table>';
				break;
				
			case "tablelistcampaignheader":
				$html = '<tr>'."\n";
				$html .= '<th>'.__('ID','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('Name','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('Start time','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('End time','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('Status','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('Banners','bannergarden').'</th>'."\n";
				$html .= '<th colspan="3">'.__('Operations','bannergarden').'</th>'."\n";
				$html .= '</tr>'."\n";
				break;
				
			case "tablelistbannerheader":
				$html = '<tr>'."\n";
				$html .= '<th>'.__('Name','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('Type','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('Width','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('Height','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('Link','bannergarden').'</th>'."\n";
				$html .= '<th>'.__('Open link in<br />new window','bannergarden').'</th>'."\n";
				$html .= '<th colspan="3">'.__('Operations','bannergarden').'</th>'."\n";
				$html .= '</tr>'."\n";
				break;
				
			case "diverror":
				$html = '<div id="bg_error" class="bg_error">';
				break;
				
			case "bannergarden_logo":
				$html = '<div id="bg_logo"><img src="'.$this->media_url.'/bg_logo.jpg" alt="Banner Garden" title="Banner Garden" /></div>';
				break;
		}
		return $html;
	}
	
	function CheckValidExtension($file,$ext_arr) {
		$finfo = pathinfo($file);
		if (!array_key_exists('extension',$finfo)) {
			return false;
		} else {
			if (!in_array(strtolower($finfo["extension"]),$ext_arr)) {
				return false;	
			} else {
				return true;	
			}
		}
	}
	
} //CLASS END
?>