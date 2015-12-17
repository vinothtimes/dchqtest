<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require "../include/help_functions.php";
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Help Pages');

?>
<b>[Help Pages]</b> <span style="background-color: <?php if ($_REQUEST['type']=='U') { echo '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding:5px; "><a href="help_pages.php?type=U">Candidate's Help</a></span>
	<span style="background-color: <?php if ($_REQUEST['type']=='E') { echo '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding: 5px;"><a href="help_pages.php?type=E">Employer's Help</a></span>
	
	<hr>

<?php

global $ACT_LANG_FILES;
	echo "Current Language: [".$_SESSION["LANG"]."] Select language:";

?>

<form name="lang_form">
<input type="hidden" name="type" value="<?php echo jb_escape_html($_REQUEST['type']); ?>">
<select name='lang' onChange="document.lang_form.submit()">
<?php
foreach ($ACT_LANG_FILES as $key => $val) {
	$sel = '';
	if ($key==$_SESSION["LANG"]) { $sel = " selected ";}
	echo "<option $sel value='".$key."'>".$AVAILABLE_LANGS [$key]."</option>";

}

?>

</select>
</form>


<?php

if ($_REQUEST['save']!='') {

	JB_save_help($_REQUEST['type'], $_REQUEST['title'], $_REQUEST['message']);
	$JBMarkup->ok_msg('Help Saved.'); 

}


if ($_REQUEST['type']=='') {

	?>
	<p>
	Please select: <a href='help_pages.php?type=E'>Help for Employers</a> or <a href="help_pages.php?type=U">Help for Job Seekers</a>.
	</p>
	<?php

} else {


	$data = JB_load_help($_REQUEST['type']);



?>

	<form method="post" action="help_pages.php">
	<!--Display Help: --><input type="hidden" value="YES" <?php if ($data['display']=='YES') { echo " checked ";} ?> name="display"><!-- Yes <input type="radio" value="NO" <?php if ($data['display']=='NO') { echo " checked ";} ?> name="display"> No<br>-->
	<input type="hidden" name="type" value="<?php echo jb_escape_html($_REQUEST['type']); ?>">
	Help Title: <input type="text" name="title" value="<?php echo JB_escape_html($data['title']); ?>" size="50"><br>
	Help Message: <br>
	<?php

	require_once(JB_basedirpath()."include/lib/ckeditor/ckeditor.php") ;
	$CKEditor = new CKEditor();
	$CKEditor->initialized = false;
	$CKEditor->basePath	= jb_get_CK_js_base(true);
	$config = array (
		'toolbar'=>'Basic',
		'docType'=>$JBMarkup->doc_type,
		'width'=>40*15,
		'height'=>25*15,
		'language'=>'en'
	);

	$events['instanceReady'] = 'function (ev) { ev.editor.dataProcessor.writer.selfClosingEnd = \'>\'; }'; // turn off XHTML generation

	$CKEditor->editor('message', $data['message'], $config, $events);

	
		
	?>
	<br>
	<input name='save' type="submit" value="Save">
	</form>


	<?php



}

JB_admin_footer();

?>

