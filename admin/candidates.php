<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require (dirname(__FILE__)."/admin_common.php");
require_once JB_basedirpath()."include/resumes.inc.php";


JB_admin_header('Admin -> Language');

$ListMarkup = &JB_get_ListMarkupObject('JBCanListMarkup');
$CandidateForm = &JB_get_DynamicFormObject(5);
?>
<b>[CANDIDATES]</b> <span style="background-color: <?php if ($_REQUEST['show']!='') { echo '#F2F2F2'; } else { echo "#FFFFCC"; } ?>; border-style:outset; padding:5px; "><a href="candidates.php">List Candidates</a></span>
<span style="background-color: <?php if ($_REQUEST['show']=='NA') { echo '#FFFFCC'; } else { echo "#F2F2F2"; } ?>; border-style:outset; padding: 5px;"><a href="candidates.php?show=NA">Non-Validated Candidates</a></span>
	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="resumes.php?show=ALL">View Resumes</a></span>
	<?php
if (JB_RESUMES_NEED_APPROVAL=='YES') {
?>
<span style="background-color: <?php  echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="resumes.php?show=WA">New Resumes Waiting</a></span>
<?php
	
}
?>
	<hr>
<?php


if ($_REQUEST['action'] == 'Activate') {

	for ($i=0; $i < sizeof($_REQUEST['users']); $i++) {

		$sql = "UPDATE `users` SET `validated`='1' WHERE `ID`='".jb_escape_sql($_REQUEST['users'][$i])."'";
		JB_mysql_query($sql);

		//activate the resume
		$sql = "UPDATE `resumes_table` SET `status`='ACT' WHERE `user_id`='".jb_escape_sql($_REQUEST['users'][$i])."'";
	    JB_mysql_query($sql);
    
		$sql = "SELECT * from `users` WHERE `ID`='".jb_escape_sql($_REQUEST['users'][$i])."'";
		$result = JB_mysql_query($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		$JBMarkup->ok_msg('Activated username: <b>'.jb_escape_html($row['Username']).'</b> ('.jb_escape_html(JB_get_formatted_name($row['FirstName'], $row['LastName'])).')');

	}

	
    ?>
   <p>
   <a href="candidates.php">Candidate Accounts</a>

   <?php
   
}

if ($_REQUEST['action'] == 'Suspend') {

	for ($i=0; $i < sizeof($_REQUEST['users']); $i++) {
	   
		$sql = "UPDATE `users` SET `Validated`='0' WHERE `ID`='".jb_escape_sql($_REQUEST['users'][$i])."'";
		JB_mysql_query($sql);
		//suspend the resume
		$sql = "UPDATE `resumes_table` SET `status`='SUS' WHERE `resume_id`='".jb_escape_sql($_REQUEST['users'][$i])."'";
		JB_mysql_query($sql);

		$sql = "SELECT * from `users` WHERE `ID`='".jb_escape_sql($_REQUEST['users'][$i])."'";
		$result = JB_mysql_query($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		$JBMarkup->ok_msg("Suspended Username: <b>".jb_escape_html($row['Username'])."</b> (".JB_get_formatted_name($row['FirstName'], $row['LastName']).')');
		
	   

	  
	}

	?>
	<p>
	Go to: <a href="candidates.php">Candidate Accounts</a>

	<?php

}




if (strtolower($_REQUEST['action']) == 'delete') {

	if (sizeof($_REQUEST['users'])>0) {

		for ($i=0; $i < sizeof($_REQUEST['users']); $i++) {
			JB_delete_candidate($_REQUEST['users'][$i]);
			$JBMarkup->ok_msg("Deleted #".$_REQUEST['users'][$i]);
		}

	} else {
		JB_delete_candidate($_REQUEST['user_id']);
		
		$JBMarkup->ok_msg("Deleted #".$_REQUEST['user_id']);

	}


}

if ($_REQUEST['action'] == 'Send Email') {

  
   
   if (!is_array($_REQUEST['users'])) {
		$JBMarkup->error_msg('ERROR! No Users Selected. Use the check-boxes to select the users that you want to email.');

   } else {

		 echo "sending email...<br>";

		for ($i=0; $i < sizeof($_REQUEST['users']); $i++) {


			$sql = "SELECT * from `users` WHERE `ID`='".jb_escape_sql($_REQUEST['users'][$i])."'";
			$result = JB_mysql_query($sql) or die (mysql_error());
			$row = mysql_fetch_array($result, MYSQL_ASSOC);

			$msg = str_replace ( "%name%", JB_get_formatted_name($row['FirstName'], $row['LastName']), stripslashes($_REQUEST['message']));
			$msg = str_replace ( "%username%", $row['Username'], $msg);
			$msg = str_replace ( "%email%", $row['Email'], $msg);


			$to = $row['Email'];
			$from = JB_SITE_CONTACT_EMAIL; // Enter your email adress here

			$email_id=JB_queue_mail($to, JB_get_formatted_name($row['FirstName'], $row['LastName']), JB_SITE_CONTACT_EMAIL, JB_SITE_NAME, stripslashes($_REQUEST['subject']), $msg, '', 9);
			$JBMarkup->ok_msg("Email queued to:". JB_escape_html($row['Email'])) ;
			JB_process_mail_queue(1, $email_id);

		}

   }

}


if ($_REQUEST['action'] == 'edit' ) {


	if ($_REQUEST['form'] != "" ) { // saving
		$employer_id = $CandidateForm->save($admin=true); //JB_insert_candidate_data(); 
		$JBMarkup->ok_msg('Candidate data updated.');
	} else {
		$CandidateForm->load($_REQUEST['user_id']);
		$CandidateForm->display_form('edit', true);
	}

}

if ($_REQUEST['action'] == 'reset' ) {
   
	if ($_REQUEST['pass'] != '') {
		$_REQUEST['pass'] = md5($_REQUEST['pass']);
		$sql = "UPDATE `users` SET `Password`='".jb_escape_sql($_REQUEST['pass'])."' WHERE `ID`='".jb_escape_sql($_REQUEST['user_id'])."' LIMIT 1";
		JB_mysql_query($sql) or die(mysql_error());
		$JBMarkup->ok_msg('Password Changed.');
		JBPLUG_do_callback('can_new_pass', $_REQUEST['pass'], $row['Username']);
	} else {

		$sql = "SELECT * from `users` WHERE `ID`='".jb_escape_sql($_REQUEST['user_id'])."'";
		$result = JB_mysql_query($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		?>
		Change the password for username: 
		<?php 
		echo JB_escape_html($row['Username'])." (".JB_get_formatted_name($row['FirstName'], $row['LastName']).")";
		 
		$q_string = JB_generate_candidate_q_string(); 

	     ?>

         <form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=reset">
            <input type="hidden" name="user_id" value="<?php echo htmlentities($_REQUEST['user_id']);?>">
			<input type="hidden" name="show" value="<?php echo htmlentities($_REQUEST['show']); ?>">
            New Password:<input type="text" name="pass"><br>
            <input type="submit" value="OK">  <input type="button" value="Cancel" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF'])."?offset=".htmlentities($_REQUEST['offset'])."&amp;".jb_escape_html($q_string);?>'">
         </form>

		 </body>
		 </html>


      <?php

		  die();




   }

}


JBPLUG_do_callback('candidate_admin_top', $A=false);

if (($_REQUEST['action'] == '') || ($_REQUEST['action']=='search')) {


		$q_string = JB_generate_candidate_q_string(); 
?>

<form style="margin: 0" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post">
<input type="hidden" name="action" value="search">
<input type="hidden" name="show" value="<?php echo jb_escape_html($_REQUEST['show']); ?>">
<input type="hidden" name="order_by" value="<?php echo jb_escape_html($_REQUEST['order_by']); ?>">
<input type="hidden" name="ord" value="<?php echo jb_escape_html($_REQUEST['ord']); ?>">
         
           <center>
         <table border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse"  id="AutoNumber2"  width="100%">
  
    <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><font size="2" face="Arial"><b>Name</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
      <input type="text" name="q_name" size="39" value="<?php echo jb_escape_html(stripslashes($_REQUEST['q_name']));?>" ></font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">Username</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_username" size="28" value="<?php echo jb_escape_html(stripslashes($_REQUEST['q_username'])); ?>"></td>
    </tr>
    <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">Alerts</font></b></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
	 
      &nbsp;<input type="checkbox" name="q_news" <?php if ($_REQUEST['q_news']!='') { echo " checked "; } ?> value="ON" >> <font size="2" face="arial"> &nbsp;</FONT></font><font face="arial" size="2">Newsletter</font><font face="Arial">
      <input type="checkbox" name="q_alerts" <?php if ($_REQUEST['q_alerts']!='') { echo " checked "; } ?> value="ON"> >
        <font size="2" face="arial"> &nbsp;</font></font><font face="arial" size="2">Job Alerts</font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">Email</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_email" size="28" value="<?php echo jb_escape_html(stripslashes($_REQUEST['q_email'])); ?>" ></td>
    </tr>
    <tr>
       <td align="top" colspan="2" bgcolor="#EDF8FC"><b>
       <font face="Arial" size="2">Signed</font></b><font size="2" face="Arial"><b> 
       Up After:</b></font>
<?php



?>
       <select name="q_aday">
		<option></option>
		<option <?php if ($_REQUEST['q_aday']=='01') { echo ' selected ';} ?> >1</option>
		<option <?php if ($_REQUEST['q_aday']=='02') { echo ' selected ';} ?> >2</option>
		<option <?php if ($_REQUEST['q_aday']=='03') { echo ' selected ';} ?> >3</option>
		<option <?php if ($_REQUEST['q_aday']=='04') { echo ' selected ';} ?> >4</option>
		<option <?php if ($_REQUEST['q_aday']=='05') { echo ' selected ';} ?> >5</option>
		<option <?php if ($_REQUEST['q_aday']=='06') { echo ' selected ';} ?> >6</option>
		<option <?php if ($_REQUEST['q_aday']=='07') { echo ' selected ';} ?>>7</option>
		<option <?php if ($_REQUEST['q_aday']=='08') { echo ' selected ';} ?>>8</option>
		<option <?php if ($_REQUEST['q_aday']=='09') { echo ' selected ';} ?> >9</option>
		<option <?php if ($_REQUEST['q_aday']=='10') { echo ' selected ';} ?> >10</option>
		<option <?php if ($_REQUEST['q_aday']=='11') { echo ' selected ';} ?> > 11</option>
		<option <?php if ($_REQUEST['q_aday']=='12') { echo ' selected ';} ?> >12</option>
		<option <?php if ($_REQUEST['q_aday']=='13') { echo ' selected ';} ?> >13</option>
		<option <?php if ($_REQUEST['q_aday']=='14') { echo ' selected ';} ?> >14</option>
		<option <?php if ($_REQUEST['q_aday']=='15') { echo ' selected ';} ?> >15</option>
		<option <?php if ($_REQUEST['q_aday']=='16') { echo ' selected ';} ?> >16</option>
		<option <?php if ($_REQUEST['q_aday']=='17') { echo ' selected ';} ?> >17</option>
		<option <?php if ($_REQUEST['q_aday']=='18') { echo ' selected ';} ?> >18</option>
		<option <?php if ($_REQUEST['q_aday']=='19') { echo ' selected ';} ?> >19</option>
		<option <?php if ($_REQUEST['q_aday']=='20') { echo ' selected ';} ?> >20</option>
		<option <?php if ($_REQUEST['q_aday']=='21') { echo ' selected ';} ?> >21</option>
		<option <?php if ($_REQUEST['q_aday']=='22') { echo ' selected ';} ?> >22</option>
		<option <?php if ($_REQUEST['q_aday']=='23') { echo ' selected ';} ?> >23</option>
		<option <?php if ($_REQUEST['q_aday']=='24') { echo ' selected ';} ?> >24</option>
		<option <?php if ($_REQUEST['q_aday']=='25') { echo ' selected ';} ?> >25</option>
		<option <?php if ($_REQUEST['q_aday']=='26') { echo ' selected ';} ?> >26</option>
		<option <?php if ($_REQUEST['q_aday']=='27') { echo ' selected ';} ?> >27</option>
		<option <?php if ($_REQUEST['q_aday']=='28') { echo ' selected ';} ?> >28</option>
		<option <?php if ($_REQUEST['q_aday']=='29') { echo ' selected ';} ?> >29</option>
		<option <?php if ($_REQUEST['q_aday']=='30') { echo ' selected ';} ?> >30</option>
		<option <?php if ($_REQUEST['q_aday']=='31') { echo ' selected ';} ?> >31</option>
	  </select>
	  <select name="q_amon" >
	   <option ></option>
		<option <?php if ($_REQUEST['q_amon']=='01') { echo ' selected ';} ?> value="1">Jan</option>
		<option <?php if ($_REQUEST['q_amon']=='02') { echo ' selected ';} ?> value="2">Feb</option>
		<option <?php if ($_REQUEST['q_amon']=='03') { echo ' selected ';} ?> value="3">Mar</option>
		<option <?php if ($_REQUEST['q_amon']=='04') { echo ' selected ';} ?> value="4">Apr</option>
		<option <?php if ($_REQUEST['q_amon']=='05') { echo ' selected ';} ?> value="5">May</option>
		<option <?php if ($_REQUEST['q_amon']=='06') { echo ' selected ';} ?> value="6">Jun</option>
		<option <?php if ($_REQUEST['q_amon']=='07') { echo ' selected ';} ?> value="7">Jul</option>
		<option <?php if ($_REQUEST['q_amon']=='08') { echo ' selected ';} ?> value="8">Aug</option>
		<option <?php if ($_REQUEST['q_amon']=='09') { echo ' selected ';} ?> value="9">Sep</option>
		<option <?php if ($_REQUEST['q_amon']=='10') { echo ' selected ';} ?> value="10">Oct</option>
		<option <?php if ($_REQUEST['q_amon']=='11') { echo ' selected ';} ?> value="11">Nov</option>
		<option <?php if ($_REQUEST['q_amon']=='12') { echo ' selected ';} ?> value="12">Dec</option>
	  </select>
	  <input type="text"  name="q_ayear" size="4"  value="<?php echo $_REQUEST['q_ayear']; ?>" >
	
	  </td>

    </td>
    <td colspan="1" bgcolor="#EDF8FC">&nbsp;</td>
    <td colspan="1" bgcolor="#EDF8FC">&nbsp;</td>
    <tr>
      <td width="731" bgcolor="#EDF8FC" colspan="4">
      <font face="Arial"><b>
      <input type="submit" value="Search Candidates" name="B1" style="float: left"><?php if ($_REQUEST['action']=='search') { ?>&nbsp; </b></font><b>[<font face="Arial"><a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">Start a New Search</a></font>]</b><?php } ?></td>
    </tr>
    </table>

           </center>
         

</form>
<div style="float: right;">
<font size="2"><a href="get_csv.php?table=users">Download CSV</a> | <font size="2"><a href="candidatelist.php">Edit List</a></font>
</div>
<?php


}

if (($_REQUEST['show'] == "NA"))  {

	echo "<h3>Listing Non-Valid Candidate Accounts</h3>";

	$where_sql = " AND Validated='0' ";


} else {

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
         $q_date = jb_escape_sql($_REQUEST['q_ayear'])."-".jb_escape_sql($_REQUEST['q_amon'])."-".jb_escape_sql($_REQUEST['q_aday']);
         $where_sql .= " AND  '".$q_date."' <= `SignupDate` ";
}

if ($_REQUEST['q_news'] != '') {
	$where_sql .= " AND `Newsletter`='1' "; 

}

if ($_REQUEST['q_alerts'] !='') {
	$where_sql .= " AND `Notification1`='1' ";

}

if ($_REQUEST['ord']=='asc') {
	$ord = 'ASC';
} elseif ($_REQUEST['ord']=='desc') {
	$ord = 'DESC';
} else {
	$ord = 'DESC'; // sort descending by default
}

if (($_REQUEST['order_by'] == '') || (!JB_is_field_valid($_REQUEST['order_by'], 5))) {
	// by default, order by the post_date
	$order = " `SignupDate` ";           
} else {
	$order = " `".jb_escape_sql($_REQUEST['order_by'])."` ";
}


$_REQUEST['offset'] = (int) $_REQUEST['offset'];
if ($_REQUEST['offset']<0) {
	$_REQUEST['offset'] = abs($_REQUEST['offset']);
}
$records_per_page = 20;

$sql = "select SQL_CALC_FOUND_ROWS * FROM `users` WHERE 1=1 ".$where_sql." ORDER BY $order ".jb_escape_sql($ord)."  LIMIT ".jb_escape_sql($_REQUEST['offset']).", ".jb_escape_html($records_per_page)."";

$result = JB_mysql_query($sql) or die (mysql_error());

$row = mysql_fetch_row(jb_mysql_query("SELECT FOUND_ROWS()"));
$count = $row[0];

if ($_REQUEST['action'] == 'search') {
	$q_string = JB_generate_candidate_q_string();
}




if ($count > 0 ) {

	// calculate number of pages & current page
	$pages = ceil($count / $records_per_page);
	$cur_page = $_REQUEST['offset'] / $records_per_page;
	$cur_page++;

?>

<center><b><?php echo mysql_num_rows($result); ?> Candidate's Accounts Returned (<?php echo $pages;?> pages) </b></center>
<?php
	if ($count > $records_per_page)  {
		echo "<center>";
		$label["navigation_page"] =  str_replace ("%CUR_PAGE%", $cur_page, $label["navigation_page"]);
		$label["navigation_page"] =  str_replace ("%PAGES%", $pages, $label["navigation_page"]);
		echo "<span > ".$label["navigation_page"]."</span> ";
		$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
		$LINKS = 10;
		JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
		echo "</center>";
	}
?>
<p>
<form style="margin: 0px;" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); echo "?offset=".jb_escape_html($_REQUEST['offset']).jb_escape_html($q_string); ?>" name="form1" >
<input type="hidden" name="show" value="<?php echo jb_escape_html($_REQUEST['show']); ?>">
<input type="hidden" name="order_by" value="<?php echo jb_escape_html($_REQUEST['order_by']); ?>">
<input type="hidden" name="ord" value="<?php echo jb_escape_html($_REQUEST['ord']); ?>">

<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:99%; border:0px">
<tr>

<td colspan="8">
<input type="Submit" name="action" value="Activate"> 
<input type="submit" name="action" value="Suspend"> 
<input type="submit" name="action" value="Delete"  onClick="if (!confirmLink(this, 'Delete this Account, are you sure?')) { return false; }" >


</td></tr>

  <tr bgColor="#eaeaea">
     
	 <td><b><font face="Arial" size="2">Action</font></b></td>
	 <td><b><font face="Arial" size="2"><?php echo $ListMarkup->get_select_all_checkbox('users');?></td>

	<?php

	
	$admin = true;
	JB_echo_list_head_data(5, $admin);
	?>
		
  </tr>

  <?php


	$q_string = JB_generate_candidate_q_string();
  
  $i=0;
 
  while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i<$records_per_page)) {
	  $CandidateForm->set_values($row);
	  $i++;
	  ?>
		<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

		<td><?php if ($row['Validated'] == 0) { 
		?>
         
           <!-- input type="button" value="Activate" name="activated" onClick="window.location='<?php echo $_SERVER[PHP_SELF]; ?>?action=activate&user_id=<?php echo $row[ID];?>'" -->
                         
           <input type="button" onClick="if (!confirmLink(this, 'Delete this Account, are you sure?')) {return;} window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=delete&amp;user_id=<?php echo $row['ID'];?>' " value="Delete" name="activated">
           
      <?php
  
  } else {
     ?>
     <FONT SIZE="" COLOR="#66CC00"><b>Active</b></FONT>
 
		   <input style="font-size: 8pt" type="button" value="Change PW" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=reset&amp;user_id=<?php echo $row['ID']."&amp;offset=".jb_escape_html($_REQUEST['offset']);?>'" >
		   <input style="font-size: 8pt" type="button" value="Edit" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=edit&amp;user_id=<?php echo $row['ID']."&amp;offset=".jb_escape_html($_REQUEST['offset']);?>'" >
      
      <?php
  }
  
  ?>
    
  </td>
   <td><input type="checkbox" name="users[]" value="<?php echo $row['ID']; ?>"></td>

   <?php

	   JB_echo_candidate_list_data($admin);

   ?>

  
 
  </tr>
       <?php }
    
  ?>
</table>
</center>
<?php

if ($count > $records_per_page)  {
	$pages = ceil($count / $records_per_page);
	$cur_page = $_REQUEST['offset'] / $records_per_page;
	$cur_page++;

	echo "<center>";
	?>
	
	<?php
	echo "Page $cur_page of $pages - ";
	$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
	$LINKS = 10;
	JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
	echo "</center>";

}
?>
<h2>Send Email to Candidates</h2>
Use the check-boxes to select the Candidates that you want to Email.<br>

Subject: <input type="text" name="subject" size="60" value="<?php echo $_REQUEST['subject'];?>"><br> 
<textarea name="message" rows="20" cols=50><?php echo stripslashes($_REQUEST['message']);?></textarea><br>
<input type="submit" value="Send Email" name="action">
</form>

<?php

} else {
   echo "There are no accounts matching the specified query.";

}


JB_admin_footer();

?>


