<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require "../include/motd_functions.php";
require (dirname(__FILE__)."/admin_common.php");


JB_admin_header('Admin -> MOTD');
?>
<b>[Message Of The Day]</b> <span style="background-color: <?php if ($_REQUEST['type']=='U') { echo '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding:5px; "><a href="motd.php?type=U">Candidate's MOTD</a></span>
	<span style="background-color: <?php if ($_REQUEST['type']=='E') { echo '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding: 5px;"><a href="motd.php?type=E">Employer's MOTD</a></span>

	
	<hr>

<?php

global $AVAILABLE_LANGS;
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

	JB_save_motd($_REQUEST['type'], $_REQUEST['title'], $_REQUEST['message'], $_REQUEST['display']);
	$JBMarkup->ok_msg('MOTD Saved.');

}


if ($_REQUEST['type']=='') {

?>
<p>
Please select: <a href='motd.php?type=E'>MOTD for Employers</a> or <a href="motd.php?type=U">MOTD for Job Seekers</a>.
</p>
<?php

} else {
	$data = JB_load_motd($_REQUEST['type']);

	?>

	<form method="post" action="motd.php">
	Display MOTD: <input type="radio" value="YES" <?php if ($data['display']=='YES') { echo " checked ";} ?> name="display"> Yes <input type="radio" value="NO" <?php if ($data['display']=='NO') { echo " checked ";} ?> name="display"> No<br>
	<input type="hidden" name="type" value="<?php echo jb_escape_html($_REQUEST['type']); ?>">
	MOTD Title: <input type="text" name="title" value="<?php echo JB_escape_html($data['title']); ?>" size="50"><br>
	MOTD Message: <br>
	<?php

		require_once(JB_basedirpath()."include/lib/ckeditor/ckeditor.php") ;
		$CKEditor = new CKEditor();
		$CKEditor->initialized = false;
		$CKEditor->basePath	= jb_get_CK_js_base(true);
		$config = array (
			'toolbar'=>'Basic',
			'docType'=>$JBMarkup->doc_type,
			'width'=>'100%',
			'height'=>150,
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
