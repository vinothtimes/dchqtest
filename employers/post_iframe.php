<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

define ('NO_HOUSE_KEEPING', true);
include('../config.php');

include('login_functions.php');
include_once('../include/posts.inc.php');

$PostingForm = &JB_get_DynamicFormObject(1);
$PostingForm->set_mode('edit');
if ($_REQUEST['save']!='') {
	$errors = $PostingForm->validate();
	if (!$errors) {
		$post_id = $PostingForm->save();
		$PostingForm->load($post_id); 
	}
}


$JBMarkup->enable_wz_dragdrop();
echo $JBMarkup->get_doctype();
$JBMarkup->markup_open(); // <html>

$JBMarkup->head_open();


JBPLUG_do_callback('post_iframe_header', $A=false); 
$JBMarkup->stylesheet_link(JB_get_maincss_url());// <link> to main.css

 $JBMarkup->charset_meta_tag(); ?>
<title><?php 
	$label["post_iframe_title"] = str_replace ("%SITE_NAME%", JB_SITE_NAME , $label["post_iframe_title"]);
	echo $label["post_iframe_title"]; ?></title>

<script type="text/javascript">
function confirmLink(theLink, theConfirmMsg) {
       if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
           return true;
       }
       var is_confirmed = confirm(theConfirmMsg + '\n');
       if (is_confirmed) {
           theLink.href += '&is_js_confirmed=1';
       }
       return is_confirmed;
   } // end of the 'confirmLink()' function

function save_pin() {
  map_x = dd.elements.pin.x - dd.elements.map.x;
  map_y = dd.elements.pin.y - dd.elements.map.y;
  window.status = "x:"+map_x+ " y:"+map_y;
  document.form1.pin_x.value=map_x;
  document.form1.pin_y.value=map_y;
 
}

function SubmitOnce(theform) {
   theform.savebutton.disabled=true;
  

}
</script>
<?php

$JBMarkup->head_close();
$JBMarkup->body_open('style="background-color:white; background-image: none;"');


JB_process_login(); 
	
 

$save = $_REQUEST['save'];
// set fees flag
if ((JB_POSTING_FEE_ENABLED == 'YES') || (JB_PREMIUM_POSTING_FEE_ENABLED == 'YES')) {
		$_FEES_ENABLED = "YES";		
}
$show_map = true;
if ($save != "" ) { // saving
	

	if ($errors) { // we have an error
		$mode = "edit";	
		$PostingForm->display_form($mode);

	} else {

		$label["post_iframe_p_saved"] = str_replace('%POST_TYPE%', 'type='.$_REQUEST['type'], $label["post_iframe_p_saved"]);
		$JBMarkup->ok_msg($label["post_iframe_p_saved"]);
			
		$show_map = false;
		
		// scroll the page up:
		?>

		<script type="text/javascript">
		window.setTimeout ("parent.scrollTo(0,0);",500);
		</script>
		<?php

		

		//$PostingForm->display_form('view');
		//echo $PostingForm->get_template_value('DESCRIPTION');

		
	}
} else {
	
	$mode = "edit";
	if ($_REQUEST['post_id'] != '') {
		
		$PostingForm->load($_REQUEST['post_id']);
		
		if ($_SESSION['JB_ID']!=$PostingForm->get_value('user_id')) {
			die ("Hacking attempt! Your IP address has been logged.");
			
		}
		
		$PostingForm->display_form($mode);

	} else {

		if (is_numeric($_REQUEST['repost_id'])) {
			
			$PostingForm->load($_REQUEST['repost_id']);
			$PostingForm->set_value('post_id', '');
			
			
			if (($PostingForm->get_value('post_mode')=='premium') && (JB_PREMIUM_AUTO_UPGRADE=='YES')) {
				$_REQUEST['type'] = '';

			}
		} else {
			JB_prefill_posting_form (1, $PostingForm->get_values(), $_SESSION['JB_ID']);
		}


				// Premium - paid, check for credits.
		if ($_REQUEST['type'] == 'premium') {

			 echo $label["post_iframe_post_prm"]; ?> <a href="" onclick="window.open('adsinfo.php', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=600,height=600,left=50,top=50');return false;"><b><?php echo $label["post_iframe_read_more"]."<Br>"; ?></b></a>
			
			<?php

			$posts_remain = JB_get_num_premium_posts_remaining($_SESSION['JB_ID']);
			
			if ($posts_remain > 0) {
				$label["post_iframe_prm_remain"] = str_replace  ("%P_POSTS%", $posts_remain, $label["post_iframe_prm_remain"]);
				$posts_remain_label = $label["post_iframe_prm_remain"];
			}
		}

		// if standard - paid, check for credits.
		elseif (JB_POSTING_FEE_ENABLED=='YES') {
			
			$posts_remain = JB_get_num_posts_remaining($_SESSION['JB_ID']);
			if ($posts_remain > 0) {
				$label["post_iframe_posts_remain"] = str_replace  ("%POSTS%", $posts_remain, $label["post_iframe_posts_remain"]);
				$posts_remain_label = $label["post_iframe_posts_remain"];
			}



		}

		// standard - free, check if unlimited.
		else {

			// FREE standard posts

				if (JB_FREE_POST_LIMIT=='YES') {
					// how many posts remaining..?
					$now = (gmdate("Y-m-d H:i:s"));
					$sql = "SELECT count(*) as MYCOUNT from posts_table WHERE user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND `post_mode`='free' AND expired='N' ";
					
					$result = JB_mysql_query($sql) or die (mysql_error());
					$row = mysql_fetch_array($result, MYSQL_ASSOC);
					$posts_remain = JB_FREE_POST_LIMIT_MAX - $row['MYCOUNT'];
					$label["post_iframe_remainmax"] = str_replace('%POSTS_REMAIN%', $posts_remain, $label["post_iframe_remainmax"]);
					$label["post_iframe_remainmax"] = str_replace('%JB_FREE_POST_LIMIT_MAX%', JB_FREE_POST_LIMIT_MAX, $label["post_iframe_remainmax"]);
					$posts_remain_label = $label["post_iframe_remainmax"];
				} else {
					$posts_remain = 100; // some really big number
					$posts_remain_label = $label["post_iframe_ulimitedfree"];

				}

		}
		$_PRIVILEGED_USER = false;
		$_PRIVILEGED_USER = JB_is_privileged_user($_SESSION['JB_ID'], $_REQUEST['type']);
		

		if ($_PRIVILEGED_USER) {
			$posts_remain_label = ""; // clear the label
			
		}

		// NOW print the form, or information

		if (($posts_remain > 0) || ($posts_remain==-1) || ($_PRIVILEGED_USER)) {
			echo "<p>";
			if ($_REQUEST['repost_id']!='') {
				echo "<h3>".$label["post_iframe_reposthead"]."</h3>";
				echo $label["post_iframe_repostdesc"];

			} else {
				echo $label["post_new_intro"];
			}
			echo "</p> ";
			echo $posts_remain_label;
			
			$PostingForm->display_form($mode);
		} else {

			if ($_REQUEST['type'] == 'premium') {
				$show_map = false;
				// tell the user to buy premium posts
				echo "<br>";
				echo $posts_remain_label;
				echo $label['buy_p_posts_msg'] ;
				echo "<br>";
				//echo $label["post_iframe_clickhere"];
				$offer_active_msg = JBEmployer::JB_get_special_offer_msg();
				echo $offer_active_msg;

				?>
				<p style="text-align: center;"><a href="credits.php" target="_parent"><IMG src="<?php echo JB_THEME_URL; ?>images/<?php echo $label['buy_p_posts_button_img']; ?>" width="187" height="41" border="0" alt="<?php echo $label["post_iframe_clickhere"]; ?>"></a>
				<?php if ($offer_active_msg) { ?>
				<b> OR </B>
				<a href="subscriptions.php" target='_parent'><IMG src="<?php echo JB_THEME_URL; ?>images/<?php echo $label['subscribe_now_button_img'];?>" width="187" height="41" border="0" alt=""></a>
				</p>
				<?php } ?>
				<?php

			} elseif (JB_POSTING_FEE_ENABLED=='YES') {
				$show_map = false;

				
				// tell the user to buy posts
				echo $posts_remain_label;

				echo $label['buy_posts_msg'];

				$offer_active_msg = JBEmployer::JB_get_special_offer_msg();
				echo $offer_active_msg;
				?>
				
				<?php //echo $label["post_iframe_pst_click"];?>
				<p style="text-align: center;">
				<a href='credits.php' target='_parent'><IMG src="<?php echo JB_THEME_URL; ?>images/<?php echo $label['buy_posts_button_img']; ?>" width="187" height="41" border="0" alt="<?php echo $label["post_iframe_add"]; ?>"><a>
				<?php if ($offer_active_msg) { ?>
				<b> OR </B>
				<a href="subscriptions.php" target='_parent'><IMG src="<?php echo JB_THEME_URL; ?>images/<?php echo $label['subscribe_now_button_img']; ?>" width="187" height="41" border="0" alt=""></a>
				<?php } ?>
				</p>
			<?php
			

			} else {

				if (JB_FREE_POST_LIMIT=='YES') {
					// tell the user to delete old posts
					echo $posts_remain_label;
					echo "<br>".$label["post_iframe_posts_delete_old"];

				} 

			}

		}
	
	}


}

if ($PostingForm->get_values('pin_x') == '') {
	$PostingForm->set_value('pin_x', $_REQUEST['pin_x']);
	$PostingForm->set_value('pin_y', $_REQUEST['pin_y']);
}


################################################################

?>
<script type="text/javascript">
	// HtmlArea is no more...
	//HTMLArea.replace('ad');
	if (document.forms[0]) {
		window.setTimeout ("parent.scrollTo(0,0);",500);
	}
</script>

<?php

if ((JB_MAP_DISABLED=="NO") && ($show_map)) {

?>

	<img border="1" alt="pin" name="pin" src="<?php echo jb_get_pin_img_url(); ?>" <?php $size=getimagesize(jb_get_pin_img_path()) ?> width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>"><p></p>
	<?php

	$map_size = getimagesize(jb_get_map_img_path());
	$pin_size = getimagesize(jb_get_pin_img_path());

	//echo "pin size size: ".$pin_size[0];

	$pin_y = $PostingForm->get_value('pin_y');
	$pin_x = $PostingForm->get_value('pin_x');

    //print ("piny $pin_y pinx $pin_x");

	$right = ($map_size[0]-$pin_size[0])-$pin_x; // map_x - pin_x
	$bottom = $map_size[1]-$pin_y;

	if ($pin_y == '' ) {
	   $pin_y=0;
	}

	if ($pin_x == '' ) {
	   $pin_x=0;
	}
	?>
	<script type="text/javascript">
	
		SET_DHTML("pin"+MAXOFFLEFT+<?php echo $pin_x+$pin_size[0]; ?>+MAXOFFRIGHT+<?php echo $right;?>+MAXOFFBOTTOM+<?php echo $bottom;?>+MAXOFFTOP+<?php echo $pin_y; ?>+CURSOR_MOVE,"map"+NO_DRAG);
		<?php
		
		 echo "dd.elements.pin.moveTo(dd.elements.map.x+$pin_x, dd.elements.map.y+$pin_y); ";
		
		?>
		
		dd.elements.pin.setZ(dd.elements.pin.z+1); 
		dd.elements.map.addChild("pin"); 

	</script>
 <?php 
 
 } 
 
$JBMarkup->body_close();
$JBMarkup->markup_close();

?>
