<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
 
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");

$offset = (int) $_REQUEST['offset'];
$post_id = (int) $_REQUEST['post_id'];

if ($post_id>0) { 
	$JBPage = new JBJobPage($post_id, $admin=true);
}

JB_admin_header('Admin -> Applications');

$ALM = &JB_get_ListMarkupObject('JBAppListMarkup');

$ALM->set_list_mode('ADMIN');

$COLSPAN = 5;
JBPLUG_do_callback('admin_apply_list_action_colspan', $COLSPAN); // a plugin can also set the colspan
$ALM->set_colspan($COLSPAN);



$post_id = (int) $_REQUEST['post_id'];
$action = jb_alpha($_REQUEST['action']);
$apps = $_REQUEST['apps'];


if ($_REQUEST['delete']) {
	
	for ($i=0; $i < sizeof($apps); $i++) {
		$sql = "DELETE FROM `applications` WHERE `app_id`='".jb_escape_sql($apps[$i])."' ";
		
		$result = JB_mysql_query ($sql) or die (mysql_error());
	}
	if (sizeof($apps)) {
		$JBMarkup->ok_msg('Application(s) deleted');
	} else {
		$JBMarkup->error_msg('No Application(s) selected');
	}
}

?>


<p>&nbsp;
<?php

if ($post_id != '') {

	$display_mode = "HALF";
	$JBPage->output($display_mode);


} else {

		if ($_REQUEST['purge']!='') {

		if ($_REQUEST['purge_days']==false) {
			$_REQUEST['purge_days'] = 356;

		}
		
		?>
		<form name="form3" width="100%" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
		<table bgcolor="#FF0000">
		<tr><td><font color="#ffffff">
		Confirm Delete - Purge all applications that are older than </font><input size="3" type="" name="purge_days" value="<?php echo jb_escape_html($_REQUEST['purge_days']); ?>"><font color="#ffffff"> days.  </font> <input name="purge2" type="submit" value="OK"><br>
		<i>You may purge the old applications to gain more space on your account.</i>
		</td></tr>
		</table>
		</form>
		<?php
	}

	if ($_REQUEST['purge2']!='') {
		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "DELETE from applications where DATE_SUB('$now', INTERVAL '".jb_escape_sql($_REQUEST['purge_days'])."' DAY) > app_date ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		$JBMarkup->ok_msg(JB_mysql_affected_rows()." application(s) deleted from the system");
		
	}

	$offset = (int) $_REQUEST['offset'];
	$records_per_page = 4;
	

	$sql = "SELECT * FROM applications ORDER BY `app_date` DESC LIMIT $offset, $records_per_page ";

	$result = JB_mysql_query($sql) or die (mysql_error());

	$count = array_pop(mysql_fetch_row(jb_mysql_query("SELECT count(*) FROM applications ")));

	if (mysql_num_rows($result) >0 ) {

		$result = JB_mysql_query($sql) or die (mysql_error());
		
		
		$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
		$LINKS = 10;
		

		$ALM->nav_pages_start();
		JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
	
		$ALM->nav_pages_end();

		$row['formatted_date'] = JB_get_formatted_date($row['app_date']);

		$ALM->open_form('form1');

		$ALM->list_start('joblist', 'list');

		$ALM->admin_list_controls();

		$ALM->list_head_open(); // <tr>


		$ALM->list_head_admin_action('apps'); 

		$ALM->list_head_cell_open(); echo $label["c_app_date"]; $ALM->list_head_cell_close();
		$ALM->list_head_cell_open(); echo $label["c_app_title"]; $ALM->list_head_cell_close();
		$ALM->list_head_cell_open(); echo $label["c_app_name"]; $ALM->list_head_cell_close();
		$ALM->list_head_cell_open(); echo $label["c_app_email"]; $ALM->list_head_cell_close();
		
	
		$ALM->list_head_close();

		$i=0;
		while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i<$records_per_page)) {

			$ALM->set_values($row);
			$i++;

			$new_window = "onclick=\"window.open('post_window.php?post_id=".$row['post_id']."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=800,height=500,left = 50,top = 50');return false;\"";

			$read_more = "onclick=\"window.open('view_cover.php?app_id=".$row['app_id']."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=600,height=400,left = 50,top = 50');return false;\"";

			$sql2 = "SELECT * FROM users where ID='".jb_escape_sql($row['user_id'])."'";
			$result2 = JB_mysql_query ($sql2) or die (mysql_error());
			$row2 = mysql_fetch_array($result2);

			$sql3 = "SELECT * FROM resumes_table where user_id='".jb_escape_sql($row['user_id'])."'";
			$result3 = JB_mysql_query ($sql3) or die (mysql_error());
			$row3 = mysql_fetch_array($result3);

			$ALM->list_item_open('standard');

			$ALM->list_data_admin_action();

			$ALM->list_cell_open(); echo JB_get_formatted_date(JB_get_local_time($row['app_date'])); $ALM->list_cell_close();

			$ALM->list_cell_open(); ?><a <?php echo $new_window; ?> href="posts.php?post_id=<?php echo $row['post_id'];?>"><?php echo JB_escape_html($row['data1']); ?></a><?php $ALM->list_cell_close();


			$ALM->list_cell_open();
		
			
			if ($row3['resume_id'] != '') {

				echo "<a href='resumes.php?resume_id=".$row3['resume_id']."'>".jb_escape_html(JB_get_formatted_name($row2['FirstName'], $row2['LastName']))."</a>"; 

			} else {
				echo jb_escape_html(JB_get_formatted_name($row2['FirstName'], $row2['LastName'])); 

			}

			$ALM->list_cell_close();	

			$ALM->list_cell_open(); echo JB_escape_html($row['data3']); $ALM->list_cell_close();

			JBPLUG_do_callback('admin_apply_list_data_columns', $result);

			$ALM->list_item_close();

			$ALM->list_item_open('standard');
			$ALM->cover_letter($label["emp_app_cover_letter"]);
			$ALM->list_item_close();

		}

		$ALM->list_end();
		$ALM->close_form();


		$ALM->nav_pages_start();	
		$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
		$LINKS = 10;
		JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
		$ALM->nav_pages_end();

	?>
	<p>
	<form name="form2" method="post" action="apps.php">
	<input name="purge" type="submit" value="Purge Old Applications...">
	</form>
	</p>
	<?php
	} else {
	?>
		<span><?php echo $label["c_app_no_apps"];?></span>
		<?php

	}
}


JB_admin_footer();


?>