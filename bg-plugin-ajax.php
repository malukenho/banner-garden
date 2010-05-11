<?php
	header('Content-type: text/html; charset=UTF-8');
	require_once ("../../../wp-config.php");
	load_plugin_textdomain( 'bannergarden', false, dirname( plugin_basename( __FILE__ )).'/localization' );
	require_once ("bannergarden.class.php");
	if (class_exists('BannerGarden')) {
		$bg = new BannerGarden();
	}

	switch ($_POST["type"]) {
		case "pic":
			$val_arr = array('picture' => base64_decode($_POST["picture"]),
												'link' => base64_decode($_POST["link"]),
												'new_window' => $_POST["nw"]);
			$frm = $bg->GetBannerAjaxForm('pic',$val_arr);
			echo $frm;
			break;
			
		case "flash":
			$val_arr = array('flash' => base64_decode($_POST["flash"]),
												'width' => $_POST["width"],
												'height' => $_POST["height"]);
			$frm = $bg->GetBannerAjaxForm('flash',$val_arr);
			echo $frm;
			break;
			
		case "code":
			$val_arr = array('code' => base64_decode($_POST["code"]));
			$frm = $bg->GetBannerAjaxForm('code',$val_arr);
			echo $frm;
			break;
				
	}
?>