<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
function JB_save_help($type, $title, $message) {

	if (($type!='E') && ($type!='U')) {return false; }

	
	$time = gmdate("Y-m-d H:i:s");

	$sql = "REPLACE INTO `help_pages` (`help_type`, `help_lang`, `help_message`, `help_title`, `help_date_updated`) VALUES ('$type', '".jb_escape_sql($_SESSION['LANG'])."', '".jb_escape_sql($message)."', '".jb_escape_sql($title)."', '$time') ";
	JB_mysql_query($sql) or die(mysql_error());



}

function JB_load_help($type) {
	if (($type!='E') && ($type!='U')) {return false; }

	$data = array();

	$sql = "SELECT * FROM `help_pages` where `help_type` = '$type' AND `help_lang`='".jb_escape_sql($_SESSION['LANG'])."' ";
	$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	$row = @mysql_fetch_array($result, MYSQL_ASSOC);
	$data['title']=$row['help_title'];
	$data['message']=$row['help_message'];
	$data['updated']=$row['help_date_updated'];

	/*$sql = "SELECT * FROM `jb_variables` where `key` = 'HELP_$type"."_DISPLAY' ";
	$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	$row = @mysql_fetch_array($result, MYSQL_ASSOC);
	$data['display']=$row['val'];
	*/

	if ($data['message']=='') {

		$data['message'] = jb_extract_old_help_file ($type);
		global $label;
		$data['title'] = $label['c_help_heading'];

	}

	return $data;


}

function JB_display_help($type, $width=100) {
	if (($type!='E') && ($type!='U')) {return false; }
	$data = JB_load_help($type);
	if ($data['display']=='YES') {
		JB_render_box_top($width,  $data['title']);
		echo $data['message'];
		JB_render_box_bottom();
		return true;
	}
	return false;


}

function jb_extract_old_help_file ($type) {

	global $label;

	if ($type=='E') {

		/*
		$folder = JB_EMPLOYER_FOLDER;
		$filename = JB_basedirpath().$folder.'help.php';

		$fh = fopen ($filename, 'r');
		$str = fread ($fh, filesize($filename));

		if (preg_match ('#<table.+</table>#si', $str, $m)) {
			$str = $m[0];
			$str = str_replace('<h2><font face="Verdana">Help</font></h2>', '', $str);
		}*/

		$str = 

			'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"  width="80%"  align="center">
    <tr>
      <td width="100%">
      <h2><font face="Verdana">Help</font></h2>
      <h2><font face="Verdana">Employer\'s Account</font></h2>
      <p><font face="Verdana" size="2">Your Employer\'s account allows you to post job 
      advertisements to the '.jb_escape_html(JB_SITE_NAME).' Job Board. It also allows you to browse 
       resumes that were posted to '.jb_escape_html(JB_SITE_NAME).'. You can also create a 
      business profile, which is similar to an online business card which you 
      can then use to promote your business to potential candidates.</font></p>
      <h3><font face="Verdana">Main Menu</font></h3>
      <h4><font face="Verdana"><a href="#account">Account - To access the Main 
      Page of your account &amp; to Logout</a></font></h4>
      <h4><font face="Verdana"><a href="#profile">Profile - To Edit / View your 
      business profile. </a></font></h4>
      <h4><font face="Verdana"><a href="#resumes">Resumes - To Browse Resumes. </a></font></h4>
      <h4><font face="Verdana"><a href="#posts">Posts - To Post and Manage your 
      Job Advertisements on the '.jb_escape_html(JB_SITE_NAME).' Job Board</a></font></h4>
      <h4><font face="Verdana"><a href="#help">Help - This help page.</a></font></h4>
      <hr>
      <h4><font face="Verdana"><a name="account"></a>Account</font></h4>
      <p><font face="Verdana" size="2"><b>Main Page</b> - This is the first page 
      that you see when you log into your account<br>
      <b>Logout</b> - When you want to log out of the system, choose this from 
      the menu.</font></p>
      <hr>
      <h4><font face="Verdana"><a name="profile"></a>Profile - To Edit / View 
      your business profile. </font></h4>
      <p><font face="Verdana" size="2">A business profile is like a business 
      card on paper. This section allows you to create a business card&nbsp; for 
      the website for job seekers to see. To Create a new profile, go to Edit 
      Profile. You can view your profile.</font></p>
      <p><font face="Verdana" size="2"><b>Edit Profile </b>- Edit or Create a 
      new profile. Only one profile can be made. Fields that are mandatory are 
      marked with a red star. Once you finish editing your profile, click the 
      Submit button.</font></p>
      <p><font face="Verdana" size="2"><b>View Profile </b>- Once a profile 
      exists, you can View the profile here. The profile is displayed just as 
      everyone else would see it.</font></p>
      <hr>
      <h4><font face="Verdana"><a name="resumes"></a>Resume - Browse Resumes. </font></h4>
      <p><font face="Verdana" size="2">Everyday, there are many job seekers who are looking for a job on '.jb_escape_html(JB_SITE_NAME).' Job seekers to post their C.V. into our database. You can browse the database from here. </font></p>
      <p><font face="Verdana" size="2"><b>Browse Resumes </b>- View resumes and photos.</font></p>
      
      <hr>
      <h4><font face="Verdana"><a name="posts"></a>Posts - To manage 
      your Job Advertisements on the '. jb_escape_html(JB_SITE_NAME).' Job Board</font></h4>
      <p><font face="Verdana" size="2">Here is where you can manage your job 
      posts on '.jb_escape_html(JB_SITE_NAME).' You can Post a New Job from here, or you 
      can Edit or Delete your previous jobs. When you create and submit a new 
      job post, it needs to be approved by '. jb_escape_html(JB_SITE_NAME).' before it can be published for everyone to see. From the Job Post Manager, you can see which posts have 
      been approved and which posts are on the waiting list to be approved. Note 
      that every time you edit a post, it will need to be approved again. 
      Approval usually takes less than 24 hours.</font></p>
      <p><font face="Verdana" size="2"><b>Job Post Manager </b>- Edit or Delete 
      Jobs Posts. Allows you to see what posts you have made, and what is their 
      approval status.</font></p>
      <p><font face="Verdana" size="2"><b>Post New Job</b> - Post a new Job 
      advertisement. The advertisement includes a map, which helps 
      job seekers with the finding the location of the job. You can move the yellow pin.</font></p>
      <h3>Html Editor Tips</h3>
      <p>The Job Advertisement Editor is an advanced HTML editor which allows you to format your advertsiment text in more interesting ways. Here are some tips for using the editor:</p>
      * To go to a new line without starting a new paragraph, press "Shift" + "Enter"<br>
      * Try to avoid pasting text from Word and other adavnced Editors.<br>
      * Don\'t over-use and put too much formatting - it may make your post difficult to read! 
      <hr>
      
      <h4><font face="Verdana"><a name="help"></a>Help - This help page.</font></h4>
      <p><font size="2">Do you have any questions, problems or ideas? Please 
      write to us: </font><a href="mailto:'. JB_SITE_CONTACT_EMAIL.'">
      <font size="2">'. JB_SITE_CONTACT_EMAIL.'</font></a></p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</td>
    </tr>
  </table>';

	} else {
		$str = $label["c_help_text"];
	}

	return $str;




}

?>