<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require (dirname(__FILE__)."/admin_common.php");
require_once ("../include/posts.inc.php");
require ("../include/profiles.inc.php");



require_once "../include/employers.inc.php";

$EmployerForm = &JB_get_DynamicFormObject(4);
$EmployerForm->set_mode('edit');
$ListMarkup = &JB_get_ListMarkupObject('JBEmpListMarkup');

# Copyright 2005-2009 Jamit Software
# http://www.jamit.com/


if (isset($_REQUEST['mail']) ? $mail = $_REQUEST['mail'] : $mail='');
if (isset($_REQUEST['users']) ? $users = $_REQUEST['users'] : $users='');
if (isset($_REQUEST['msg_type']) ? $msg_type = $_REQUEST['msg_type'] : $msg_type='');
if (isset($_REQUEST['message']) ? $message = $_REQUEST['message'] : $message='');
 if (isset($_REQUEST['subject']) ? $subject = $_REQUEST['subject'] : $subject='');

// change password
if (isset($_REQUEST['user_id']) ? $user_id = $_REQUEST['user_id'] : $user_id='');
if (isset($_REQUEST['pass']) ? $pass = $_REQUEST['pass'] : $pass='');

if (isset($_REQUEST['action']) ? $action = $_REQUEST['action'] : $action='');

if (isset($_REQUEST['is_js_confirmed']) ? $is_js_confirmed = $_REQUEST['is_js_confirmed'] : $is_js_confirmed='');

if (isset($_REQUEST['show_emp']) ? $show_emp = $_REQUEST['show_emp'] : $show_emp='');
if (isset($_REQUEST['cat']) ? $cat = $_REQUEST['cat'] : $cat='');
if (!isset($_REQUEST['offset'])) $_REQUEST['offset']='';
if (!isset($q_string)) $q_string='';
if (!isset($_REQUEST['show'])) $_REQUEST['show'] = '';
if (!isset($_REQUEST['reset'])) $_REQUEST['reset'] = '';

if ($_REQUEST['action'] == 'search' ) {
	$q_string = JB_generate_emp_q_string();
}

JB_admin_header('Admin -> Employers');

?>

	<b>[EMPLOYERS]</b> <span style="background-color: <?php if ($_REQUEST['show']=='') { echo '#FFFFCC'; } else { echo "#F2F2F2"; } ?>; border-style:outset; padding:5px; "><a href="employers.php">List Employers</a></span>
	<span style="background-color: <?php if ($_REQUEST['show']=='NA') { echo '#FFFFCC'; } else { echo "#F2F2F2"; } ?>; border-style:outset; padding: 5px;"><a href="employers.php?show=NA">Non-Validated Employers</a></span>
	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="profiles.php">Employer Profiles</a></span>
	<hr>

<?php


if ($action == 'Send Email') {

   

   if (!is_array($users)) {

	   $JBMarkup->error_msg("ERROR! No Users Selected. Use the check-boxes to select the users that you want to email.");

   } else {

		echo "sending email...<br>";
	   for ($i=0; $i < sizeof($users); $i++) {


		  $sql = "SELECT * from `employers` WHERE `ID`='".jb_escape_sql($users[$i])."'";
		  $result = JB_mysql_query($sql) or die (mysql_error());
		  $row = mysql_fetch_array($result, MYSQL_ASSOC);

		  
		 // stripslashes() to be removed in the future
		  $msg = stripslashes($msg);
		  $subject = stripslashes($subject);

		  $msg = str_replace ( "%name%", JB_get_formatted_name($row['FirstName'], $row['LastName']), $message);
		  $msg = str_replace ( "%username%", $row['Username'], $msg);
		  $msg = str_replace ( "%email%", $row['Email'], $msg);


		  $to = $row['Email']; //$users[$i];
		  $from = JB_SITE_CONTACT_EMAIL; // Enter your email adress here
			
		
		  $email_id=JB_queue_mail($to, JB_get_formatted_name($row['FirstName'], $row['LastName']), JB_SITE_CONTACT_EMAIL, JB_SITE_NAME, ($subject), ($msg), '', 8);
		  $JBMarkup->ok_msg("Email queued to:". JB_escape_html($row['Email']));
		  JB_process_mail_queue(1, $email_id);



	   }
   }

}

if ($action == 'Activate') {

	for ($i=0; $i < sizeof($users); $i++) {

		$sql = "UPDATE `employers` SET `validated`='1' WHERE `ID`='".jb_escape_sql($users[$i])."'";
		JB_mysql_query($sql);
    
		$sql = "SELECT * from `employers` WHERE `ID`='".jb_escape_sql($users[$i])."'";
		$result = JB_mysql_query($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		$JBMarkup->ok_msg("Activated username: <b>".JB_escape_html($row['Username'])."</b> (".JB_escape_html(JB_get_formatted_name($row['FirstName'], $row['LastName'])).")");
		
	}

    ?>
   <p>
   <a href="employers.php">Employer Accounts</a>
   <?php

   
}




if ($action == 'Suspend') {

	for ($i=0; $i < sizeof($users); $i++) {
	   
	   $sql = "UPDATE `employers` SET `validated`='0' WHERE `ID`='".jb_escape_sql($users[$i])."'";
	   JB_mysql_query($sql);
	   $sql = "SELECT * from `employers` WHERE `ID`='".jb_escape_sql($users[$i])."'";
	   $result = JB_mysql_query($sql) or die (mysql_error());
	   $row = mysql_fetch_array($result, MYSQL_ASSOC);

	   $JBMarkup->ok_msg("Suspended username: <b>".JB_escape_html($row['Username'])."</b> (".jb_escape_html(JB_get_formatted_name($row['FirstName'], $row['LastName'])).')');

	   
	}
	

	?>
	<p>
	Go to: <a href="employers.php?show=NA">Employer Accounts - Non-Valid</a> to view the suspended accounts.
	</p>

	<?php

}


function delete_employer($employer_id) {

	global $JBMarkup;

		// check for transactions...

	if ($_REQUEST['delete_it'] == '') {
	
		$sql = "SELECT * from `package_invoices`, `subscription_invoices` WHERE package_invoices.employer_id='".jb_escape_sql($employer_id)."' OR subscription_invoices.employer_id='".jb_escape_sql($employer_id)."' limit 10";
		$result = JB_mysql_query($sql) or die (mysql_error());
		

		if (mysql_num_rows($result)>0) {
		  $del_confirm = true;
		}

		$sql = "SELECT * from posts_table WHERE user_id='".jb_escape_sql($employer_id)."' limit 1 ";
		$result = JB_mysql_query($sql) or die (mysql_error());

		if (mysql_num_rows($result)>0) {
		  $del_confirm = true;
		}

		if ($del_confirm) {

		  echo "<font color='maroon'><b>Cannot delete this account. This employer has some records stored in the database.</b></font> <a href='employers.php?action=delete&amp;user_id=$employer_id&amp;delete_it=yes'>Click here</a> to delete anyway.";

		}
  
	}
  
  if (!$del_confirm) {
	  $_REQUEST['delete_it']='yes';
  }

  

   if ($_REQUEST['delete_it']!='') {



	   JB_delete_employer ($employer_id);
	   $JBMarkup->ok_msg("Deleted User $employer_id.");

	   
	   ?>
	



   <?php

   } 


}

if (strtolower($action) == 'delete') {

	//print_r($_REQUEST);
	if (sizeof($_REQUEST['users'])>0) {
		for ($i=0; $i < sizeof($users); $i++) {
			delete_employer($users[$i]);
		}
	} else {
		delete_employer($_REQUEST['user_id']);
	}
	
}



if (is_numeric($_REQUEST['reset'])) {
   
   if ($pass != '') {
      $pass = md5($pass);
      $sql = "UPDATE `employers` SET `Password`='$pass' WHERE `ID`='".jb_escape_sql($_REQUEST['reset'])."' LIMIT 1";
      JB_mysql_query($sql) or die(mysql_error());

	  $sql = "SELECT * from `employers` WHERE `ID`='".jb_escape_sql($_REQUEST['reset'])."'";
      $result = JB_mysql_query($sql) or die (mysql_error());
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
    
	  $JBMarkup->ok_msg('Password Changed.');

	  JBPLUG_do_callback('emp_new_pass', $_REQUEST['pass'], $row['Username']);
   } else {

	   $sql = "SELECT * from `employers` WHERE `ID`='".jb_escape_sql($_REQUEST['reset'])."'";
      $result = JB_mysql_query($sql) or die (mysql_error());
      $row = mysql_fetch_array($result, MYSQL_ASSOC);

   

      ?>
	     Change the password for username: 
		 <?php 

		if ($_REQUEST['action']=='search') {
			$q_string = JB_generate_emp_q_string();
		}

		echo JB_escape_html($row['Username'])." (".jb_escape_html(JB_get_formatted_name($row['FirstName'], $row['LastName'])).")";
	     ?>

         <form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?offset=<?php echo $_REQUEST['offset']?>&action=reset&<?php echo $q_string;?>">
            <input type="hidden" name="reset" value="<?php echo jb_escape_html($_REQUEST['reset']);?>">
			<input type="hidden" name="offset" value="<?php echo jb_escape_html($_REQUEST['offset']);?>">
            New Password:<input type="text" name="pass"><br>
            <input type="submit" value="OK">  <input type="button" value="Cancel" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF'])."?offset=".jb_escape_html($_REQUEST['offset'])."&amp;".$q_string;?>'">
         </form>

		 </body>
		 </html>


      <?php

		  die();




   }

}


if (isset($_REQUEST['q_aday']) ? $q_aday = $_REQUEST['q_aday'] : $q_aday='');
if (isset($_REQUEST['q_amon']) ? $q_amon = $_REQUEST['q_amon'] : $q_amon='');
if (isset($_REQUEST['q_ayear']) ? $q_ayear = $_REQUEST['q_ayear'] : $q_ayear='');
if (isset($_REQUEST['q_name']) ? $q_name = $_REQUEST['q_name'] : $q_name='');
if (isset($_REQUEST['q_username']) ? $q_username = $_REQUEST['q_username'] : $q_username='');
if (isset($_REQUEST['q_resumes']) ? $q_resumes = $_REQUEST['q_resumes'] : $q_resumes='');
if (isset($_REQUEST['q_news']) ? $q_news = $_REQUEST['q_news'] : $q_news='');
if (isset($_REQUEST['q_email']) ? $q_email = $_REQUEST['q_email'] : $q_email='');
if (isset($_REQUEST['q_company']) ? $q_company = $_REQUEST['q_company'] : $q_company=''); 

if (is_numeric($_REQUEST['user_id'] ) && ($action!=='delete')) {

	
	if ($_REQUEST['form'] != "" ) { // saving

		$employer_id = $EmployerForm->save($admin=true); 
		$JBMarkup->ok_msg("Employer data updated.");

	} else {
		
		$mode = "edit";
		
		$EmployerForm->load($_REQUEST['user_id']);
		$EmployerForm->display_form('edit', true);
	}

}

JBPLUG_do_callback('employer_admin_top', $A=false);

if (($_REQUEST['show'] == '' ) || ($_REQUEST['show'] == 'NA' )) {

	?>

<form style="margin: 0" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=search" method="post">

<input type="hidden" name="order_by" value="<?php echo jb_escape_html($_REQUEST['order_by']); ?>">
<input type="hidden" name="ord" value="<?php echo jb_escape_html($_REQUEST['ord']); ?>">
<input type="hidden" name="show" value="<?php echo jb_escape_html($_REQUEST['show']); ?>">        
           <center>
         <table border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse"  id="AutoNumber2" width="100%">
    
    <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><font size="2" face="Arial"><b>Name</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
      <input type="text" name="q_name" size="39" value="<?php echo jb_escape_html(stripslashes($q_name));?>" ></font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">Username</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_username" size="28" value="<?php echo jb_escape_html(stripslashes($q_username)); ?>"></td>
    </tr>
    <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">Alerts</font></b></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
      &nbsp;<input type="checkbox" name="q_news" value="ON" <?php if ($q_news != '') {echo 'checked'; } ?> > &gt; <font size="2" face="arial"> &nbsp;</FONT></font><font face="arial" size="2">Newsletter</font><font face="Arial">
      <input type="checkbox" name="q_resumes" value="ON" <?php if ($q_resumes != '') {echo 'checked'; } ?> > &gt;
        <font size="2" face="arial"> &nbsp;</font></font><font face="arial" size="2">Resumes</font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">Email</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_email" size="28" value="<?php echo jb_escape_html(stripslashes($q_email)); ?>" ></td>
    </tr>
    <tr>
       <td align="top" colspan="2" bgcolor="#EDF8FC"><b>
       <font face="Arial" size="2">Signed</font></b><font size="2" face="Arial"><b> 
       Up After:</b></font>

       <select name="q_aday">
                            <option></option>
                            <option <?php if ($q_aday=='01') { echo ' selected ';} ?> >1</option>
                            <option <?php if ($q_aday=='02') { echo ' selected ';} ?> >2</option>
                            <option <?php if ($q_aday=='03') { echo ' selected ';} ?> >3</option>
                            <option <?php if ($q_aday=='04') { echo ' selected ';} ?> >4</option>
                            <option <?php if ($q_aday=='05') { echo ' selected ';} ?> >5</option>
                            <option <?php if ($q_aday=='06') { echo ' selected ';} ?> >6</option>
                            <option <?php if ($q_aday=='07') { echo ' selected ';} ?>>7</option>
                            <option <?php if ($q_aday=='08') { echo ' selected ';} ?>>8</option>
                            <option <?php if ($q_aday=='09') { echo ' selected ';} ?> >9</option>
                            <option <?php if ($q_aday=='10') { echo ' selected ';} ?> >10</option>
                            <option <?php if ($q_aday=='11') { echo ' selected ';} ?> > 11</option>
                            <option <?php if ($q_aday=='12') { echo ' selected ';} ?> >12</option>
                            <option <?php if ($q_aday=='13') { echo ' selected ';} ?> >13</option>
                            <option <?php if ($q_aday=='14') { echo ' selected ';} ?> >14</option>
                            <option <?php if ($q_aday=='15') { echo ' selected ';} ?> >15</option>
                            <option <?php if ($q_aday=='16') { echo ' selected ';} ?> >16</option>
                            <option <?php if ($q_aday=='17') { echo ' selected ';} ?> >17</option>
                            <option <?php if ($q_aday=='18') { echo ' selected ';} ?> >18</option>
                            <option <?php if ($q_aday=='19') { echo ' selected ';} ?> >19</option>
                            <option <?php if ($q_aday=='20') { echo ' selected ';} ?> >20</option>
                            <option <?php if ($q_aday=='21') { echo ' selected ';} ?> >21</option>
                            <option <?php if ($q_aday=='22') { echo ' selected ';} ?> >22</option>
                            <option <?php if ($q_aday=='23') { echo ' selected ';} ?> >23</option>
                            <option <?php if ($q_aday=='24') { echo ' selected ';} ?> >24</option>
                            <option <?php if ($q_aday=='25') { echo ' selected ';} ?> >25</option>
                            <option <?php if ($q_aday=='26') { echo ' selected ';} ?> >26</option>
                            <option <?php if ($q_aday=='27') { echo ' selected ';} ?> >27</option>
                            <option <?php if ($q_aday=='28') { echo ' selected ';} ?> >28</option>
                            <option <?php if ($q_aday=='29') { echo ' selected ';} ?> >29</option>
                            <option <?php if ($q_aday=='30') { echo ' selected ';} ?> >30</option>
                            <option <?php if ($q_aday=='31') { echo ' selected ';} ?> >31</option>
                          </select>
                          <select name="q_amon" >
                           <option ></option>
                            <option <?php if ($q_amon=='01') { echo ' selected ';} ?> value="1">Jan</option>
                            <option <?php if ($q_amon=='02') { echo ' selected ';} ?> value="2">Feb</option>
                            <option <?php if ($q_amon=='03') { echo ' selected ';} ?> value="3">Mar</option>
                            <option <?php if ($q_amon=='04') { echo ' selected ';} ?> value="4">Apr</option>
                            <option <?php if ($q_amon=='05') { echo ' selected ';} ?> value="5">May</option>
                            <option <?php if ($q_amon=='06') { echo ' selected ';} ?> value="6">Jun</option>
                            <option <?php if ($q_amon=='07') { echo ' selected ';} ?> value="7">Jul</option>
                            <option <?php if ($q_amon=='08') { echo ' selected ';} ?> value="8">Aug</option>
                            <option <?php if ($q_amon=='09') { echo ' selected ';} ?> value="9">Sep</option>
                            <option <?php if ($q_amon=='10') { echo ' selected ';} ?> value="10">Oct</option>
                            <option <?php if ($q_amon=='11') { echo ' selected ';} ?> value="11">Nov</option>
                            <option <?php if ($q_amon=='12') { echo ' selected ';} ?> value="12">Dec</option>
                          </select>
                          <input type="text"  name="q_ayear" size="4"  value="<?php echo $q_ayear; ?>" >
                        
                          </td>

    </td>
    <td colspan="1" bgcolor="#EDF8FC">
    <p style="float: right;"><b><font face="Arial" size="2">Company</font></b><font size="2" face="Arial"> </font></td>
    <td colspan="1" bgcolor="#EDF8FC"><input type="text" size="28" name="q_company" value="<?php echo jb_escape_html(stripslashes($q_company)); ?>"></td>
    <tr>
      <td width="731" bgcolor="#EDF8FC" colspan="2">
      <font face="Arial"><b>
      <input type="submit" value="Search Employers" name="B1" style="float: left">
	  </b></td><td colspan="2">
	  <?php if (true) { ?>&nbsp; </font><b>[<font face="Arial"><a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">Start a New Search</a></font>]</b><?php } ?></td>
    </tr>
    </table>

           </center>
         

</form>
<div style="float: right;">
<font size="2"><a href="get_csv.php?table=employers">Download CSV</a></font> | <font size="2"><a href="employerlist.php">Edit List</a></font>
</div>
<?php


} 





if (!isset($_REQUEST['show'])) $_REQUEST['show']='';

$where_sql='';

if ($_REQUEST['show'] == "NA") {
	echo "<h3>Listing Non-Valid Employer Accounts</h3>";
	$where_sql = " AND Validated='0' ";
} elseif ($_REQUEST['show'] == '') {
	$where_sql = " AND Validated='1' ";
}

if ($_REQUEST['q_name'] != '') {
	$list = preg_split ("/[\s,]+/", $_REQUEST['q_name']);
    for ($i=1; $i < sizeof($list); $i++) {
		$or1 .=" OR (`FirstName` like '%".jb_escape_sql($list[$i])."%')";
		$or2 .=" OR (`LastName` like '%".jb_escape_sql($list[$i])."%')";
    }
    $where_sql .= " AND (((`FirstName` like '%".jb_escape_sql($list[0])."%') $or1) OR ((`LastName` like '%".jb_escape_sql($list[0])."%') $or2))";
}

if ($_REQUEST['q_username'] != '') {
	$_REQUEST['q_username'] = trim($_REQUEST['q_username']);
	$list = preg_split ("/[\s,]+/", $_REQUEST['q_username']);
    for ($i=1; $i < sizeof($list); $i++) {
		$or .=" OR (`Username` like '%".jb_escape_sql($list[$i])."%')";
    }
    $where_sql .= " AND ((`Username` like '%".jb_escape_sql($list[0])."%') $or)";
}

if ($_REQUEST['q_company'] != '') {
	$_REQUEST['q_company'] = trim ($_REQUEST['q_company']);
	$list = preg_split ("/[\s,]+/", $_REQUEST['q_company']);
    for ($i=1; $i < sizeof($list); $i++) {
		$or .=" OR (`CompName` like '%".jb_escape_sql($list[$i])."%')";
    }
    $where_sql .= " AND ((`CompName` like '%".jb_escape_sql($list[0])."%') $or)";
}

if ($_REQUEST['q_email'] != '') {
	$_REQUEST['q_email'] = trim ($_REQUEST['q_email']);
	$list = preg_split ("/[\s,]+/", $_REQUEST['q_email']);
    for ($i=1; $i < sizeof($list); $i++) {
		$or .=" OR (`Email` like '%".jb_escape_sql($list[$i])."%')";
    }
    $where_sql .= " AND ((`Email` like '%".jb_escape_sql($list[0])."%') $or)";
}

if (($_REQUEST['q_aday'] !='') && ($_REQUEST['q_amon']!='') && ($_REQUEST['q_ayear']!='')) {
	$_REQUEST['q_ayear'] = trim ($_REQUEST['q_ayear']);
    $q_date = $_REQUEST['q_ayear']."-".$_REQUEST['q_amon']."-".$_REQUEST['q_aday'];
    $where_sql .= " AND  '".jb_escape_sql($q_date)."' <= `SignupDate` ";
}

if ($q_news != '') {
	$where_sql .= " AND `Newsletter`='1' "; 

}

if ($q_resumes !='') {
	$where_sql .= " AND `Notification1`='1' ";

}

if ($_REQUEST['ord']=='asc') {
	$ord = 'ASC';
} elseif ($_REQUEST['ord']=='desc') {
	$ord = 'DESC';
} else {
	$ord = 'DESC'; // sort descending by default
}

if (($_REQUEST['order'] == '') || (!JB_is_field_valid($_REQUEST['order'], 4))) {
	// by default, order by the post_date
	$order = " `SignupDate` ";           
} else {
	$order = " `".jb_escape_sql($_REQUEST['order'])."` ";
}




$records_per_page = 20;
$offset = (int) $_REQUEST['offset'];
if ($offset<0) {
	$offset = abs($offset);
}
$sql = "select SQL_CALC_FOUND_ROWS * FROM `employers` WHERE 1=1 $where_sql ORDER BY $order $ord LIMIT $offset,$records_per_page";

if ($_REQUEST['order_by']=='posts') {
	// need to order the list by the number of jobs each employer posted
	$sql = " select t1.*, count(post_id) as post_count from employers as t1 LEFT JOIN posts_table as t2 ON t1.ID=t2.user_id GROUP by t2.user_id ORDER BY Validated, post_count $ord";
}

$result = JB_mysql_query($sql) or die (mysql_error());

$row = mysql_fetch_row(jb_mysql_query("SELECT FOUND_ROWS()"));
$count = $row[0];


if ($count > 0 ) {

?>
<form style="margin: 0px;" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF'])."?".$q_string; ?>" name="form1" >

<?php


	if ($count > $records_per_page)  {

		$pages = ceil($count / $records_per_page);
		$cur_page = $_REQUEST['offset'] / $records_per_page;
		$cur_page++;

		echo "<center>";
		?>
		<center><b><?php echo $count; ?> Employers Returned (<?php echo $pages;?> pages) </b></center>
		<?php
		echo "Page $cur_page of $pages - ";
		$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
		$LINKS = 10;
		JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
		echo "</center>";

	}



?>

<p>




<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >
<tr>
<td colspan="10">
<input type="submit" name="action" value="Activate">
<input type="submit" name="action" value="Suspend"> 
<input type="submit" name="action" value="Delete"  onClick="if (!confirmLink(this, 'Delete this Account, are you sure?')) { return false; }" >
<input type="hidden" name="offset" value="<?php echo jb_escape_html($_REQUEST['offset']);?>">

</td>
</tr>
  <tr bgColor="#eaeaea">
  <td><b><font face="Arial" size="2">Action</font></b></td>
    <td><b><font face="Arial" size="2"><?php echo $ListMarkup->get_select_all_checkbox('users');?></td>
    
	<?php
$admin = true;
JB_echo_list_head_data(4, $admin);
	?>
   
     
  </tr>

  <?php


	$i=0;
	
	while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i < $records_per_page)) {
		$EmployerForm->set_values($row);
		$i++;
  
  ?>
  <tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

  <td><?php 
	  if ($row['Validated'] == 0) { 
     ?>
        <!-- input style="font-size: 8pt" type="button" name="activate" value="Activate" onClick="window.location='<?php echo $_SERVER[PHP_SELF]; ?>?action=activate&user_id=<?php echo $row[ID]; ?>'" -->
        <input style="font-size: 8pt" type="button" name="delete" value="Delete" onClick="if (!confirmLink(this, 'Delete this Account, are you sure?')) {return;} window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?offset=<?php echo $_REQUEST['offset'];?>&action=delete&amp;user_id=<?php echo $row['ID']; ?>&show=<?php echo $_REQUEST['show'].$q_string; ?>'">
          
      <?php
  
	  
	  } else {
     ?>
     <FONT SIZE="2" COLOR="#66CC00"><b>Active</b></FONT><br>
        <!-- input style="font-size: 8pt" type="button" value="Suspend" onClick="window.location='<?php echo $_SERVER[PHP_SELF]; ?>?action=suspend&user_id=<?php echo $row[ID];?>'" ><br -->
           <input style="font-size: 8pt" type="button" value="Change PW" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?reset=<?php echo $row['ID']."&$q_string&offset=".$_REQUEST['offset'];?>'" >
		  
        
      <?php

	}

  ?>
   <input style="font-size: 8pt" type="button" value="Edit" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=edit&user_id=<?php echo $row['ID']."&$q_string&offset=".$_REQUEST['offset'];?>'" >

  <?php
   
	$row['Validated'];?></td>
     <td><input type="checkbox" name="users[]" value="<?php echo $row['ID']; ?>"></td>

	 <?php
	
	  JB_echo_employer_list_data($admin);

	?>
    
	
    
  </tr>
       <?php 
} ?>

</table>
</center>

<?php
if ($count > $records_per_page)  {
	$pages = ceil($count / $records_per_page);
	$cur_page = $_REQUEST['offset'] / $records_per_page;
	$cur_page++;

	
	?>
	
	<?php
	echo "Page $cur_page of $pages ";
	$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
	$LINKS = 10;
	echo "<center>";
	
	JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
	echo "</center>";
}

?>

<script type="text/javascript">
function set_validation_message () {

   var str 
      = "Hi %name%,\n\n"
+"Your <?php echo str_replace('"', '\\"', JB_SITE_NAME); ?> employer's account has been approved!\n\n"

+"You can now log in with the Member ID %username%\n\n"

+"Login Here: <?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER; ?>\n\n"

+"Cheers,\n"

+"<?php echo str_replace('"', '\\"', JB_SITE_NAME); ?> Team\n"; 

   document.form1.message.value=str;
   document.form1.subject.value="Your Employer's Account has been activated!";


}

function set_new_message () {

	 var str 
      = "Hi %name%,\n\n";

	document.form1.message.value=str;
   document.form1.subject.value='';


}

</script>
<h2>Send Email to Employers</h2>
Use the check-boxes to select the Employers that you want to Email.<br>
Canned Messages: (<input name="msg_type" type="radio" onClick="set_validation_message()">Validation Message)  (<input name="msg_type" onClick="set_new_message()" type="radio" value="NEWS"> New ) <br>
Subject: <input type="text" name="subject" size="60" value="<?php echo jb_escape_html($subject);?>"><br> 
<textarea name="message" rows="20" cols=50><?php echo stripslashes($message);?></textarea><br>
<input type="hidden" name="offset" value="<?php echo jb_escape_html($_REQUEST['offset']);?>">
<input type="hidden" name="q_string" value="<?php echo $q_string;?>">
<input type="submit" value="Send Email" name="action">
</form>


<?php

} else {
   echo "There are no accounts matching the specified query.";

}
JB_admin_footer();

?>

