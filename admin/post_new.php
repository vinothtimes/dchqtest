<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('NO_HOUSE_KEEPING', true);
require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> New Post');

?>

<b>[POSTS]</b> <span style="background-color:#F2F2F2; border-style:outset; padding:5px; "><a href="posts.php?show=ALL">Approved Posts</a></span>
	<span style="background-color: <?php  echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="posts.php?show=WA">New Posts Waiting</a></span>
	<span style="background-color: <?php  echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="posts.php?show=NA">Non-Approved Posts</a></span>
	<span style="background-color: <?php echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="posts.php?show=EX">Expired Posts</a></span>
	<span style="background-color:FFFFCC; border-style:outset; padding: 5px;"><a href="post_new.php">Post a Job</a></span>
<hr>
<?php



if (is_numeric($_REQUEST['employer_id'])) {

	$_SESSION['employer_id'] = $_REQUEST['employer_id'];
}

?>

Select Employer Account:

<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=post" >
	<input type="hidden" name="new" value="<?php echo jb_escape_html($_REQUEST['new']);?>">
	<input type="hidden" name="go" value="2">
	<table border="0"  cellSpacing="1" cellPadding="5" bgColor="#d9d9d9">

	<tr>

	<td> 
	
	<b>Employer:</b> </td>
	<td colspan="2">
	<select name="employer_id" onchange="document.form1.submit()" >
	<option value="">[Select..]</option>
	<?php
	$sql = "select * from employers order by Username";
	$result = JB_mysql_query($sql);
	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		if ($row['ID'] == $_SESSION['employer_id'] ) {
			$sel = ' selected ';
		} else {
			$sel = '';
		}

		echo '<option '.$sel.' value="'.$row['ID'].'">'.JB_escape_html($row['Username']).' ('.JB_escape_html(substr($row['CompName'],0,28)).')</option>';



	}
	?>
	</select><br>
	
	</td>

	</tr>
	<tr>
	<td> 
	
	<b>Posting Type:</b> </td>
	<td>
	<input type="radio" name="type" value="" <?php if ($_REQUEST['type']=='') { echo ' checked '; } ?> > Standard <input type="radio" name="type" value="P" <?php if ($_REQUEST['type']=='P') { echo ' checked '; } ?>> Premium
	</td>
	</tr>
</table>

<?php

if (is_numeric($_SESSION['employer_id'])) {

	if (is_numeric($_REQUEST['repost'])) {
		$repost = '&repost_id='.$_REQUEST['post_id'];
		$_REQUEST['post_id'] = '';
	} 


	?>

	<iframe width="100%" FRAMEBORDER="0" id="post_form" height="1600" src="post_iframe.php?employer_id=<?php echo $_SESSION['employer_id']; ?>new=yes&amp;type=<?php echo $_REQUEST['type'].$repost ;?>" ></iframe>

	<?php

}


JB_admin_footer();
?>



