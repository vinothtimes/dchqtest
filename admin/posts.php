<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> List Posts');

require_once ("../include/posts.inc.php");
require_once ("../include/category.inc.php");


if ($_REQUEST['show'] != '') {
	$show = $_REQUEST['show'];
	$_SESSION['show'] = $show;
}

if ($_REQUEST['show'] =='') {
	$_REQUEST['show'] = $_SESSION['show'];
}

$action = $_REQUEST['action'];
$posts = $_REQUEST['posts'];
$post_id = $_REQUEST['post_id'];
$cat_change = $_REQUEST['cat_change'];

?>
<p>
<b>[POSTS]</b> <span style="background-color: <?php if ($_SESSION['show']=='ALL') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding:5px; "><a href="posts.php?show=ALL">Approved Posts</a></span>
	<span style="background-color: <?php if ($_SESSION['show']=='WA') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="posts.php?show=WA">New Posts Waiting</a></span>
	<span style="background-color: <?php if ($_SESSION['show']=='NA') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="posts.php?show=NA">Non-Approved Posts</a></span>
	<span style="background-color: <?php if ($_SESSION['show']=='EX') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="posts.php?show=EX">Expired Posts</a></span>
	<span style="background-color: <?php  echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="post_new.php">Post a Job</a></span>
</p>
<hr>
<?php

$list_mode = 'ADMIN';
	
JB_display_dynamic_search_form (1);

if ($_REQUEST['purge']!='') {

	if ($_REQUEST['purge_days']=='') {
		$_REQUEST['purge_days'] = JB_POSTS_DISPLAY_DAYS;

	}
	
	?>
	<form name="form3" width="100%" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
	<table bgcolor="#FF0000">
	<tr><td><span style="color:#ffffff">
	Confirm Delete - Purge all posts that are older than </span><input size="3" type="" name="purge_days" value="<?php echo jb_escape_html($_REQUEST['purge_days']); ?>"><span style="color: #ffffff"> days.  </span> <input name="purge2" type="submit" value="OK"><br>
	<i>Are you sure that you want to purge? Expired posts still stay in the database as archived posts, and do not get deleted because sometimes you may still generate traffic from search engines. You may purge the expired posts to gain more space on your account. Additionaly, the purged posts will not be tallied in Admin->Stats anymore</i>
	</td></tr>
	</table>
	</form>
	<?php
}

if ($_REQUEST['show']=='ALL') {

	?>

	
<?php echo $label['category_header']?>
		<?php
			$cat = $_REQUEST['cat'];
			if ($cat=='') {
				$cat=0;

			} else {
				echo "<a href='".htmlentities($_SERVER['PHP_SELF'])."'>".$label['root_category_link']."</a>"; ?> <?php echo jb_escape_html(JB_getCatName($_REQUEST['cat']));?><br>
		&nbsp;&nbsp;&nbsp;&nbsp;<?php echo JB_getPath_templated($_REQUEST['cat']);
				$list_mode = "BY_CATEGORY_ADMIN";

			}
			//echo "cat is: [$cat]";
			$categories = JB_getCatStruct($cat, $_SESSION['LANG'], 1);
			JB_display_categories($categories, 3);
			?>
			
	<?php } ?>
<p>
	<?php



if ($_REQUEST['purge2']!='') {
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "SELECT post_id from posts_table where DATE_SUB('$now', INTERVAL '".jb_escape_sql($_REQUEST['purge_days'])."' DAY) > post_date ";
	$result = JB_mysql_query($sql) or die (mysql_error());
	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		JB_delete_post($row['post_id']);
	}
	JB_finalize_post_updates();
}


$user_id = $_REQUEST['user_id'];

if ($action == 'delete') {

	JB_delete_post($_REQUEST['post_id']);
	JB_finalize_post_updates();

   $JBMarkup->ok_msg("Job Post #$post_id deleted.");

}

if ($action=='Bulk Delete') {
	$posts = $_REQUEST['posts'];
	for ($i=0; $i < sizeof($posts); $i++) {
		JB_delete_post($posts[$i]);
		$JBMarkup->ok_msg("Job Post #".$posts[$i]." deleted.");
	}
	JB_finalize_post_updates();


}

if ($action == 'Approve') {

	$posts = $_REQUEST['posts'];

	$PForm = &JB_get_DynamicFormObject(1);

	for ($i=0; $i < sizeof($posts); $i++) {
		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "UPDATE `posts_table` SET `approved`='Y', `reason`='', post_date='".$now."' WHERE `post_id`='".jb_escape_sql($posts[$i])."'";
		JB_mysql_query($sql) or die (mysql_error());
		$JBMarkup->ok_msg('Job Post #'.jb_escape_html($posts[$i]).' approved!');

		JBPLUG_do_callback('admin_approve_post', $posts[$i]);
		// send out the email to the employer

		
		$post_data = $PForm->load($posts[$i]);
		JB_update_post_category_count($PForm->get_values());

		if (JB_EMAIL_POST_APPR_SWITCH == "YES") {

			// send approval notification email to employer

			

			$TITLE = ($PForm->get_raw_template_value ("TITLE"));
			$DATE = JB_get_formatted_date($PForm->get_template_value ("DATE"));
			$POSTED_BY_ID = $post_data['user_id'];

			// get the employer
			$sql = "SELECT * FROM employers WHERE ID='".jb_escape_sql($POSTED_BY_ID)."' ";

			$emp_result = jb_mysql_query($sql);
			$emp_row = mysql_fetch_array($emp_result);

			// get the email template
			$template_result = JB_get_email_template (220, $emp_row['lang']); 
			$t_row = mysql_fetch_array($template_result);

			$to_address = $emp_row['Email'];
			$to_name = jb_get_formatted_name($emp_row['FirstName'], $emp_row['LastName']);
			$subject = $t_row['EmailSubject'];
			$message = $t_row['EmailText'];
			$from_name = $t_row['EmailFromName'];
			$from_address = $t_row['EmailFromAddress'];

			/*substitute the vars

			%LNAME% - last name of the user
			%FNAME% - first name of the user
			%SITE_NAME% - name of your website
			%SITE_URL% - URL to your site
			%SITE_CONTACT_EMAIL% - contact email to your site.
			%POST_TITLE% - The title of the post
			%POST_DATE% - The date of the post
			%POST_URL% - The URL of the post
			
			*/

			$message = str_replace("%LNAME%", $emp_row['LastName'], $message);
			$message = str_replace("%FNAME%", $emp_row['FirstName'], $message);
			$message = str_replace("%SITE_NAME%", JB_SITE_NAME, $message);
			$message = str_replace("%SITE_URL%", JB_BASE_HTTP_PATH, $message);
			$message = str_replace("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $message);
			$message = str_replace("%POST_TITLE%", $TITLE, $message);
			$message = str_replace("%POST_DATE%", $DATE, $message);
			$message = str_replace("%POST_URL%", JB_BASE_HTTP_PATH."index.php?post_id=".$post_data['post_id'], $message);
			
			// Place the email on the queue!

			JB_queue_mail($to_address, $to_name, $from_address, $from_name, $subject, $message, '', 220);

		}
		

	}
	JB_finalize_post_updates();

}



if ($action == 'Disapprove') {

	if ($_REQUEST['reason']=='') {
		$JBMarkup->error_msg("<b>Error:</b>Cannot disapprove post(s) because the reason was not given.");
	} else {

		$posts = $_REQUEST['posts'];

		$PForm = &JB_get_DynamicFormObject(1);

		for ($i=0; $i < sizeof($posts); $i++) {
			$sql = "UPDATE `posts_table` SET `approved`='N', `reason`='".jb_escape_sql($_REQUEST['reason'])."' WHERE `post_id`='".jb_escape_sql($posts[$i])."'";
			JB_mysql_query($sql) or die (mysql_error().$sql);
			$JBMarkup->ok_msg('Job Post #.'.$posts[$i].' disapproved!');
			JBPLUG_do_callback('admin_disapprove_post', $posts[$i]);
			$post_data = $PForm->load($posts[$i]);
			
			JB_update_post_category_count($PForm->get_values());

			

			if (JB_EMAIL_POST_DISAPP_SWITCH == "YES")  {

				// send out the disapproval notification to the employer

				
				
				JB_update_post_category_count($PForm->get_values());

				// send approval notification email to employer

				

				$TITLE = ($PForm->get_raw_template_value ("TITLE"));
				$DATE = JB_get_formatted_date($PForm->get_template_value ("DATE"));
				$POSTED_BY_ID = $post_data['user_id'];

				// get the employer
				$sql = "SELECT * FROM employers WHERE ID='".jb_escape_sql($POSTED_BY_ID)."' ";

				$emp_result = jb_mysql_query($sql);
				$emp_row = mysql_fetch_array($emp_result);

				// get the email template
				$template_result = JB_get_email_template (230, $emp_row['lang']); 
				$t_row = mysql_fetch_array($template_result);

				$to_address = $emp_row['Email'];
				$to_name = jb_get_formatted_name($emp_row['FirstName'], $emp_row['LastName']);
				$subject = $t_row['EmailSubject'];
				$message = $t_row['EmailText'];
				$from_name = $t_row['EmailFromName'];
				$from_address = $t_row['EmailFromAddress'];

				/*substitute the vars

				%LNAME% - last name of the user
				%FNAME% - first name of the user
				%SITE_NAME% - name of your website
				%SITE_URL% - URL to your site
				%SITE_CONTACT_EMAIL% - contact email to your site.
				%POST_TITLE% - The title of the post
				%POST_DATE% - The date of the post
				%REASON% - The reason for the disapproval
				
				*/

				$message = str_replace("%LNAME%", $emp_row['LastName'], $message);
				$message = str_replace("%FNAME%", $emp_row['FirstName'], $message);
				$message = str_replace("%SITE_NAME%", JB_SITE_NAME, $message);
				$message = str_replace("%SITE_URL%", JB_BASE_HTTP_PATH, $message);
				$message = str_replace("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $message);
				$message = str_replace("%POST_TITLE%", $TITLE, $message);
				$message = str_replace("%POST_DATE%", $DATE, $message);
				$message = str_replace("%REASON%", stripslashes($_REQUEST['reason']), $message);
				
				// Place the email on the queue!

				JB_queue_mail($to_address, $to_name, $from_address, $from_name, $subject, $message, '', 230);

			}

		}
		JB_finalize_post_updates();
		
	}
 }

////////////////////////

if ($_REQUEST['plus_premium'] !='') { // upgrade to premium
	if (sizeof($_REQUEST['posts'])>0) {
		foreach ($_REQUEST['posts'] as $post_id) {
			$sql = "UPDATE `posts_table` SET post_mode = 'premium' WHERE post_id='".jb_escape_sql($post_id)."'";
			JB_mysql_query($sql) or die (mysql_error().$sql);
		}
		$JBMarkup->ok_msg('Upgraded Post(s) to Premium');
	}
	JB_finalize_post_updates();
}

////////////////

if ($_REQUEST['minus_premium'] !='') { // downgrade to standard

	if (JB_POSTING_FEE_ENABLED == 'YES') {
		$post_mode = "normal";
	} else {
		$post_mode = "free";
	}

	if (sizeof($_REQUEST['posts'])>0) {
		foreach ($_REQUEST['posts'] as $post_id) {
			$sql = "UPDATE `posts_table` SET post_mode = '".jb_escape_sql($post_mode)."' WHERE post_id='".jb_escape_sql($post_id)."'";
			JB_mysql_query($sql) or die (mysql_error().$sql);
		}

		$JBMarkup->ok_msg('Dowgraded Post(s) to Standard');
	}

	JB_finalize_post_updates();



}

////////////////////////////////
// bump_up

if ($_REQUEST['bump_up'] !='') { // change the date to latest 

	$now = (gmdate("Y-m-d H:i:s"));
	$PForm = &JB_get_DynamicFormObject(1);

	if (sizeof($_REQUEST['posts'])>0) {
		foreach ($_REQUEST['posts'] as $post_id) {

			$sql = "UPDATE `posts_table` SET post_date = '$now', expired='N' WHERE post_id='".jb_escape_sql($post_id)."'";
			
			JB_mysql_query($sql) or die (mysql_error().$sql);
			$PForm->load($post_id);
			JB_update_post_category_count($PForm->get_values());
			
		}

		$JBMarkup->ok_msg('Bumped post(s) up!');
	}
	JB_finalize_post_updates();



}

////////////////////////////////

if ($_REQUEST['cat_change'] != '') {

	if (sizeof ($posts) > 0) {

		echo "Select the category that you would like to move the post(s) to: <br>";

		

		$sql = "SELECT * FROM `form_fields` WHERE form_id=1 and field_type='CATEGORY' ";
		$result = JB_mysql_query ($sql) or die (mysql_error());
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			$sql = "SELECT post_id FROM posts_table where `".jb_escape_sql($row['field_id'])."`='".jb_escape_sql($_REQUEST['cat'])."' ";
			$result2 = JB_mysql_query ($sql) or die (mysql_error());
			if (mysql_num_rows($result2)> 0) {
				break;

			}

		}
		$category_init_id = $row['category_init_id'];
		$field_id = $row['field_id'];

		?>
		<form method="POST" >
		<input type="hidden" name="old_category" value="<?php echo jb_escape_html($_REQUEST['cat']);?>">
		<input type="hidden" name="posts" value="<?php echo implode(",",$_REQUEST['posts']);?>">
		<input type="hidden" name="field_id" value="<?php echo jb_escape_html($field_id) ;?>">
		<?php JB_category_select_field ('new_category', $category_init_id, 0, 1); ?><br>
		<input type="submit" name="move_to_cat" value="Move">

		</form>

		<?php

		
	}
}

if ($_REQUEST['move_to_cat'] != '') {
	$old_category = $_REQUEST['old_category'];
	$new_category = $_REQUEST['new_category'];
	$posts = explode (",", $_REQUEST['posts']);
// find the field id for this category...

	if ($new_category > 0) {

		$PForm = &JB_get_DynamicFormObject(1);

		foreach ($posts as $post_id) {

			$old_data = $PForm->load($post_id);

			$sql = "UPDATE `posts_table` SET `".jb_escape_sql($_REQUEST['field_id'])."`='".jb_escape_sql($new_category)."' WHERE post_id='".jb_escape_sql($post_id)."'  ";
			
			$result = JB_mysql_query ($sql) or die (mysql_error());

			$new_data = $PForm->load($post_id);

			JB_update_post_category_count($old_data, $new_data);
		}
		JB_finalize_post_updates();
	}
}


?>
<?php

?>
<div style="float: right;"><a href="get_csv.php?table=posts_table&amp;form_id=1">Download CSV</a> | <a href="postlist.php">Edit List</a></div>
	<?php

	switch ($_REQUEST['show']) {
		case "WA":
			echo "Now Showing: Posts Waiting.";
			break;
		case "EMP":
			echo "Now Showing: Posts Posted by Employer.";
			break;
		case "NA":
			echo "Now Showing: Posts Not Approved.";
			break;
		case "EX":
			echo "Now Showing: Posts Expired.";
			break;

	}

	if ($_REQUEST['show']=='EX') {

		show_expired_posts_button();
	
	}


	JB_list_jobs ($list_mode, $_REQUEST['show']);
	


?>

<hr>

<?php

function show_expired_posts_button() {

	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "SELECT post_id from posts_table where (DATE_SUB('$now', INTERVAL ".JB_POSTS_DISPLAY_DAYS." DAY) > post_date) OR expired='Y' LIMIT 10 ";
	$result = JB_mysql_query($sql) or die (mysql_error());

	$count = mysql_num_rows($result);

	if ($count > 0) {
		?>
		<form name="form2" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
		<table style="width:100%; background-color:#F0F0F0">
		<tr><td>
		You can purge expired posts from the database to save disk-space. <input name="purge" type="submit" value="Purge Expired Posts...">
		</td></tr>
		</table>
		</form>
		<?php

	}



}

if ($_REQUEST['show']=='ALL') {

	show_expired_posts_button();
	
}

JB_admin_footer();

?>



