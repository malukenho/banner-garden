<script type="text/javascript">
	jQuery(function() {
		var type_value;
		
	
		/******************************************/
		/********* MEDIA LIBRARY CONTROL **********/
		/******************************************/
		var formfield;
		
		jQuery('#upload_button').live('click',function() {
 			//formfield = jQuery('#adpicture').attr('name');
 			if (type_value == 'pic') {
				formfield = jQuery('#adpicture').attr('name');
				tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
      } else if (type_value == 'flash') {
      	formfield = jQuery('#adflash').attr('name');
      	tb_show('', 'media-upload.php?type=file&amp;TB_iframe=true');
      }
 			return false;
		});
		
		window.send_to_editor = function(html) {
 			//imgurl = jQuery('img',html).attr('src');
 			//jQuery('#adpicture').val(imgurl);
 			if (type_value == 'pic') {
 				mediaurl = jQuery('img',html).attr('src');
 			} else if (type_value == 'flash') {
 				mediaurl = jQuery(html).attr('href');
 			}
 			jQuery('#' + formfield).val(mediaurl);
 			tb_remove();
		}

		/**************************************/
		/********* AJAX FORM CONTROL **********/
		/**************************************/
		
		$('#code, #flash, #pic').bind('click', function() {
			var ajax_div = '#media_ajax';
  		type_value = jQuery('#bannerform input:radio:checked').val();
			var error_div = "#bg_error";
			
<?php
		if ($_POST["type"]) {			
?>			
			var posted_type = '<?php echo $_POST["type"];?>';
			if (posted_type!= '' && posted_type != type_value && jQuery(error_div).length > 0) {
				jQuery(error_div).hide(500);
			} else if (posted_type!= '' && posted_type == type_value && jQuery(error_div).length > 0) {
				jQuery(error_div).show(500);
			}
<?php
		}
?>
			jQuery.ajax({
				type : 'POST',
				url : '<?php echo get_bloginfo('wpurl') . '/wp-content/plugins/bannergarden/bg-plugin-ajax.php' ?>',
				data: {
					type: type_value
<?php
					if (isset($_POST["type"])) {
						switch ($_POST["type"]) {
							case "code":
								echo ","."\n";
								//echo "code: '".mysql_real_escape_string($_POST["adcode"])."'";
								echo "code: '".base64_encode ($_POST["adcode"])."'";
								break;	
							case "flash":
								echo ",flash: '".base64_encode ($_POST["adflash"])."',"."\n";
								echo "width: '".$_POST["adwidth"]."',"."\n";
								echo "height: '".$_POST["adheight"]."'"."\n";
								break;	
							case "pic":
								$nw = 0;
								if ($_POST["adnewwindow"] == 1) { $nw = 1; }
								echo ",picture: '".base64_encode ($_POST["adpicture"])."',"."\n";
								echo "link: '".base64_encode ($_POST["adlink"])."',"."\n";
								echo "nw: '".$nw."'"."\n";
								break;	
						}	
					}							
?>							
				},beforeSend : function(XMLHttpRequest) {
					jQuery(ajax_div).html('<table><tr><td valign="bottom"><?php echo '<img src="'.WP_PLUGIN_URL.'/bannergarden/media/bannergarden-loader.gif" alt="" />';?></td><td><?php _e('Loading...','bannergarden');?></td></tr></table>');
				},success : function(data){
					jQuery(ajax_div).html(data);
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					jQuery(ajax_div).html('<?php _e('Ajax error happened.','bannergarden'); ?>');
				}
			});
		});
<?php
		if (!empty($_POST["type"])) {
			//Simulating click, to show the ajax form, if an error occured with the validation.
			echo "jQuery('#".$_POST["type"]."').trigger('click');";
		}
?>
	});
</script>