<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
include ("../config.php");
require (dirname(__FILE__)."/admin_common.php");
require_once ("../include/resumes.inc.php");

JB_admin_header('Admin -> Reveal Resume');



?>

<h3>
Step 1 - Search For Employer
</h3>
<form style="margin: 0" action="<?php echo htmlentities($_SERVER['PHP_SELF'])?>?action=search" method="post">
  <input type="hidden" name="resume_id" value="<?php echo jb_escape_html($_REQUEST['resume_id']); ?>" >      
           <center>
         <table border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse"  id="AutoNumber2" width="100%">
    
    <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><font size="2" face="Arial"><b>Name</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
      <input type="text" name="q_name" size="39" value="<?php echo $q_name;?>" ></font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">Username</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_username" size="28" value="<?php echo $q_username; ?>"></td>
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
      
      <input type="text" name="q_email" size="28" value="<?php echo $q_email; ?>" ></td>
    </tr>
    <tr>
       <td align="top" colspan="2" bgcolor="#EDF8FC"><b>
       <font face="Arial" size="2">Signed</font></b><font size="2" face="Arial"><b> 
       Up After:</b></font>
<?php



?>
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
    <td colspan="1" bgcolor="#EDF8FC"><input type="text" size="28" name="q_company" value="<?php echo $q_company; ?>"></td>
    <tr>
      <td width="731" bgcolor="#EDF8FC" colspan="4">
      <font face="Arial"><b>
      <input type="submit" value="Search Employers" name="B1" style="float: left"><?php if ($action=='search') { ?>&nbsp; </b></font><b>[<font face="Arial"><a href="<?php echo htmlentities($_SERVER['PHP_SELF'])?>">Start a New Search</a></font>]</b><?php } ?></td>
    </tr>
    </table>

           </center>
         

</form>
<hr>
<h3>
Step 2 - Select Employer and grant access
</h3>

<?php

if ($_REQUEST['grant']!='') {

	$users = $_REQUEST['users'];
	foreach ($users as $user) {
		
		JB_grant_request ($_REQUEST['candidate_id'], $user);
		echo "<font color='#339900'><b>Granted access for Employer #$user</b></font>";


	}

}

$resume_id = $_REQUEST['resume_id'];
$q_aday = $_REQUEST['q_aday'];
$q_amon = $_REQUEST['q_amon'];
$q_ayear = $_REQUEST['q_ayear'];
$q_name = $_REQUEST['q_name'];
$q_username = $_REQUEST['q_username'];
$q_resumes = $_REQUEST['q_resumes'];
$q_news = $_REQUEST['q_news'];
$q_email = $_REQUEST['q_email'];
$q_company = $_REQUEST['q_company']; 

if ($show == "NA") {

	echo "<h3>Listing Non-Valid Employer Accounts</h3>";

	$where_sql = " AND Validated='0' ";


}

if ($q_name != '') {
	$list = preg_split ("/[\s,]+/", $q_name);
    for ($i=1; $i < sizeof($list); $i++) {
		$or1 .=" OR (`FirstName` like '%".jb_escape_sql($list[$i])."%')";
		$or2 .=" OR (`LastName` like '%".jb_escape_sql($list[$i])."%')";
    }
    $where_sql .= " AND (((`FirstName` like '%".jb_escape_sql($list[0])."%') $or1) OR ((`LastName` like '%".jb_escape_sql($list[0])."%') $or2))";
}

if ($q_username != '') {
	$q_username = trim($q_username);
	$list = preg_split ("/[\s,]+/", $q_username);
    for ($i=1; $i < sizeof($list); $i++) {
		$or .=" OR (`Username` like '%".jb_escape_sql($list[$i])."%')";
    }
    $where_sql .= " AND ((`Username` like '%".jb_escape_sql($list[0])."%') $or)";
}

if ($q_company != '') {
	$q_company = trim ($q_company);
	$list = preg_split ("/[\s,]+/", $q_company);
    for ($i=1; $i < sizeof($list); $i++) {
		$or .=" OR (`CompName` like '%".jb_escape_sql($list[$i])."%')";
    }
    $where_sql .= " AND ((`CompName` like '%".jb_escape_sql($list[0])."') $or)";
}

if ($q_email != '') {
	$q_email = trim ($q_email);
	$list = preg_split ("/[\s,]+/", $q_email);
    for ($i=1; $i < sizeof($list); $i++) {
		$or .=" OR (`Email` like '%".jb_escape_sql($list[$i])."%')";
		//$or2 .=" OR (`FirstName` like '%".$list[$i]."%')";
    }
    $where_sql .= " AND ((`Email` like '%".jb_escape_sql($list[0])."%') $or)";
	//$where_sql .= " AND ((`FirstName` like '%$list[0]%') $or2)";
}

if (($q_aday !='') && ($q_amon!='') && ($q_ayear!='')) {
	     $q_ayear = trim ($q_ayear);
         $q_date = "$q_ayear-$q_amon-$q_aday";
         $where_sql .= " AND  '$q_date' <= `SignupDate` ";
}

if ($q_news != '') {
	$where_sql .= " AND `Newsletter`='1' "; 

}

if ($q_resumes !='') {
	$where_sql .= " AND `Notification1`='1' ";

}

//if ($_REQUEST['action'] == 'search') {

	$q_string = "&action=search&q_name=$q_name&q_username=$q_username&q_news=$q_news&q_resumes=$q_resumes&q_email=$q_email&q_aday=$q_aday&q_amon=$q_amon&q_ayear=$q_ayear&q_company=$q_company&resume_id=$resume_id";
	
//}


if ($_REQUEST['order_by'] != '' ) {
	
	$order_by_sql = " ORDER BY (".jb_escape_sql($_REQUEST['order_by'])." ) desc";

} else {
	$order_by_sql = " ORDER BY  `SignupDate` DESC ";

}

$order_str = $order_by;



$sql = "select * FROM `employers` WHERE 1=1 $where_sql $order_by_sql ";

//echo $sql;

$result = JB_mysql_query($sql) or die (mysql_error());

$count = mysql_num_rows($result);

$records_per_page = 5;

if ($count > $records_per_page) {
	mysql_data_seek($result, $_REQUEST['offset']);
}


if ($count > 0 ) {

?>
<form style="margin: 0px;" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF'])."?".$q_string; ?>" name="form1" >
<input type="hidden" name="resume_id" value="<?php echo jb_escape_html($_REQUEST['resume_id']); ?>" >
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
$admin=true;
$RForm = &JB_get_DynamicFormObject(2);
$RForm->load($_REQUEST['resume_id']);

$name = $RForm->get_template_value ('RESUME_NAME', $admin);


$ListMarkup = &JB_get_ListMarkupObject('JBRequestListMarkup');

?>

<p>



<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >
<tr>
<td colspan="10"><input type="submit" name="grant" value="Grant Access to view <?php echo jb_escape_html($name); ?>'s resume"> <input type="hidden" name="offset" value="<?php echo jb_escape_html($_REQUEST['offset']);?>">
<input type="hidden" name="candidate_id" value='<?php echo jb_escape_html($data['user_id']); ?>' >
</td>
</tr>
  <tr bgColor="#eaeaea">
 
<td><b><font face="Arial" size="2"><?php echo $ListMarkup->get_select_all_checkbox('users');?></td>
    <td><b><font face="Arial" size="2"><a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?order_by=LastName&amp;offset=".$offset.$q_string; ?>">Name</a></font></b></td>
    <td><b><font face="Arial" size="2"><a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?order_by=Username&amp;offset=".$offset.$q_string; ?>">Username</a></font></b></td>
    <td><b><font face="Arial" size="2"><a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?order_by=Email&amp;offset=".$offset.$q_string; ?>">Email</a></font></b></td>
	<td><b><font face="Arial" size="2"><a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?order_by=CompName&amp;offset=".$offset.$q_string; ?>">Company</a></font></b></td>
	<td><b><font face="Arial" size="2">Posts</font></b></td>
    <td><b><font face="Arial" size="2"><a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?order_by=Newsletter&amp;offset=".$offset.$q_string; ?>">News</a></font></b></td>
    <td><b><font face="Arial" size="2"><a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?order_by=Notification1&amp;offset=".$offset.$q_string; ?>">C.V. Alerts</a></font></b></td>
	<td><b><font face="Arial" size="2"><a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?order_by=SignupDate&amp;offset=".$offset.$q_string; ?>">Signup Date</a></font></b></td>
	<td><b><font face="Arial" size="2">I.P</font></b></td>
     
  </tr>

  <?php
$resume_id = $_REQUEST['resume_id'];
$q_aday = $_REQUEST['q_aday'];
$q_amon = $_REQUEST['q_amon'];
$q_ayear = $_REQUEST['q_ayear'];
$q_name = $_REQUEST['q_name'];
$q_username = $_REQUEST['q_username'];
$q_resumes = $_REQUEST['q_resumes'];
$q_news = $_REQUEST['q_news'];
$q_email = $_REQUEST['q_email'];
$q_company = $_REQUEST['q_company']; 
$q_string = "&q_name=$q_name&q_username=$q_username&q_news=$q_news&q_resumes=$q_resumes&q_email=$q_email&q_aday=$q_aday&q_amon=$q_amon&q_ayear=$q_ayear&q_company=$q_company&resume_id=$resume_id";


  $i=0;
  while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i < $records_per_page)) {
	  
	  $i++;
  
  ?>
  <tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

     <td><input type="checkbox" name="users[]" value="<?php echo $row['ID']; ?>"></td>
    

    <td><font face="Arial" size="2"><?php echo jb_escape_html(jb_get_formatted_name($row['FirstName'], $row['LastName'])); ?></font></td>
    <td><font face="Arial" size="2"><?php echo $row['Username'];?></font></td>
    <td><font face="Arial" size="2"><?php echo $row['Email'];?></font></td>
	<td><font face="Arial" size="2"><?php echo $row['CompName'];?></font></td>
	<td><?php 
		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "SELECT * FROM `posts_table` WHERE `user_id`='".jb_escape_sql($row['ID'])."' AND expired='N' ";
		$result2 = JB_mysql_query($sql);
		$count = mysql_num_rows($result2);
		if ($count > 0) {
			echo "<a href='posts.php?show_emp=".$row['ID']."'>".$count."</a>";
		} else {
			echo "N";
		}
	
	//echo $row[Notification2];?>
	</td>
    <td><font face="Arial" size="2"><?php echo $row['Newsletter'];?>
    </font></td>
    <td><?php echo $row[Notification1];?></td>
    	<td> <font face="Arial" size="2"><?php echo $row['SignupDate'];?></font></td>
	<td><font face="Arial" size="2"><?php echo $row['IP'];?></font></td>
    
  </tr>
       <?php } ?>

</table>
<?php
	
} 
?>
<hr>
<h3>
Step 3 - Close
</h3>

<center><input type="button" name="" value="Close" onclick="window.opener.location.reload();window.close()"></center>

<?php

JB_Admin_footer();

?>