<?php 

###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require ('../config.php');
require (dirname(__FILE__)."/admin_common.php");
require_once ('../include/category.inc.php');

JB_admin_header('Email Config');

?>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; "></div>
<b>[Email Templates]</b>
<!--
<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="edit_config.php">Main</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcats.php">Categories</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcodes.php">Codes</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="language.php">Languages</a></span>
 <span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="emailconfig.php">Email Templates</a></span>	
-->

<hr>
<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
  <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Sign up</b></td>
    <td <?php if ($_REQUEST['EmailID']==1) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=1">Candidate Signup</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==2) { echo " bgcolor='#FFFFCC' ";} ?> ><span ><a href="emailconfig.php?EmailID=2">Employer Signup</a></span></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    
  </tr>
  <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Misc</b></td>
    <td <?php if ($_REQUEST['EmailID']==3) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=3">Forgot Password</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==4) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=4">Request Candidate's Details</a></span></td>
	<td <?php if ($_REQUEST['EmailID']==44) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=44">Request Granted</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==11) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=11">Employer to Candidate</a></span></td>
    
    
  </tr>
  <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Alerts</b></td>
    <td <?php if ($_REQUEST['EmailID']==5) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=5">Resume Alert (text)</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==6) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=6">Resume Alert (html)</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==7) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=7">Job Alert (text)</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==8) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=8">Job Alert (html)</a></span></td>
   
  </tr>
  <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Applications</b></td>
    <td <?php if ($_REQUEST['EmailID']==12) { echo " bgcolor='#FFFFCC' ";} ?>><a href="emailconfig.php?EmailID=12">Job Application</a></td>
    <td <?php if ($_REQUEST['EmailID']==10) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=10">Job Application receipt</a></span></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    
  </tr>
   <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Posting Order Confirmed</b></td>
    <td <?php if ($_REQUEST['EmailID']==60) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=60">P. Confirmed - Bank</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==61) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=61">P. Confirmed - Check</a></span></td>
     <td <?php if ($_REQUEST['EmailID']==62) { echo " bgcolor='#FFFFCC' ";} ?>>
	 </td>
     <td>
	 </td>
    
  </tr>
   <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Posting Order Completed</b></td>
    <td <?php if ($_REQUEST['EmailID']==70) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=70">P. Completed </a></span></td>
    <td <?php if ($_REQUEST['EmailID']==71) { echo " bgcolor='#FFFFCC' ";} ?>><!--<span ><a href="emailconfig.php?EmailID=71">P. Completed - Check</a></span>--></td>
     <td <?php if ($_REQUEST['EmailID']==72) { echo " bgcolor='#FFFFCC' ";} ?>>
	 <!--
	 <span ><a href="emailconfig.php?EmailID=72">P. Completed - Instant</a></span>
	 -->
	 </td>
     <td >
	 <!--
	 <span><a href="emailconfig.php?EmailID=63">Completed</a></span>
	 -->
	 </td>
    
  </tr>
   <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Subscription Order Confirmed</b></td>
    <td <?php if ($_REQUEST['EmailID']==80) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=80">Sub. Confirmed - Bank</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==81) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=81">Sub. Confirmed - Check</a></span></td>
     <td <?php if ($_REQUEST['EmailID']==82) { echo " bgcolor='#FFFFCC' ";} ?>>
	 <!--<span ><a href="emailconfig.php?EmailID=82">Sub. Confirmed - Instant</a></span>
	 -->
	 </td>
     <td >
	 <!--
	 <span><a href="emailconfig.php?EmailID=63">Completed</a></span>
	 -->
	 </td>
    
  </tr>

   <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Subscription Order Completed</b></td>
    <td <?php if ($_REQUEST['EmailID']==90) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=90">Sub. Completed</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==91) { echo " bgcolor='#FFFFCC' ";} ?>><!--<span ><a href="emailconfig.php?EmailID=91">Sub. Completed - Check</a></span>--></td>
     <td <?php if ($_REQUEST['EmailID']==92) { echo " bgcolor='#FFFFCC' ";} ?>><!--
	 <span ><a href="emailconfig.php?EmailID=92">Sub. Completed - Instant</a></span>--></td>
     <td></td>
    
  </tr>
   <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Subscription Expired</b></td>
    <td <?php if ($_REQUEST['EmailID']==130) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=130">Sub. Expired</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==91) { echo " bgcolor='#FFFFCC' ";} ?>><!--<span ><a href="emailconfig.php?EmailID=91">Sub. Renewed</a></span>--></td>
     <td <?php if ($_REQUEST['EmailID']==92) { echo " bgcolor='#FFFFCC' ";} ?>><!--
	 <span ><a href="emailconfig.php?EmailID=92">Sub. Completed - Instant</a></span>--></td>
     <td></td>
  </tr>

   <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Membership Order Confirmed</b></td>
    <td <?php if ($_REQUEST['EmailID']==100) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=100">M. Confirmed - Bank</a></span></td>
     <td <?php if ($_REQUEST['EmailID']==101) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=101">M. Confirmed - Check</a></span></td>
     <td <?php if ($_REQUEST['EmailID']==102) { echo " bgcolor='#FFFFCC' ";} ?>>
	 <!--<span ><a href="emailconfig.php?EmailID=102">M. Confirmed - Instant</a></span>--></td>
     <td></td>
    
  </tr>

   <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Membership Order Completed</b></td>
    <td <?php if ($_REQUEST['EmailID']==110) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=110">M. Completed</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==111) { echo " bgcolor='#FFFFCC' ";} ?>><!--<span ><a href="emailconfig.php?EmailID=111">M. Completed - Check</a></span>--></td>
     <td <?php if ($_REQUEST['EmailID']==112) { echo " bgcolor='#FFFFCC' ";} ?>>
	 <!--<span><a href="emailconfig.php?EmailID=112">M. Completed - Instant</a></span>--></td>
     <td></td>
    
  </tr>

  <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Membership Expired</b></td>
    <td <?php if ($_REQUEST['EmailID']==120) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=120">M. Expired</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==91) { echo " bgcolor='#FFFFCC' ";} ?>><!--<span ><a href="emailconfig.php?EmailID=91">Sub. Renewed</a></span>--></td>
     <td <?php if ($_REQUEST['EmailID']==92) { echo " bgcolor='#FFFFCC' ";} ?>><!--
	 <span ><a href="emailconfig.php?EmailID=92">Sub. Completed - Instant</a></span>--></td>
     <td></td>
  </tr>

  <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>Job Postings</b></td>
    <td <?php if ($_REQUEST['EmailID']==210) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=210">Job Expired</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==220) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=220">Job Approved</a></span></td>
     <td <?php if ($_REQUEST['EmailID']==230) { echo " bgcolor='#FFFFCC' ";} ?>>
	 <span ><a href="emailconfig.php?EmailID=230">Job Disapproved</a></span></td>
     <td ></td>
  </tr>
  <tr bgcolor='#ffffff'>
    <td bgColor="#eaeaea"><b>To Admin</b></td>
    <td <?php if ($_REQUEST['EmailID']==310) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=310">New Post Alert</a></span></td>
    <td <?php if ($_REQUEST['EmailID']==320) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=320">New/Edit Resume Alert</a></span></td>
     <td <?php if ($_REQUEST['EmailID']==330) { echo " bgcolor='#FFFFCC' ";} ?>><span ><a href="emailconfig.php?EmailID=330">New Order Placed</a></span></td>
     <td ></td>
  </tr>

</table>

	<hr>

<?php
##################################

/*

if ($_REQUEST[notify_customer]=='YES') {

		#load template
		$sql = "SELECT * FROM EmailTemplates WHERE EmailID='1' ";
		$result2 = mssql_query ($sql) or die();
		$row2 = mssql_fetch_array($result2);

		$msg = str_replace("%FNAME%",$userrow[FName],$row2[EmailText]);
		$msg = str_replace("%LNAME%",$userrow[LName],$msg);
		$msg = str_replace("%ORDER%",generate_email_for_order ("", $order_id),$msg);
		$msg = str_replace("%ORDERID%", $order_id,$msg);
		$msg = str_replace("%NOTE%",$_REQUEST[note_to_user],$msg);
		$msg = str_replace("%STATUS%",$orderrow[Status],$msg);
		

		$to_email = $userrow[Email];

		## SEND TO USer
		mail($to_email, $row[EmailSubject], $msg, "From: ".$row[EmailFromAddress]."\r\n" .
		"Reply-To: ".$row[EmailFromAddress]."\r\n" ."X-Mailer: PHP/" . phpversion());

	}

*/


global $ACT_LANG_FILES;
	echo "Current Language: [".$_SESSION["LANG"]."] Select language:";

?>
<form name="lang_form">
<input type="hidden" name="EmailID" value="<?php echo jb_escape_html($_REQUEST['EmailID']); ?>">
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

<hr>
<?php

if ($_REQUEST['set_global']!='') {

	if (JB_validate_mail($_REQUEST['from_addr'])) {

		$sql = "UPDATE email_templates SET  EmailFromAddress='".jb_escape_sql($_REQUEST['from_addr'])."', EmailFromName='".jb_escape_sql($_REQUEST['from_name'])."' ";
		
		$result = JB_mysql_query ($sql) or die (mysql_error());

		$sql = "UPDATE email_template_translations SET  EmailFromAddress='".jb_escape_sql($_REQUEST['from_addr'])."', EmailFromName='".jb_escape_sql($_REQUEST['from_name'])."' where lang='".jb_escape_sql($_SESSION["LANG"])."' ";
		
		$result = JB_mysql_query ($sql) or die (mysql_error());

		$JBMarkup->ok_msg('Result: Changes applied to all templates.');
	} else {
		$JBMarkup->error_msg('Error: From Email address is invalid');
	}


}

if ($_REQUEST['EmailID']==false) {

	if ($_REQUEST['from_addr']==false) {
		$_REQUEST['from_addr'] = JB_SITE_CONTACT_EMAIL;
	}
	if ($_REQUEST['from_name']==false) {
		$_REQUEST['from_name'] = JB_SITE_NAME;
	}
	
?>
<form method="post" action="emailconfig.php">
<b>Global Settings</b><br>
<table>
<tr><td>
From Email Address:</td><td> <input type="text" name="from_addr" value="<?php echo jb_escape_html($_REQUEST['from_addr']);?>"></td></tr>
<tr><td>
From Name:</td><td> <input type="text" name="from_name" value="<?php echo jb_escape_html($_REQUEST['from_name']);?>"></td></tr>
<tr><td colspan="2">
<input type="submit" value="Apply to All" name="set_global"></td></tr>
</table>
</form>

<hr>

<?php

}

if ($_REQUEST['submit']!='') {

	if (JB_validate_mail($_REQUEST['EmailFromAddress'])) {

		$sql = "UPDATE email_templates SET EmailText='".jb_escape_sql($_REQUEST['EmailText'])."', EmailFromAddress='".jb_escape_sql($_REQUEST['EmailFromAddress'])."', EmailFromName='".jb_escape_sql($_REQUEST['EmailFromName'])."', EmailSubject='".jb_escape_sql($_REQUEST['EmailSubject'])."', `sub_template`='".jb_escape_sql($_REQUEST['sub_template'])."' WHERE EmailID='".jb_escape_sql($_REQUEST['EmailID'])."' ";

		$result = JB_mysql_query ($sql) or die (mysql_error());

		$sql = "REPLACE INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailFromName`, EmailSubject, `sub_template`) VALUES (".jb_escape_sql($_REQUEST['EmailID']).", '".jb_escape_sql($_SESSION["LANG"])."', '".jb_escape_sql($_REQUEST['EmailText'])."', '".jb_escape_sql($_REQUEST['EmailFromAddress'])."', '".jb_escape_sql($_REQUEST['EmailFromName'])."', '".jb_escape_sql($_REQUEST['EmailSubject'])."', '".jb_escape_sql($_REQUEST['sub_template'])."')";



		$result = JB_mysql_query($sql) or die (mysql_error());

		JB_format_email_translation_table ();

		$JBMarkup->ok_msg('Template Saved.');
	} else {
		$JBMarkup->ok_msg('Error: From email address is invalid');
	}



}

function email_config_form($email_id) {

	

	$result = JB_get_email_template ($email_id, $_SESSION['LANG']);

	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['EmailFromAddress']=='') {
		$row['EmailFromAddress'] = JB_SITE_CONTACT_EMAIL;
	}
	if ($row['EmailFromName']=='') {
		$row['EmailFromName'] = JB_SITE_NAME;
	}
	?>
	<form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
	<input type="hidden" name="EmailID" value="<?php echo $email_id; ?>">

	<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
	<tr><td bgColor="#eaeaea"><font size="2"><b>From Address:</b></font></td><td bgColor="#ffffff"><input size="40" type="text" name="EmailFromAddress" value="<?php echo JB_escape_html($row['EmailFromAddress']); ?>"></td></tr>
	<tr><td bgColor="#eaeaea"><font size="2"><b>From Name:</b></font></td ><td bgColor="#ffffff"><input size="40" type="text" name="EmailFromName" value="<?php echo JB_escape_html($row['EmailFromName']); ?>"></td></tr>
	<tr><td bgColor="#eaeaea"><font size="2"><b>Subject:</b></font></td><td bgColor="#ffffff"><input size="40" type="text" name="EmailSubject" <?php if ($email_id==10) echo ' disabled '; ?> value="<?php echo JB_escape_html($row['EmailSubject']); ?>"> <?php if ($email_id==10) echo '<b>Note: The subject of this email will be changed to \'app_receipt_subject\' language phrase which is editable via Admin->Languages : Editing/Translation tool. The \'From address\' and \'from name\' of this email will be changed to what ever is given by the applicant!</b>'; ?>(<small>Cannot use template tags here)</small></td></tr>
	<tr><td bgColor="#eaeaea"><font size="2"><b>Email Text:</b></font></td><td bgColor="#ffffff"><textarea name="EmailText" rows="20" cols="80"><?php echo JB_escape_html($row['EmailText']); ?></textarea></td></tr>
	<?php 
	if (($email_id==8) || ($email_id==7)) {	// job alerts
		?>
		<tr><td bgColor="#eaeaea"><font size="2"><b>Job list item template:</b></font></td><td bgColor="#ffffff"><b>%JOB_ALERTS%</b> : The following line will be iterated to produce the list of matching jobs in the %JOB_ALERTS% tag<br><textarea name="sub_template" rows="2" cols="80"><?php echo JB_escape_html($row['sub_template']); ?></textarea><br><font size="2"><b>You can use the following template tags in the field above:</b><br>
		%FORMATTED_DATE% - Formatted date according to the timezone<br>
		%BASE_HTTP_PATH% - Link to the website, eg http://www.example.com/<br>
		<?php
		
		require_once ("../include/posts.inc.php");

		$PForm = &JB_get_DynamicFormObject(1);
		$PForm->reset_fields();
	
		while ($field = $PForm->next_field()) {
			if (($field['field_type']=='BLANK') || ($field['field_type']=='SEPERATOR'))  {
				continue;
			}
			if (($field['template_tag'] !='') && (strlen($field['field_label'])>0)) {
				echo "%".$field['template_tag']."% - ".$field['field_label']."<br>";
			}

		}
		
		?>
		</font>
		</td></tr>
		<?php
	}

	?>
	<?php 
	if (($email_id==5) || ($email_id==6)) {	// resume alerts
		?>
		<tr><td bgColor="#eaeaea"><font size="2"><b>Resume list item template:</b></font></td><td bgColor="#ffffff"><b>%RESUME_ALERTS%</b> : The following line will be iterated to produce the list of matching resumes in the %RESUME_ALERTS%<br><textarea name="sub_template" rows="2" cols="80"><?php echo JB_escape_html($row['sub_template']); ?></textarea><br><font size="2">
		<b>You can use the following template tags in the field above:</b><br>
		%FORMATTED_DATE% - Formatted date according to the timezone<br>
		%RESUME_DB_LINK% - Direct link to the resume / resume database for the employer (<span style="color:red; font-weight:bold;">NEW!</span>)<br>
		<?php
	
		require_once ("../include/resumes.inc.php");

		$RForm = &JB_get_DynamicFormObject(2);
		$RForm->reset_fields();
		while ($field = $RForm->next_field()) {
			if (($field['field_type']=='BLANK') || ($field['field_type']=='SEPERATOR'))  {
				continue;
			}
			if (($field['template_tag'] !='') && (strlen($field['field_label'])>0)) {
				echo "%".$field['template_tag']."% - ".$field['field_type']."<br>";
			}

		}
		
		
		
		?>
		</font>
		</td></tr>
		<?php
	}

	?>
	<tr><td bgColor="#eaeaea"></td><td bgColor="#ffffff"><input type="submit" value="Save Template" name="submit"></td></tr>
	</table>
	</form>
	<?php

}


switch ($_REQUEST['EmailID']) {
	# confirm membership
	case "1": // Candidate Signup
		?>
		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Email to confirm membership. It is sent after a CANDIDATE registers with the website.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%FNAME% - User's First Name<br>
		%LNAME% - User's Last Name<br>
		%MEMBERID% - User's Member ID<br>
		%PASSWORD% - User's Password<br>
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		%SITE_URL% - Your Website URL<br>
		</div>

		<?php
		email_config_form(1);
		break;
	# Confirm membership
	case "2":
		?>
		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Email to confirm membership. It is sent after an EMPLOYER registers with the website.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%FNAME% - User's First Name<br>
		%LNAME% - User's Last Name<br>
		%MEMBERID% - User's Member ID<br>
		%PASSWORD% - User's Password<br>
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		%SITE_URL% - Your Website URL<br>
		</div>
		

		<?php
		
		email_config_form(2);

		
		
		break;
	
	case "3": // forget password
		?>
		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Email to sent to EMPLOYER or CANDIDATE after they request a new password.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in theemail text:<br>
		%FNAME% - User's First Name<br>
		%LNAME% - User's Last Name<br>
		%MEMBERID% - User's Member ID<br>
		%PASSWORD% - User's new Password<br>
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		</div>

		<?php
		email_config_form(3);
		break;
	
	case "4": // request candidate's details
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Email to sent to CANDIDATE to approve the request issued by EMPLOYER.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%FNAME% - User's First Name<br>
		%LNAME% - User's Last Name<br>
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		%EMPLOYER_NAME% - Name of employer issuing the request<br>
		%REPLY_TO% - Email of employer issuing the request<br>
		%MESSAGE% - Optional message from employer<br>
		%PERMIT_LINK% - The link a CANDIDATE clicks to approve the request<br>
		</div>

		<?php
		email_config_form(4); // Resume Alert (text)
		break;
	case "5":
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Email to sent to EMPLOYER showing new resumes on the system.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%FNAME% - User's First Name<br>
		%LNAME% - User's Last Name<br>
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		%RESUME_ALERTS% - List of new resumes (Edit the template for the list below)<br>
		<!--%KEYWORDS_LINE% - Summary of keyword used to generate the alert (if any)<br>-->
		%EMPLOYER_LINK% - Employer's hyperlink for direct access to the resumes<br>
		</div>

		<?php
		email_config_form(5);
		break;
	case "44":
		?>
		<div style="background-color: #E9E9E9;line-height: 20px;">
		%CAN_NAME% - candidate's name<br>
		%EMP_NAME% - employer's name<br>
		%SITE_NAME% - Site name<br>
		%RESUME_DB_LINK% - The direct link to the resume<br>
		%SITE_URL% - The URL to your site<br>
		</div>
		<?php
		email_config_form(44);
		break;
	case "6": // Resume Alert (html)
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Email to sent to EMPLOYER showing new resumes on the system.</b><br>
		<i>Email is sent in HTML mode.</i> Available Tags in the email text:<br>
		%FNAME% - User's First Name<br>
		%LNAME% - User's Last Name<br>
		%RESUME_ALERTS% - List of new resumes (Edit the template for the list below)<br>
		<!--%KEYWORDS_LINE% - Summary of keyword used to generate the alert (if any)<br>-->
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		%EMPLOYER_LINK% - Employer's hyperlink for direct access to the resumes<br>
		</div>

		<?php
		email_config_form(6);
		break;
	case "7": // Job Alert (text)
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Email to sent to CANDIDATE showing new jobs on the system.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%FNAME% - User's First Name<br>
		%LNAME% - User's Last Name<br>
		%JOB_ALERTS% - List of new jobs (<A href='emailconfig.php?mode=templ&edit=job_alert'>Edit Here</a>)<br>
		<!--%KEYWORDS_LINE% - Summary of keyword used to generate the alert (if any)<br>-->
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		%CANDIDATE_LINK% - Candidate's hyperlink for direct access to the job post<br>
		
		</div>

		<?php
		email_config_form(7);
		break;
	case "8": // Job Alert (html)
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Email to sent to CANDIDATE showing new jobs on the system.</b><br>
		<i>Email is sent in HTML mode.</i> Available Tags in the email text:<br>
		%FNAME% - User's First Name<br>
		%LNAME% - User's Last Name<br>
		%SITE_NAME% - Your Website name<br>
		%JOB_ALERTS% - List of new jobs (<A href='emailconfig.php?mode=templ&edit=job_alert'>Edit Here</a>)<br>
		<!--%KEYWORDS_LINE% - Summary of keyword used to generate the alert (if any)<br>-->
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		%CANDIDATE_LINK% - Candidate's hyperlink for direct access to the job post<br>
		</div>

		<?php
		email_config_form(8);
		break;
	case "9": // blank (See the lang/ files, use the tool in the Admin)
		
	case "10": // Job Application receipt
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>CANDIDATE's Job Application. Email to sent to CANDIDATE as a receipt of their application.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		%POSTED_BY% - Employer's Name<br>
		%EMPLOYER_EMAIL% - Employer's Email<br>
		%JOB_TITLE% - Job Title<br>
		%POST_URL% - Post URL<br>
		%POST_ID% - ID of the job post<br>
		%APP_NAME% - Candidate's Name<br>
		%APP_EMAIL% - Candidate's email<br>
		%APP_SUBJECT% - Candidate's application subject<br>
		%APP_LETTER% - Candidate's application letter<br>
		%APP_ATTACHMENT1% - 1st attachment<br>
		%APP_ATTACHMENT2% - 2nd attachment<br>
		%APP_ATTACHMENT3% - 3rd attachment<br>
		%RESUME_DB_LINK% - Direct link to the resume / resume database<br>
		<p></P>
		<p>Additionally, the following template tags from the job posting can be used:<br>
		<?php

		require_once ("../include/posts.inc.php");

		$PForm = &JB_get_DynamicFormObject(1);
		$PForm->reset_fields();
	
		while ($field = $PForm->next_field()) {
			if (($field['field_type']=='BLANK') || ($field['field_type']=='SEPERATOR'))  {
				continue;
			}
			if (($field['template_tag'] !='') && (strlen($field['field_label'])>0)) {
				echo "%".$field['template_tag']."% - ".$field['field_label']."<br>";
			}

		}
		?>
		</p>
		</div>

		<?php
		email_config_form(10);
	break;

	case "11": // Employer to Candidate
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Email to sent to CANDIDATE by employer from the resume search page.</b><br>
		Note: The subject will be overwritten by whatever subject was provided by the employer<br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%MESSAGE% - The message typed in by the employer to the candidate<br>
		%EMPLOYER_NAME% - Employers's name (or company name if present)<br>
		%SITE_NAME% - Your Website name<br>
		%SITE_URL% - Your Website name<br>
		%USER_ID% - The ID of the employer who sent the email (Useful for identyfying the account it was sent from)<br>
		%SENDER_IP% - The IP Address of the employer who sent the email<br>
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>

		</div>

		<?php
		email_config_form(11);
		break;


	case "12": // Job Application Template
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>CANDIDATE's Job Application. Email to sent to CANDIDATE as a receipt of their application.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%SITE_CONTACT_EMAIL% - Contact for website admin (from config)<br>
		%SITE_NAME% - Your Website name<br>
		%POSTED_BY% - Employer's Name<br>
		%EMPLOYER_EMAIL% - Employer's Email<br>
		%JOB_TITLE% - Job Title<br>
		%POST_URL% - Post URL<br>
		%POST_ID% - ID of the job post<br>
		%APP_NAME% - Candidate's Name<br>
		%APP_EMAIL% - Candidate's email<br>
		%APP_SUBJECT% - Candidate's application subject<br>
		%APP_LETTER% - Candidate's application letter<br>
		%APP_ATTACHMENT1% - name of the 1st attachment<br>
		%APP_ATTACHMENT2% - name of the 2nd attachment<br>
		%APP_ATTACHMENT3% - name of the 3rd attachment<br>
		%RESUME_DB_LINK% - Direct link to the resume / resume database, or 'online resume not present' message will be returned if not available.<br>
		%BASE_HTTP_PATH% - The link to your site<br>
		<p><b>Note: The subject, from address and from name of this email will be changed to what ever is given by the applicant!</b></P>
		<p>Additionally, the following template tags from the job posting can be used:<br>
		<?php

		require_once ("../include/posts.inc.php");

		$PForm = &JB_get_DynamicFormObject(1);
		$PForm->reset_fields();
	
		while ($field = $PForm->next_field()) {
			if (($field['field_type']=='BLANK') || ($field['field_type']=='SEPERATOR'))  {
				continue;
			}
			if (($field['template_tag'] !='') && (strlen($field['field_label'])>0)) {
				echo "%".$field['template_tag']."% - ".$field['field_label']."<br>";
			}

		}
		?>
		</p>
		</div>

		<?php
		email_config_form(12);
		break;

		case "60": // Posting Confirmed - Bank
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Confirmed order & Bank payment selected.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%ITEM_NAME% - Item Name<br>
		%QUANTITY% - How many posts<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%BANK_NAME% - your bank name<br>
		%BANK_ADDRESS% - your bank address<br>
		%AC_NAME% - your account name<br>
		%AC_NUMBER% - your account number<br>
		%BANK_AC_SWIFT% - your bank's SWIFT number<br>
		%BANK_AC_BRANCH% - your bank's branch number / routing code
		%BANK_AC_CURRENCY% - your account currency<br>
		%SITE_URL% - Link to your homepage<br>
		%SITE_CONTACT_EMAIL% - contact email<br>
		%INVOICE_TAX% - tax amount calculated from the %INVOICE_AMOUNT%<br>
		</div>

		<?php
		email_config_form(60);
		break;

		case "61": // Posting Confirmed - Check
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Confirmed order & check / money order payment selected.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%QUANTITY% - How many posts<br>
		%PAYEE_NAME% - your name payable to<br>
		%PAYEE_ADDRESS% - your address payable to<br>
		%CHECK_CURRENCY% - currency payable to<br>
		%SITE_URL% - Link to your homepage<br>
		%CHECK_CURRENCY% - currency payable in<br>
		%INVOICE_TAX% - tax amount calculated from the %INVOICE_AMOUNT%<br>
		</div>

		<?php
		email_config_form(61);
		break;

		

		case "70": // Posting Completed 
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Sent when a payment is completed for the posting order</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%QUANTITY% - How many posts<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%ITEM_NAME% - the name of the item<br>
		%PAYMENT_METHOD% - Payment method used<br>
		%SITE_URL% - Link to your homepage<br>
		%SITE_CONTACT_EMAIL% - contact email<br>
		%DATE% - Today's Date<br>
		%INVOICE_TAX=[0.1]% - Invoice tax amount <small>(calculated from the %INVOICE_AMOUNT% where 0.1 is a configurable tax rate represented as a decimal, ie. 0.1 means 10%. Assumes that %INVOICE_AMOUNT% is the price with tax included, so the formula to work out the tax is: INVOICE_TAX = INVOICE_AMOUNT - (INVOICE_AMOUNT / (1.00 + TAX_RATE))</small><br>
		
		</div>

		<?php
		email_config_form(70);
		break;

		

		

		case "80": // Subscription order confirmed - bank
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Sent when subscription order is confirmed and Bank payment is selected</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%QUANTITY% - How many posts<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%BANK_NAME% - your bank name<br>
		%BANK_ADDRESS% - your bank address<br>
		%AC_NAME% - your account name<br>
		%AC_NUMBER% - your account number<br>
		%BANK_AC_SWIFT% - your bank's SWIFT number<br>
		%BANK_AC_BRANCH% - your bank's branch number / routing code
		%BANK_AC_CURRENCY% - your account currency<br>
		%SITE_URL% - Link to your homepage<br>
		%SITE_CONTACT_EMAIL% - contact email<br>
		%SUB_DURATION% - subscription duration (months)<br>
		</div>

		<?php
		email_config_form(80);
		break;

		case "81": // Subscription order confirmed - check / money order
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Sent when subscription order is confirmed and Check/Money Order payment is selected</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%ITEM_NAME% - name of the subscription<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%PAYEE_NAME% - your name payable to<br>
		%PAYEE_ADDRESS% - your address payable to<br>
		%CHECK_CURRENCY% - currency payable to<br>
		%SITE_URL% - Link to your homepage<br>
		%SUB_DURATION% - Subscription duration (months)<br>
		%CHECK_CURRENCY% - currency payable in
		</div>

		<?php
		email_config_form(81);
		break;

		

		case "90": // Subscription order completed
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Sent when subscription order is completed.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%ITEM_NAME% - name of the subscription item<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%SUB_DURATION% - duration (months)<br>
		%SUB_START% - start date<br>
		%SUB_END% - end date<br>
		%PAYMENT_METHOD% - payment method used<br>
		%SITE_URL% - Link to your homepage<br>
		%SITE_CONTACT_EMAIL% - contact email<br>
		%INVOICE_TAX=[0.1]% - Invoice tax amount <small>(calculated from the %INVOICE_AMOUNT% where 0.1 is a configurable tax rate represented as a decimal, ie. 0.1 means 10%. Assumes that %INVOICE_AMOUNT% is the price with tax included, so the formula to work out the tax is: INVOICE_TAX = INVOICE_AMOUNT - (INVOICE_AMOUNT / (1.00 + TAX_RATE))</small><br>
		</div>
		<?php
		email_config_form(90);
		break;

		
		case "100": // Signup order confirmed - Bank
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Sent when a Membership order is confirmed and an Bank payment is selected. </b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%ITEM_NAME% - name of the membership<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%BANK_NAME% - your bank name<br>
		%BANK_ADDRESS% - your bank address<br>
		%AC_NAME% - your account name<br>
		%AC_NUMBER% - your account number<br>
		%BANK_AC_SWIFT% - your bank's SWIFT number<br>
		%BANK_AC_BRANCH% - your bank's branch number / routing code
		%BANK_AC_CURRENCY% - your account currency<br>
		%SITE_URL% - Link to your homepage<br>
		%SITE_CONTACT_EMAIL% - contact email<br>
		%MEM_DURATION% - duration of the membership (months)<br>
		</div>
		<?php
		email_config_form(100);
		break;

		case "101": // Signup order confirmed - Check /Money Order
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Sent when a Membership order is confirmed and an Check / Money Order payment is selected. </b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%ITEM_NAME% - name of the membership<br>
		%INVOICE_CODE% - invoice ID<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%PAYEE_NAME% - your name payable to<br>
		%PAYEE_ADDRESS% - your address payable to<br>
		%CHECK_CURRENCY% - currency payable to<br>
		%SITE_URL% - Link to your homepage<br>
		%MEM_DURATION% - duration of the membership<br>
		</div>
		<?php
		email_config_form(101);
		break;

		

		case "110": // Membership order completed
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Sent when a the payment for a Membership order is Completed. </b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%ITEM_NAME% - item name of the membership<br>
		%MEM_DURATION% - duration (months)<br>
		%MEM_START% - start date<br>
		%MEM_END% - end date<br>
		%SITE_URL% - Link to your homepage<br>
		%SITE_CONTACT_EMAIL% - contact email<br>
		%INVOICE_TAX=[0.1]% - Invoice tax amount <small>(calculated from the %INVOICE_AMOUNT% where 0.1 is a configurable tax rate represented as a decimal, ie. 0.1 means 10%. Assumes that %INVOICE_AMOUNT% is the price with tax included, so the formula to work out the tax is: INVOICE_TAX = INVOICE_AMOUNT - (INVOICE_AMOUNT / (1.00 + TAX_RATE))</small><br>
		</div>
		<?php
		email_config_form(110);
		break;


		case "120": // Membership expired
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Sent when membership had expired.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%ITEM_NAME% - name of the membership item<br>
		%PAYMENT_METHOD% - the payment method used<br>
		%MEM_START% - Start Date<br>
		%MEM_END% - End Date<br>
		%MEM_DURATION% - Membership Duration<br>
		%SITE_URL% - URL to your site<br>
		%SITE_CONTACT_EMAIL% - contact email to your site.<br>
		</div>
		<?php
		email_config_form(120);
		break;

		case "130": // Subscription expired
		?>

		<div style="background-color: #E9E9E9;line-height: 20px;">
		<b>Sent when subscription had expired.</b><br>
		<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
		%LNAME% - last name of the user<br>
		%FNAME% - first name of the user<br>
		%SITE_NAME% - name of your website<br>
		%INVOICE_CODE% - invoice ID<br>
		%INVOICE_AMOUNT% - invoice amount<br>
		%ITEM_NAME% - name of the subscription item<br>
		%PAYMENT_METHOD% - the payment method used<br>
		%SUB_START% - Start Date<br>
		%SUB_END% - End Date<br>
		%SUB_DURATION% - Subscription Duration<br>
		%SITE_URL% - URL to your site<br>
		%SITE_CONTACT_EMAIL% - contact email to your site.<br>
		</div>
		<?php
		email_config_form(130);
		break;

		case "210": // Job Post expired
			?>
			<div style="background-color: #E9E9E9;line-height: 20px;">
			<b>Sent when job post had expired.</b><br>
			<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
			%LNAME% - last name of the user<br>
			%FNAME% - first name of the user<br>
			%SITE_NAME% - name of your website<br>
			%SITE_URL% - URL to your site<br>
			%SITE_CONTACT_EMAIL% - contact email to your site.<br>
			%POST_TITLE% - The title of the post<br>
			%POST_DATE% - The date of the post<br>
			%VIEWS% - The number of views the post received<br>
			%APPS% - The number of online applications the post received<br>
			</div>

			<?php
			email_config_form(210);
		break;

		case "220": // Job Post approved
			?>
					
			<div style="background-color: #E9E9E9;line-height: 20px;">
			<b>Sent when job post was approved by the Admin.</b><br>
			<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
			%LNAME% - last name of the user<br>
			%FNAME% - first name of the user<br>
			%SITE_NAME% - name of your website<br>
			%SITE_URL% - URL to your site<br>
			%SITE_CONTACT_EMAIL% - contact email to your site.<br>
			%POST_TITLE% - The title of the post<br>
			%POST_DATE% - The date of the post<br>
			%POST_URL% - The URL of the post<br>
			
			</div>

			<?php
			email_config_form(220);
		break;

		case "230": // Job Post disapproved
			?>
			<div style="background-color: #E9E9E9;line-height: 20px;">
			<b>Sent when job post was disapproved by the Admin.</b><br>
			<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
			%LNAME% - last name of the user<br>
			%FNAME% - first name of the user<br>
			%SITE_NAME% - name of your website<br>
			%SITE_URL% - URL to your site<br>
			%SITE_CONTACT_EMAIL% - contact email to your site.<br>
			%POST_TITLE% - The title of the post<br>
			%POST_DATE% - The date of the post<br>
			%REASON% - The reason for the disapproval<br>
			</div>


			<?php
			email_config_form(230);
		break;

		case "310": // A new Job posted
			?>
			<div style="background-color: #E9E9E9;line-height: 20px;">
			<b>Sent to the Admin when a new job post was posted.</b><br>
			<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
			
			%SITE_NAME% - name of your website<br>
			%SITE_URL% - URL to your site<br>
			%SITE_CONTACT_EMAIL% - Admin contact email
			%POST_TITLE% - The title of the post<br>
			%DATE% - The date of the post<br>
			%POSTED_BY% - Name of the employer on the post<br>
			%POST_DESCRIPTION% - Post description<br>
			%ADMIN_LINK% - Link to admin<br>

			</div>


			<?php
			email_config_form(310);
		break;

		case "320": // A new resume is posted / updated
			?>
			<div style="background-color: #E9E9E9;line-height: 20px;">
			<b>Sent to the Admin when resume is updated / posted.</b><br>
			<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
			
			%SITE_NAME% - name of your website<br>
			%SITE_URL% - URL to your site<br>
			%RESUME_SUMMARY% - summary of the resume posted<br>
			%ADMIN_LINK% - Link to Admin<br>
			</div>


			<?php
			email_config_form(320);
		break;

		case "330": // A new order
			?>
			<div style="background-color: #E9E9E9;line-height: 20px;">
			<b>Sent to the Admin when a new Posting Order / Subscription / Membership is placed.</b><br>
			<i>Email is sent in text mode.</i> Available Tags in the email text:<br>
			%ITEM_NAME% - The copy of the original order text<br>
			%ORDER_ID% - The order ID<br>
			%PRICE% - The price<br>
			%USER% - Username the customer<br>
			%FNAME% - First Name<br>
			%LNAME% - Last Name<br>
			%INVOICE_AMOUNT% - Invoice Amount<br>
			%INVOICE_CODE% - Order ID<br>
			%ADMIN_LINK% - Link to the Admin<br>
			</div>


			<?php
			email_config_form(330);
		break;



}

?>


    </div>

	<?php

	?>

	</td></tr></table>
<p>
    


<?php

JB_admin_footer();


?>