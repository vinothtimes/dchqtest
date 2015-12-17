<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
include('../config.php'); 
$save = $_REQUEST['save'];
$post_id = $_REQUEST['post_id'];
$type = $_REQUEST['type'];
$employer_id = (int) $_SESSION['employer_id'];
$_REQUEST['user_id'] = $employer_id;
define ('NO_HOUSE_KEEPING', true);
require (dirname(__FILE__)."/admin_common.php");
include_once('../include/posts.inc.php'); 

$PostingForm = &JB_get_DynamicFormObject(1);
$PostingForm->set_mode('edit');
echo $JBMarkup->get_admin_doctype();
$JBMarkup->markup_open(); // <html>

$JBMarkup->head_open();

$JBMarkup->stylesheet_link(JB_get_admin_maincss_url());

$JBMarkup->charset_meta_tag(); 
?>

<title><?php echo jb_escape_html(JB_SITE_NAME); ?> - Post a new Job</title>

<script type="text/javascript">


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
<script type="text/javascript" src="<?php echo jb_get_WZ_dragdrop_js_src(true);?>"></script> 

<?php

$JBMarkup->head_close();
$JBMarkup->body_open();
?>


<table style="margin: 0 auto;"><tr><td>


<?php 

$_FEES_ENABLED = "NO";
		
if ($_REQUEST['save'] != "" ) { // saving
	
	$errors = $PostingForm->validate(true);
	if ($errors) { // we have an error
		$mode = "edit";
		$PostingForm->display_form($mode, true);
	} else {
		$post_id = $PostingForm->save(true);
		$JBMarkup->ok_msg("Post Saved. <a href='posts.php' target='_parent'>Continue</a>.");

		?>

		<script type="text/javascript">
		window.setTimeout ("parent.scrollTo(0,0);",500);
		</script>

		<?php
		$PostingForm->load($post_id);
		$PostingForm->display_form('edit', true);
		
	}
} else {
	
	$mode = "edit";
	if ($_REQUEST['post_id'] != '') {
		$data = $PostingForm->load($post_id);
	} else {

		JB_prefill_posting_form (1, $PostingForm->get_values(), $_SESSION['JB_ID']);
	}

	?>
	
<p>&nbsp;</td>
</tr>
</table>

		<?php		

	$PostingForm = &JB_get_DynamicFormObject(1);
	$PostingForm->display_form($mode, true);
	$show_map = true;


	// old map system used pin_x and pin_y to save the pixel position on the map.
	if ($data['pin_x'] == '') {
		$data['pin_x'] = $_REQUEST['pin_x'];
		$data['pin_y'] = $_REQUEST['pin_y'];
	}

?>
</td></tr></table>

<?php

}


################################################################
	

$map_size = getimagesize(jb_get_map_img_path());
$pin_size = getimagesize(jb_get_pin_img_path());

if ((JB_MAP_DISABLED=="NO") && ($show_map)) {

	?>

	<img border="1" name="pin" alt="pin" src="<?php echo jb_get_pin_img_url(); ?>" <?php $size=getimagesize(jb_get_pin_img_path()) ?> width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>">
	
	<?php

	// old map system used pin_x and pin_y to save the pixel position on the map.
	$pin_y = $data['pin_y'];
	$pin_x = $data['pin_x'];

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
	if ($pin_x != '') {
	   echo "dd.elements.pin.moveTo(dd.elements.map.x+$pin_x, dd.elements.map.y+$pin_y); ";
	} else {
	?>
		dd.elements.pin.moveTo(dd.elements.map.x, dd.elements.map.y); 
	<?php } ?>
	dd.elements.pin.setZ(dd.elements.pin.z+1); 
	dd.elements.map.addChild("pin"); 
	
	</script>

<?php

}

$JBMarkup->body_close();
$JBMarkup->markup_close();
?>
