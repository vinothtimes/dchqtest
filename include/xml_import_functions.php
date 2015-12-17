<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################	
require ('xml_import_parsers.php');

function JB_save_import_feed_form() {

	$feed_id = (int) $_REQUEST['feed_id'];

	// read the sample XML file and get it ready to be placed in the database.

	if ($_FILES['xml_sample']['name']!='') {

		$uploaddir = JB_FILE_PATH;

		$a = explode(".",$_FILES['xml_sample']['name']);
		$ext = strtolower(array_pop($a));
		$name = strtolower(array_shift($a));
		if ($_SESSION['JB_ID'] != '') {
			$name = "xml_".$name;
		}
	   
		$name = preg_replace('#[^a-z^0-9]+#i', "_", $name); // strip out unwanted characters
		$ext = preg_replace('#[^a-z^0-9]+#i', "_", $ext); // strip out unwanted characters
		
		$new_name = $name.time().".".$ext;
		$uploadfile = $uploaddir . $new_name; //

		if (strpos(strtoupper(PHP_OS), 'WIN')!==false) { 
			// sometimes the dir can have double slashes on Win, remove 'em
			$_FILES['xml_sample']['tmp_name'] = str_replace ('\\\\', '\\', $_FILES['xml_sample']['tmp_name']);
		}

		if (move_uploaded_file($_FILES['xml_sample']['tmp_name'], $uploadfile)) {
			//echo "File is valid, and was successfully uploaded.\n";
			@chmod ($uploadfile, JB_NEW_FILE_CHMOD);
		}

		$fp = fopen($uploadfile, 'r');
		$xml_sample = fread($fp, filesize($uploadfile));
		fclose($fp);

		unlink($uploadfile); // do not need it anymore

		if ($feed_id!=false) {
			// reset the sequence element.
			$feed_row = array();
			$feed_row = JB_XMLIMP_load_feed_row($feed_id);
			$feed_row['FMD']->seq = '';
			$feed_row['FMD']->save(); 
			jb_xml_import_update_status($feed_row);
		}

	}

	if ($feed_id==false) {

		$feed_id = JB_db_generate_id_fast('feed_id', 'xml_import_feeds');

		// initialize the feed meta-data
		$FMD = new JB_XMLImportFeedMetaData($feed_id);

		$sql = "INSERT INTO `xml_import_feeds` (`feed_id`, `feed_metadata`, `feed_name`, `description`, `date`, `xml_sample`, `feed_key`, `ip_allow`, `feed_url`, `feed_filename`, `ftp_user`, `ftp_pass`, `ftp_filename`, `ftp_host`, `pickup_method`, `status`, `cron`) VALUES ('".$feed_id."', '".jb_escape_sql(serialize($FMD))."', '".jb_escape_sql($_REQUEST['feed_name'])."', '".jb_escape_sql($_REQUEST['description'])."', NOW(), '".jb_escape_sql(addslashes($xml_sample))."', '".jb_escape_sql($_REQUEST['feed_key'])."', '".jb_escape_sql($_REQUEST['ip_allow'])."', '".jb_escape_sql($_REQUEST['feed_url'])."', '".jb_escape_sql($_REQUEST['feed_filename'])."', '".jb_escape_sql($_REQUEST['ftp_user'])."', '".jb_escape_sql($_REQUEST['ftp_pass'])."', '".jb_escape_sql($_REQUEST['ftp_filename'])."', '".jb_escape_sql($_REQUEST['ftp_host'])."', '".jb_escape_sql($_REQUEST['pickup_method'])."', 'NEW_SAMPLE', '".jb_escape_sql($_REQUEST['cron'])."');"; 

		

		jb_mysql_query($sql);


	} else {

		if ($xml_sample != false) {
			$xml_sample_sql = ", `xml_sample`='".jb_escape_sql(addslashes($xml_sample))."', status='NEW_SAMPLE' ";
		}

		// save the form data. feed_metadata is edited somewhere else and not saved here

		$sql = "UPDATE xml_import_feeds SET `feed_name`='".jb_escape_sql($_REQUEST['feed_name'])."', `description`='".jb_escape_sql($_REQUEST['description'])."' $xml_sample_sql, `feed_key`='".jb_escape_sql($_REQUEST['feed_key'])."', `ip_allow`='".jb_escape_sql($_REQUEST['ip_allow'])."', `feed_url`='".jb_escape_sql($_REQUEST['feed_url'])."', `feed_filename`='".jb_escape_sql($_REQUEST['feed_filename'])."', `ftp_user`='".jb_escape_sql($_REQUEST['ftp_user'])."', `ftp_pass`='".jb_escape_sql($_REQUEST['ftp_pass'])."', `ftp_filename`='".jb_escape_sql($_REQUEST['ftp_filename'])."', `ftp_host`='".jb_escape_sql($_REQUEST['ftp_host'])."',
		`pickup_method`='".jb_escape_sql($_REQUEST['pickup_method'])."', `cron`='".jb_escape_sql($_REQUEST['cron'])."' WHERE feed_id = '".jb_escape_sql($feed_id)."' ";

		jb_mysql_query($sql);

	}

	return $feed_id;

	

}

###################################

FuNcTiOn JB_save_import_feed_field_setup_form() {

	$feed_id = (int) $_REQUEST['feed_id'];

	$feed = JB_XMLIMP_load_feed_row($feed_id);
	
	$feed['FMD']->fillOptionsFromRequest();
	$feed['FMD']->save();

}

###################################

function jb_delete_import_feed($feed_id) {

	$feed_id = (int) $feed_id;

	$sql = "DELETE FROM xml_import_feeds WHERE feed_id='".jb_escape_sql($feed_id)."' ";
	jb_mysql_query($sql);
	


}

###############################################

function JB_validate_import_feed_form() {

	$file_path = __FILE__; // eg e:/apache/htdocs/ojo/admin/edit_config.php
	
	$file_path = explode (DIRECTORY_SEPARATOR, $file_path);
	array_pop($file_path); // get rid of filename
	array_pop($file_path); // get rid of /admin
	$file_path = implode (DIRECTORY_SEPARATOR, $file_path);
	$file_path .= DIRECTORY_SEPARATOR;
	if ((trim($_REQUEST['feed_name'])==false)) {
		$error .= "- Please enter a name for your feed<br> ";
	}

	if ($_REQUEST['xml_sample_exists']==false) {

		if (trim($_REQUEST['xml_sample'])==false) {
			if ($_FILES['xml_sample']['name']==false) {
				$error .= "- Please upload a sample XML file<br> ";
			}
		}

	}

	switch ($_REQUEST['pickup_method']) {

		case 'FILE':
			if ((trim($_REQUEST['feed_filename'])==false)) {
				$error .= '- Please enter a filename your XML feed file<br>';
			} elseif (!is_file($_REQUEST['feed_filename'])) {
				$error .= '- Fetch from File: Please check the path to the XML feed file, the system cannot find it<br>';
			}
			break;
		case 'POST':
			break;
		case 'URL':
			if (trim($_REQUEST['feed_url'])==false) {
				$error .= '- Fetch from URL: Please enter a URL to your XML feed<br>';
			}
			break;
		case 'FTP':
			if (trim($_REQUEST['ftp_host'])==false) {
				$error .= '- FTP: Please enter a host<br>';
			}
			if (trim($_REQUEST['ftp_user'])==false) {
				$error .= '- FTP: Please enter a username<br>';
			}
			if (trim($_REQUEST['ftp_pass'])==false) {
				$error .= '- FTP: Please enter a password<br>';
			}
			if (trim($_REQUEST['ftp_filename'])==false) {
				$error .= '- FTP: Please the path to your XML feed<br>';
			}

			break;

	}

	return $error;


}

################################################

function JB_display_import_feed_form($load_row=true) {

	$file_path = __FILE__; // eg e:/apache/htdocs/ojo/admin/edit_config.php
	
	$file_path = explode (DIRECTORY_SEPARATOR, $file_path);
	array_pop($file_path); // get rid of filename
	array_pop($file_path); // get rid of /include
	$file_path = implode (DIRECTORY_SEPARATOR, $file_path);

	if ($load_row) {

		// load feed from database

		$sql = "select * from xml_import_feeds WHERE feed_id='".jb_escape_sql($_REQUEST['feed_id'])."' ";
		
		$result = JB_mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		$_REQUEST['feed_name'] = $row['feed_name'];
		$_REQUEST['description'] = $row['description'];
		$_REQUEST['feed_name'] = $row['feed_name'];
		if ($row['xml_sample'] != '') {
			$_REQUEST['xml_sample'] = $row['xml_sample'];
			$_REQUEST['xml_sample_exists'] = true;
		}
		$_REQUEST['feed_key'] = $row['feed_key'];
		$_REQUEST['ip_allow'] = $row['ip_allow'];
		$_REQUEST['feed_url'] = $row['feed_url'];
		$_REQUEST['feed_filename'] = $row['feed_filename'];
		$_REQUEST['ftp_user'] = $row['ftp_user'];
		$_REQUEST['ftp_pass'] = $row['ftp_pass'];
		$_REQUEST['ftp_filename'] = $row['ftp_filename'];
		$_REQUEST['ftp_host'] = $row['ftp_host'];
		$_REQUEST['pickup_method'] = $row['pickup_method'];
		$_REQUEST['cron'] = $row['cron'];
		if ($_REQUEST['action']=='new_feed') {
			?>
			<h3>Add a new Feed to Import</h3>
			<?php

		} else {
			?>
			<h3>Edit Feed</h3>
			<?php
		}


	} else {

		$_REQUEST['feed_name'] = stripslashes($_REQUEST['feed_name']);
		$_REQUEST['description'] = stripslashes($_REQUEST['description']);
		$_REQUEST['feed_name'] = stripslashes($_REQUEST['feed_name']);
		if ($_REQUEST['xml_sample'] != '') {
			$_REQUEST['xml_sample'] = stripslashes($_REQUEST['xml_sample']);
			$_REQUEST['xml_sample_exists'] = true;
		}
		$_REQUEST['feed_key'] = stripslashes($_REQUEST['feed_key']);
		$_REQUEST['ip_allow'] = stripslashes($_REQUEST['ip_allow']);
		$_REQUEST['feed_url'] = stripslashes($_REQUEST['feed_url']);
		$_REQUEST['feed_filename'] = stripslashes($_REQUEST['feed_filename']);
		$_REQUEST['ftp_user'] = stripslashes($_REQUEST['ftp_user']);
		$_REQUEST['ftp_pass'] = stripslashes($_REQUEST['ftp_pass']);
		$_REQUEST['ftp_filename'] = stripslashes($_REQUEST['ftp_filename']);
		$_REQUEST['ftp_host'] = stripslashes($_REQUEST['ftp_host']);
		$_REQUEST['pickup_method'] = stripslashes($_REQUEST['pickup_method']);
		$_REQUEST['cron'] = stripslashes($_REQUEST['cron']);


		?>
		<h3>Add New Feed</h3>
		<?php
	}


	?>
	

	<form id="dynamic_form" enctype="multipart/form-data" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF'])?>">
	<input type="hidden" name="feed_id" value="<?php echo jb_escape_html($_REQUEST['feed_id']); ?>">
	<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
		<tr bgcolor="#ffffff" >
			<td bgcolor="#eaeaea"><b>Feed Name: <span class="is_required_mark">*</span></b></td>
			<td><input size="30" type="text" name="feed_name" value="<?php echo jb_escape_html($_REQUEST['feed_name']); ?>"> eg. Jamit.com XML Import</td>
		</tr>
		<tr bgcolor="#ffffff" >
			<td bgcolor="#eaeaea"><b>Feed Description</b></td>
			<td><textarea name="description" rows="4" cols="30"><?php echo jb_escape_html($_REQUEST['description']);?></textarea></td>
		</tr>
		<tr bgcolor="#ffffff" >
			<td bgcolor="#eaeaea"><b>XML Sample file <span class="is_required_mark">*</span></b></td>
			<td> <?php if ($_REQUEST['xml_sample']==true) { echo "Here is the existing sample file. You can change it by re-uploading the file. (<i>Sample only, do not edit the field below</i>)<br><textarea disabled name='xml_sample' rows='4' cols='80'>".jb_escape_html($_REQUEST['xml_sample'])."</textarea>"; echo '<input type="hidden" name="xml_sample" value="'.htmlentities($_REQUEST['xml_sample']).'"><input type="hidden" name="xml_sample_exists" value="1">'; } ?><br>
			<input type="file" name="xml_sample" > (Please upload an XML file with some sample data. The sample should contain only job 1 record)</td>
		</tr>
		<tr>
			<td bgcolor='#eaeaea'><b>IP Address Allow</b></td>
			
			<?php if ($_REQUEST['up_allow']==false) {  $_REQUEST['ip_allow']='ALL,localhost';}  ?>
		
			<td bgcolor='#ffffff'><textarea name='ip_allow' rows='1' cols='60'><?php echo htmlentities($_REQUEST['ip_allow']); ?></textarea><br>List of addresses seperated by commas. Special values can be ALL and localhost.
			</td>
		</tr>
		
		<tr>
		<tr bgcolor="#ffffff" >
			<td colspan="2" bgcolor="#eaeaea">Feed pickup-method <span class="is_required_mark">*</span>: Select how the XML feed will be imported</td>
		</tr>

		</tr>
		<?php

	  if ($_REQUEST['feed_filename']==false) {

		  $_REQUEST['feed_filename'] = $file_path.DIRECTORY_SEPARATOR;

	  }
	  if ($_REQUEST['pickup_method']==false) {

		  $_REQUEST['pickup_method'] = 'FILE';

	  }
		?>
		<tr bgcolor="#ffffff" >
			<td bgcolor="#eaeaea"><b>Server Push</b></td>
			<td>  <input type="radio" name="pickup_method" value="POST" <?php if ($_REQUEST['pickup_method']=='POST') echo ' checked '; ?>>Pushed to your job board<br>
			If this method is selected, then a remote server will use HTTP to POST the XML data to your job board.<br>
			Feed Key: <input size="30" type="text" name="feed_key" value="<?php echo jb_escape_html($_REQUEST['feed_key']); ?>"> (If required, enter your secret feed key here.)
			</td>
		</tr>
		<tr bgcolor="#ffffff" >
			<td bgcolor="#eaeaea"><b>Fetch from URL</b></td>
			<td>  <input type="radio" name="pickup_method" value="URL" <?php if ($_REQUEST['pickup_method']=='URL') echo ' checked '; ?>>From a URL<br>
			If this method is selected, then the XML feed will be read from the specified URL.<br>
			URL to XML file: <input size="70" type="text" name="feed_url" value="<?php echo jb_escape_html($_REQUEST['feed_url']); ?>"> eg. http://www.example.com/export.xml
			<?php

			if (!ini_get('allow_url_fopen')) {
				echo "<br>Please Note: It looks like your server has 'allow_url_fopen' turned off. This option will not work on this server<br>";
			}

			?>
			</td>
		</tr>

		<tr bgcolor="#ffffff" >
			<td bgcolor="#eaeaea"><b>Fetch from File</b></td>
			<td>  <input type="radio" name="pickup_method" value="FILE" <?php if ($_REQUEST['pickup_method']=='FILE') echo ' checked '; ?>>From a local file<br>
			If this method is selected, then the XML feed will be read from a local file.<br>
			Path to XML file: <input size="70" type="text" name="feed_filename" value="<?php echo jb_escape_html($_REQUEST['feed_filename']); ?>"> eg. <?php echo dirname(__FILE__);?>
			</td>
		</tr>
		<tr bgcolor="#ffffff" >
			<td bgcolor="#eaeaea"><b>Fetch using FTP</b></td>
			<td>  <input type="radio" name="pickup_method" value="FTP" <?php if ($_REQUEST['pickup_method']=='FTP') echo ' checked '; ?>>From a remote FTP server<br>
			If this method is selected, then the XML feed will be fetched from another server via FTP.<br>
			Hostname: <input size="20" type="text" name="ftp_host" value="<?php echo jb_escape_html($_REQUEST['ftp_host']); ?>"> <br>
			Username: <input size="15" type="text" name="ftp_user" value="<?php echo jb_escape_html($_REQUEST['ftp_user']); ?>"> <br>
			Password: <input size="15" type="password" name="ftp_pass" value="<?php echo jb_escape_html($_REQUEST['ftp_pass']); ?>"> <br>
			Path to xml file: <input size="70" type="text" name="ftp_filename" value="<?php echo jb_escape_html($_REQUEST['ftp_filename']); ?>"> eg. /home/user/export.xml
			</td>
		</tr>

		<tr bgcolor="#ffffff" >
			<td  bgcolor="#eaeaea"><b>Schedule on Cron:</b></td>
			<td><input type="radio" name="cron" <?php if ($_REQUEST['cron']=='Y')  { echo ' checked '; } ?> value="Y" > - Yes, schedule the feed to import automatically every hour. (For URL, File or FTP fetching methods)<br>
				<input type="radio" name="cron" <?php if ($_REQUEST['cron']!='Y')  { echo ' checked '; } ?> value="N"> - No (The feed importing process can be run manually, or select this option if the feed is using the Server Push pickup method)</td>
		</tr>
		
		
	</table>
	<input type="submit"  name="submit_feed" value="Submit">
	</form>
	<?php



}
#########################################

function jb_list_xml_import_feeds() {

	$sql = "SELECT * FROM xml_import_feeds";
	$result = JB_mysql_query($sql) or die(MYSQL_ERROR());

	$feed_id = (int) $_REQUEST['feed_id'];

	if (mysql_num_rows($result)>0) {

		?>
	<small>(XML Import patch v2.0)</small>
		<table border=0 cellSpacing="1" cellPadding="3" bgColor="#d9d9d9"  >
		<tr bgColor="#eaeaea">
			<td><b>Feed Id</b></td>
			<td><b>Feed Name</b></td>
			<td><b>Description</b></td>
			<td><b>IP Allow</b></td>
			<td><b>Status</b></td>
			<td><b>Pickup Method</b></td>
			<td><b>Action</b></td>
		</tr>

		<?php
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {


			if ((!$_REQUEST['feed_id']) || ($_REQUEST['feed_id']==$row['feed_id'])) {
				$row['status'] = jb_xml_import_update_status($row);
				
			}


			?>
			<tr bgcolor="<?php echo ($row['feed_id']==$_REQUEST['feed_id']) ? '#FFFFCC' : '#ffffff'; ?>">
				<td><?php echo $row['feed_id'];?></td>
				<td><a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=edit_feed&feed_id=<?php echo $row['feed_id'];?>"><?php echo jb_escape_html($row['feed_name']);?></a></td>
				<td><?php echo jb_escape_html($row['description']);?></td>
				<td><?php echo jb_escape_html($row['ip_allow']);?></td>
				<td><?php //echo jb_escape_html($row['status']);
				if ($row['status']=='READY') {
					echo '<font color="green"><b>Ready to import</b></font>';
				}
				if ($row['status']=='NEW_SAMPLE') {
					echo '<br><a href="'.$_SERVER['PHP_SELF'].'?action=setupstruct&feed_id='.jb_escape_html($row['feed_id']).'" style="color:maroon; font-weight: bold;">Please setup feed structure!</a>';
				}
				if ($row['status']=='SET_FIELDS') {
					echo '<br><a href="'.$_SERVER['PHP_SELF'].'?action=setupfields&feed_id='.jb_escape_html($row['feed_id']).'" style="color:maroon; font-weight: bold;">Please map your fields!</a>';
				}
				?></td>
				<td><?php echo jb_escape_html($row['pickup_method']);?></td>
				<td nowrap><?php if (($row['status']=='READY') && ($row['pickup_method']!='POST')) { echo '<A href="'.htmlentities($_SERVER['PHP_SELF']).'?action=fetch&feed_id='.$row['feed_id'].'">Fetch'; }?><?php if (($row['status']=='READY') && ($row['pickup_method']!='POST')) { echo '</A> |';} ?> <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=setupstruct&feed_id=<?php echo jb_escape_html($row['feed_id']); ?>">Set Structure</a> | <a href="<?php echo $_SERVER['PHP_SELF'];?>?action=setupfields&feed_id=<?php echo jb_escape_html($row['feed_id']); ?>">Map Fields</a> |
				<a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=edit_feed&feed_id=<?php echo $row['feed_id'];?>"><img border=0 src='edit.gif'></a> &nbsp;<a  href="<?php echo  htmlentities($_SERVER['PHP_SELF']);?>?action=del_feed&feed_id=<?php echo $row['feed_id'];?>" onclick="if (!confirmLink(this, 'Delete, are you sure?')) return false;" ><img border=0 src="delete.gif" ></a><br>
				<?php

				if (($row['status']=='READY') && ($row['pickup_method']=='POST')) {

					if ($row['feed_key']!='') {
						$key = '&key='.$row['feed_key'];
					}

					?>
					Pickup URL: <input style='font-size:11px' onfocus="this.select()" type="text" size='70' value="<?php echo JB_escape_html(JB_BASE_HTTP_PATH.'jb-xml-pickup.php?feed_id='.$row['feed_id'].$key); ?>">
				<?php

				}

				?>
				</td>

			</tr>

			<?php

			$row = array();
				

		}

	}

	?>

	</table>
	<?php


}

//////////////////////////////

function jb_display_field_setup_form($load_fmd=true) {
	$feed_id = (int) $_REQUEST['feed_id'];
	$feed = JB_XMLIMP_load_feed_row($feed_id);
	
	if ($load_fmd) {
		// prefill the $_REQUEST[] with all the options from the database
		$feed['FMD']->fillRequestFromOptions();
	} else {
		$feed['FMD']->fillOptionsFromRequest();
	}

	
	?>

	<h3>Map fields</h3>

	<?php

	//$sql = "SELECT * FROM form_fields WHERE form_id=1 anD field_type!='BLANK' AND field_type != 'SEPERATOR' ORDER BY section, list_sort_order";

	$sql = "SELECT *, t1.field_label AS FLABEL FROM form_field_translations as t1, form_fields as t2 WHERE t2.form_id=1 AND t2.field_id=t1.field_id AND field_type!='BLANK' AND field_type != 'SEPERATOR' AND lang='".JB_escape_sql($_SESSION['LANG'])."' order by section, field_sort";


	$result = jb_mysql_query($sql);

	?>

	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	<input type="hidden" name="feed_id" value="<?php echo htmlentities($feed_id);?>">
	<input type="hidden" name="action" value="setupfields">
	<input type="hidden" name="seq" value="<?php echo $feed["FMD"]->seq; ?>">

	<table  border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9"  >

		<tr bgColor="#eaeaea">
			<td colspan="3" style="background-color: #5296DE; color:white;"><b>Job Importing - Account Options</b></td>
		</tr>
		<?php $_REQUEST['account_create'] = ($_REQUEST['account_create']==false)? 'ONLY_DEFAULT': $_REQUEST['account_create'];
		
		?>
		<tr bgcolor="white">
			<td colspan="1"><span style="font-weight: bold; font-size: 10pt">How to associate the jobs with employer's accounts?</span></td>
			<td colspan="2"><input type="radio" name="account_create" value="IMPORT_REJECT" <?php echo ($_REQUEST['account_create']=='IMPORT_REJECT')? 'checked':''; ?> > Insert using the employer's account details provided with the feed. Reject if a user/pass does not authenticate<br>
			<input type="radio" name="account_create" value="IMPORT_DEFAULT" <?php echo ($_REQUEST['account_create']=='IMPORT_DEFAULT')? 'checked':''; ?>> Insert using the employer's account, but insert using the <b>default username</b> if user/pass do not authenticate<br>
			<input type="radio" name="account_create" value="IMPORT_CREATE" <?php echo ($_REQUEST['account_create']=='IMPORT_CREATE')? 'checked':''; ?>> Insert using the employer's username, create a new account from the account data present in the feed. Allows blank passwords.<br>
			<input type="radio" name="account_create" value="ONLY_DEFAULT" <?php echo ($_REQUEST['account_create']=='ONLY_DEFAULT')? 'checked':''; ?>> Always import the jobs under the <b>default username.</b> Allows blank passwords.<br>
			
			<b>Default Username</b><span class="is_required_mark">*</span>: <input type="text" size="20" name="default_user" value="<?php echo jb_escape_html($_REQUEST['default_user'])?>"><br>
			
			</td>
		</tr>
		<tr bgcolor="white">
			<td colspan="1"><span style="font-weight: bold; font-size: 10pt">How many credits to deduct?</span></td>
			<td colspan="2"><input type="text" name="deduct_credits" size="5" value="<?php if ($_REQUEST['deduct_credits']==false) {$_REQUEST['deduct_credits']=0;} echo $_REQUEST['deduct_credits']; ?>">
			</td>
		</tr>
		<tr bgcolor="white">
			<td colspan="1"><span style="font-weight: bold; font-size: 10pt">Account details</span></td>
			<td colspan="2"><span class="is_required_mark">*</span>If you selected any of the first three options above, then you will need to setup your account data fields: (The following are required: Username, password, Email, First name, Last name)<br>
			<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
				<tr bgcolor="white">
					<td>Username:<span class="is_required_mark">*</span></td>
					<td><select <?php if ($_REQUEST['ac_Username']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> type="select" name="ac_Username">
						<option value="">[Select Field]</option>
						<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['ac_Username']); ?>
						</select>
					</td>
				</tr>
				<tr bgcolor="white">
					<td>Password:<span class="is_required_mark">*</span></td>
					<td><select <?php if ($_REQUEST['ac_Password']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> type="select" name="ac_Password">
						<option value="">[Select Field]</option>
						<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['ac_Password']); ?>
						</select> <input type="checkbox" name="pass_md5" <?php if ($_REQUEST['pass_md5']=='Y') echo ' checked '; ?> value="Y">Passwords are encrypted using MD5 Hash
					</td>
				</tr>
				<tr bgcolor="white">
					<td>Account Email:<span class="is_required_mark">*</span></td>
					<td><select <?php if ($_REQUEST['ac_Email']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> type="select" name="ac_Email">
						<option value="">[Select Field]</option>
						<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['ac_Email']); ?>
						</select>
					</td>
				</tr>
				<tr bgcolor="white">
					<td>First Name:<span class="is_required_mark">*</span></td>
					<td><select type="select" <?php if ($_REQUEST['ac_FirstName']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> name="ac_FirstName">
						<option value="">[Select Field]</option>
						<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['ac_FirstName']); ?>
						</select>
					</td>
				</tr>
				<tr bgcolor="white">
					<td>Last Name:<span class="is_required_mark">*</span></td>
					<td><select <?php if ($_REQUEST['ac_LastName']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> type="select" name="ac_LastName">
						<option value="">[Select Field]</option>
						<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['ac_LastName']); ?>
						</select>
					</td></tr>
				<tr bgcolor="white">
					<td>Company Name:</td>
					<td><select <?php if ($_REQUEST['ac_CompName']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> type="select" name="ac_CompName">
						<option value="">[Select Field]</option>
						<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['ac_CompName']); ?>
						</select>
					</td>
				</tr>

				<?php

				//$sql = "SELECT * FROM form_fields WHERE form_id=4 AND field_type!='BLANK' AND field_type != 'SEPERATOR'  ";
				
	$sql = "SELECT *, t1.field_label AS FLABEL FROM form_field_translations as t1, form_fields as t2 WHERE t2.form_id=4 AND t2.field_id=t1.field_id AND field_type!='BLANK' AND field_type != 'SEPERATOR' AND lang='".JB_escape_sql($_SESSION['LANG'])."' order by section, field_sort ";

				$result2 = jb_mysql_query($sql);

				if (mysql_num_rows($result2)>0) {

					while ($ac_row=mysql_fetch_array($result2)) {
						?>
						<tr bgcolor="white">
							<td><?php echo $ac_row['FLABEL'];?>:</td>
							<td><select <?php if ($_REQUEST['ac_'.$ac_row['field_id']]!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> type="select" name="ac_<?php echo $ac_row['field_id']; ?>">
								<option value="">[Select Field]</option>
								<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['ac_'.$row['field_id']]); ?>
								</select>
							</td>
						</tr>
						<?php
					}


				}

				?>
					
			</table>
			
			</td>
		</tr>
		<tr bgColor="#eaeaea">
			<td colspan="3" style="background-color: #5296DE; color:white;"><b>Job Importing - Feed Commands</b></td>
		</tr>
		<tr bgcolor="white">
			<td colspan="1"><span style="font-weight: bold; font-size: 10pt">Command field</span></td>
			<td colspan="2"><select <?php if ($_REQUEST['command_field']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> style="font-size: 10pt" type="select" name="command_field">
				<option  value="">[Select Field]</option>
				<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['command_field']); ?>
				</select> - Does the feed have a field which tells the system what to do with the data? eg a command may be Add, Delete, Update. If no field is selected here, then all records are assumed as to be added.
			</td>
		</tr>

		<tr bgColor="#eaeaea">
			<td colspan="1" bgColor="white"></td>
			<td colspan="1"><b>Command</b></td>
			<td colspan="1"><b>Command name</b></td>
		</tr>
		<?php

			if ($_REQUEST['insert_command']==false) {
				$_REQUEST['insert_command'] = 'Add';
			}
			if ($_REQUEST['update_command']==false) {
				$_REQUEST['update_command'] = 'Update';
			}
			if ($_REQUEST['delete_command']==false) {
				$_REQUEST['delete_command'] = 'Delete';
			}

		?>
		<tr bgcolor="white">
			<td colspan="1"></td>
			<td colspan="1">Insert a new job</td>
			<td colspan="1">Look for this command: <input type="text" name="insert_command" value="<?php echo jb_escape_html($_REQUEST['insert_command']); ?>"></td>
		</tr>
		<tr bgcolor="white">
			<td colspan="1"></td>
			<td colspan="1">Update existing job</td>
			<td colspan="1">Look for this command: <input type="text" name="update_command" value="<?php echo jb_escape_html($_REQUEST['update_command']); ?>"></td>
		</tr>
		<tr bgcolor="white">
			<td colspan="1"></td>
			<td colspan="1">Delete existing job</td>
			<td colspan="2">Look for this command: <input type="text" name="delete_command" value="<?php echo jb_escape_html($_REQUEST['delete_command']); ?>"></td>
		</tr>

		<tr bgColor="#eaeaea">
			<td colspan="3" style="background-color: #5296DE; color:white;"><b>Job Importing - Special Attributes</b></td>
		</tr>

		<tr bgcolor="white">
			<td colspan="1"><b>Application URL field</b></td>
			<td colspan="2"><select <?php if ($_REQUEST['app_url']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> style="font-size: 10pt" type="select" name="app_url">
				<option value="">[Select Field]</option>
				<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['app_url']); ?>
				</select>
				<br>When the 'apply' button is pressed, Jamit will redirect the candidate to the 'Application URL'.
				<br>If no application URL present, Jamit should: <input type="radio" value="O" name="default_app_type" <?php if ($_REQUEST['default_app_type']=='O') echo ' checked '; ?>> <i>Accept applications for that posting on your site<i> or, <input type="radio" name="default_app_type" value="N" <?php if (!$_REQUEST['default_app_type'] || $_REQUEST['default_app_type']=='N') echo ' checked '; ?>> <i>Do not display the 'apply' button for that posting</i>
			</td>
		</tr>
	
		<tr bgcolor="white">
			<td colspan="1"><b>GUID</b><span class="is_required_mark">*</span> - Global Unique ID</td>
			<td colspan="2"><select <?php if ($_REQUEST['guid']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> style="font-size: 10pt" type="select" name="guid">
				<option value="">[Select Field]</option>
				<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['guid']); ?>
				</select> (This is a Global Unique Identifier for the job post. This ID is not just local, but used accross all possible job boards and databases. A URL is an excellent choice for a GUID.)
			</td>
		</tr>

		<tr bgcolor="white">
			<td colspan="1"><b>Post Date</b></td>
			<td colspan="2"><select <?php if ($_REQUEST['post_date']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> style="font-size: 10pt" type="select" name="post_date">
				<option value="">[Select Field]</option>
				<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['post_date']); ?>
				</select> (Date and Time, Formatted to the specification of <a href="http://www.faqs.org/rfcs/rfc2822">RFC 2822</a> or YYYY-MM-DD. If date is invalid or not selected, the job will be imported with the current date and time.)
			</td>
		</tr>

		

		<tr bgcolor="white">
			<td colspan="1"><b>Post Mode</b></td>
			<td colspan="2"><select <?php if ($_REQUEST['post_mode']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> style="font-size: 10pt" type="select" name="post_mode">
				<option value="">[Select Field]</option>
				<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['post_mode']); ?>
				</select> (Optional. This field represents the <i>post_mode field</i>. It can have one of the following values: 'free', 'normal', 'premium'. If not mapped, the post will be posted as 'normal')
			</td>
		</tr>

		<tr bgcolor="white">
			<td colspan="1"><b>Approval Status</b></td>
			<td colspan="2"><select <?php if ($_REQUEST['approved']!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> style="font-size: 10pt" type="select" name="Approval">
				<option value="">[Select Field]</option>
				<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['approved']); ?>
				</select> (Optional. This field represents the <i>approved</i> field. It can have one of the following values: 'Y' for approved posts or 'N' for not approved posts. If not mapped, the post will be posted as: <select style="font-weight: bold" name="default_approved" <?php if ($_REQUEST['default_approved']=='Y') echo 'selected'; ?>><option value="Y">'Y - Approved'</option><option value="N" <?php if ($_REQUEST['default_approved']=='N') echo 'selected'; ?> >'N - Not Approved'</option></select>)
			</td>
		</tr>


		<tr><td colspan="3" style="background-color: #5296DE; color:white;"><b>Jobs Feed - Map your fields to the XML attributes<b></td></tr>
		
		<tr bgColor="#eaeaea">
			<td><b>Name</b></td>
			<td><b>XML Field</b></td>
			<td><b>Options</b></td>
			
		</tr>
		


		<?php

		$code = "\t//The following are examples showing how to set the data\n".
				"\t//structure with custom values. set_data_value() takes 3 arguments:\n".
				"\t// (string) value, (string) field_id, and optionally (int) form_id.\n";

		$code2 = "//\tThe Feed Meta Data object is accessable like this:\n";

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			

			?>

			<tr bgcolor="white">

				<td>
					<span style="font-weight: bold; font-size: 10pt"><?php echo $row['FLABEL'];?><?php if ($row['is_required']=='Y') { echo '<span class="is_required_mark">*</span>';} ?></span> <?php echo $row['field_type'].' (#'.$row['field_id'];?>)
				</td>
				<td width="10%">
					<select <?php if ($_REQUEST['xml_element_'.$row['field_id']]!='') { ?> style="color:#008080; font-weight: bold" <?php } ?> style="font-size: 10pt" type="select" name="xml_element_<?php echo $row['field_id'];?>">
					<option value="">[Select Field]</option>
					<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['xml_element_'.$row['field_id']]); ?>
					</select>
				</td>
				<td>

				<div style="font-size: 8pt;padding-left:50px;padding-right:auto">
		
					Validate? <?php

					JB_XMLIMP_echo_validation_select('validate'.$row['field_id'], $_REQUEST['validate'.$row['field_id']]);
					?>
					
					<?php


					if (($row['field_type']=='MSELECT') || ($row['field_type']=='RADIO') || ($row['field_type']=='SELECT') || ($row['field_type']=='CHECK')) {

						if ($_REQUEST['code_mode'.$row['field_id']]==false) {
							$_REQUEST['code_mode'.$row['field_id']] = 'ADD_NEW';
						}
						?><br>
						
						<span style="color: black; font-size:10pt">If the imported value does <b>not exist</b> as an <b>option</b> in your database, then:</span><br>
						<input type="radio" name="code_mode<?php echo $row['field_id'];?>" value="ADD_NEW" <?php if ($_REQUEST['code_mode'.$row['field_id']]=='ADD_NEW') echo 'checked'; ?> > - Add the value as a new option, using first three letters as the code<br>
						<input type="radio" name="code_mode<?php echo $row['field_id'];?>" value="ADD_PAIR" <?php if ($_REQUEST['code_mode'.$row['field_id']]=='ADD_PAIR') echo 'checked'; ?> > - Add the value as a new option, using <select style="font-weight: bold" type="select" name="code_pair<?php echo $row['field_id'];?>">
					<option value="">[Select Field]</option>
					<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['code_pair'.$row['field_id']]); ?>
					</select> for the code.<br>
						<input type="radio" name="code_mode<?php echo $row['field_id'];?>" value="ERROR" <?php if ($_REQUEST['code_mode'.$row['field_id']]=='ERROR') echo 'checked'; ?> > - Throw an error & skip the whole record<br>
						<input type="radio" name="code_mode<?php echo $row['field_id'];?>" value="IGNORE" <?php if ($_REQUEST['code_mode'.$row['field_id']]=='IGNORE') echo 'checked'; ?> > - Don't do anything, import anyway<br>

						<?php

					}elseif ($row['field_type']=='CATEGORY') {

						if ($_REQUEST['cat_mode'.$row['field_id']]==false) {
							$_REQUEST['cat_mode'.$row['field_id']] = 'ADD_MATCH';
						}

						?><br>
						<span style="color: black; font-size:10pt;">If the imported value does <b>not exist</b> as a <b>category</b> on your database, then:</span><br>
						<input type="radio" name="cat_mode<?php echo $row['field_id'];?>" value="ADD_NEW" <?php if ($_REQUEST['cat_mode'.$row['field_id']]=='ADD_NEW') echo 'checked'; ?> > - Add the value as a new category, under the category of: 
						<select style="font-weight: bold" name="parent_category<?php echo $row['field_id'];?>">
						<?php JB_category_option_list2(0, $_REQUEST['parent_category'.$row['field_id']], 1); ?>
						</select>
						<br>
						<input type="radio" name="cat_mode<?php echo $row['field_id'];?>" value="ADD_MATCH" <?php if ($_REQUEST['cat_mode'.$row['field_id']]=='ADD_MATCH') echo 'checked'; ?> > - Attempt to match the category name with text from <select style="font-weight: bold" type="select" name="cat_match<?php echo $row['field_id'];?>">
					<option value="">[Select Field]</option>
					<?php JB_XMLIMP_echo_element_option_list($feed, $_REQUEST['cat_match'.$row['field_id']]); ?>
					</select><br>
						<input type="radio" name="cat_mode<?php echo $row['field_id'];?>" value="ERROR" <?php if ($_REQUEST['cat_mode'.$row['field_id']]=='ERROR') echo 'checked'; ?> > - Throw an error & skip the whole record<br>
						<input type="radio" name="cat_mode<?php echo $row['field_id'];?>" value="IGNORE" <?php if ($_REQUEST['cat_mode'.$row['field_id']]=='IGNORE') echo 'checked'; ?> > - Don't do anything, import anyway<br>
						<?php
					} else {
						if ($row['field_type']=='EDITOR') {
							if ($_REQUEST['allow_html'.$row['field_id']]=='') {
								$_REQUEST['allow_html'.$row['field_id']] = 'Y';
							}
						}
?>						<br>
						or, <input type="checkbox" name="ignore<?php echo $row['field_id'];?>" <?php if ($_REQUEST['ignore'.$row['field_id']]=='Y') { echo ' checked '; } ?> value="Y" style="font-size: 8pt"> Ignore field & Replace with: <input style="font-size: 8pt" name="replace<?php echo $row['field_id'];?>" value="<?php echo jb_escape_html($_REQUEST['replace'.$row['field_id']]); ?>" size="20" type="text"><br>
						<input type="checkbox" name="allow_html<?php echo $row['field_id'];?>" value="Y"  <?php if ($_REQUEST['allow_html'.$row['field_id']]=='Y') echo 'checked'; ?> > - Allow limited HTML<br>
<?php

					}
					
				?>
				</div>
				</td>
				
			</tr>

			<tr bgColor="#EFF3FF">
				<td colspan="3">&nbsp;</td>
			</tr>

			<?php

				if ($_REQUEST['xml_element_'.$row['field_id']]) {
					$tag = ", <<-- mapped to <".$_REQUEST['xml_element_'.$row['field_id']].">";
				} else {
					$tag ='';
				}


			$field_list .= "- Field id:".$row['field_id'].", Label: '".$row['FLABEL']."', Field Type:".$row['field_type']." $tag \n";

		}

		?>

		<tr><td colspan="3" bgcolor="white">
		<input type="submit" name="submit_field_setup" value="Save" style="font-size:14pt;">
		</td>
		</tr>
		<tr><td colspan="3">
				Advanced: Summary of field mappings. The following box displays a summary of the fields and which elements they have been mapped to thus far.
				<textaREA rows="5" style="width:100%; font-size:10px"><?php echo htmlentities($field_list); ?></textaREA>
			</td>
		</tr>

	</table>
	<?php



}

// matthew robinson is a wanker.

function JB_XMLIMP_validate_field_setup_form() {

	if (($_REQUEST['account_create']=='ONLY_DEFAULT') || ($_REQUEST['account_create']=='IMPORT_DEFAULT')) {
		if (trim($_REQUEST['default_user'])==false) {
			$error .= "- Please specify a valid default username<br>";
		}
	}

	if ($_REQUEST['account_create']!='ONLY_DEFAULT') {
		if ($_REQUEST['ac_Username']==false) {
			$error .= "- Account details: Please select the field from which to import the usernames from<br>";
		}
		if ($_REQUEST['ac_Password']==false) {
			$error .= "- Account details: Please select the field from which to import the passwords from<br>";
		}
		if ($_REQUEST['ac_Email']==false) {
			$error .= "- Account details: Please select the field from which to import the emails from<br>";
		}
		if ($_REQUEST['ac_FirstName']==false) {
			$error .= "- Account details: Please select the field from which to import the first names from<br>";
		}
		if ($_REQUEST['ac_LastName']==false) {
			$error .= "- Account details: Please select the field from which to import the last names from<br>";
		}

	}

	//if ($_REQUEST['app_url']==false) {
	//	$error .= "- Application URL: Please select the field from which to import the application URLs from<br>";
	//}
	if ($_REQUEST['guid']==false) {
		$error .= "- GUID: Please select the field from which to import the Global Unique Identifier from<br>";
	}

	// now look through the fields which are required

	$sql = "SELECT * FROM form_fields WHERE form_id=1 anD field_type!='BLANK' AND field_type != 'SEPERATOR' ORDER BY section, field_sort";
	$result = jb_mysql_query($sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		if ($_REQUEST['cat_mode'.$row['field_id']]=='ADD_MATCH') {
			if ($_REQUEST['cat_match'.$row['field_id']]=='') {
				$error .= '- Category ['.jb_escape_html($row['field_label'])."]: Please select a field from the drop-down list, 'Attempt to match the category name with text from'... <br>";
			}
		}
		if ($_REQUEST['cat_mode'.$row['field_id']]=='ADD_NEW') {
			if ($_REQUEST['parent_category'.$row['field_id']]=='') {
				$error .= '- Category ['.jb_escape_html($row['field_label'])."]: Please select a field from the drop-down list, 'Add the value as a new category, under the category of:'... <br>";
			}
		}

		if ($row['is_required']=='Y') {

			if (($_REQUEST['xml_element_'.$row['field_id']]=='') && ($_REQUEST['ignore'.$row['field_id']]=='')) {
				$error .= "- ".jb_escape_html($row['field_label']).": This field is required by your system, please select the XML field to import from. If there is no corresponding field, you can check 'Ignore field & replace with...'<br>";
			}


		}


	}


	

	return $error;


}

function JB_XMLIMP_echo_validation_select($field_id, $value) {

	?>
	<select style="font-size: 8pt;" name="<?php echo $field_id;?>">
		<option value="">No validation needed</option>
		<option value="not_blank" <?php if ($value=='not_blank') echo ' selected ';?>>Not blank</option>
		<option value="alphanumeric" <?php if ($value=='alphanumeric') echo ' selected ';?>>Alphanumeric</option>
		<option value="numeric" <?php if ($value=='numeric') echo ' selected ';?>>Numeric</option>
		<option value="email" <?php if ($value=='email') echo ' selected ';?>>Email</option>
		<option value="currency" <?php if ($value=='currency') echo ' selected ';?>>Currency</option>
		<option value="url" <?php if ($value=='url') echo ' selected ';?>>URL</option>
	</select>
	<?php


}

///////////////////////

function JB_XMLIMP_echo_element_option_list(&$feed, $selected) {

	$parser = new xmlFeedFieldParser($feed['xml_sample']);

	foreach ($parser->data as $key=>$val) {

		if (strpos($key, $feed['FMD']->seq)!==false) {
			$opt = str_replace($feed['FMD']->seq.'|', '', $key);
			if ($key==$selected) {
				$sel = ' selected ';
			} else {
				$sel = '';
			}
			echo '<option style="color:#008080; font-weight:bold" '.$sel.' value="'.htmlentities($key).'">'.htmlentities($opt).'</option>';

		}

	}
	reset ($parser->data);




}

///////////////////

function jb_display_structure_setup_form($feed_id) {

	// load the sample

	$sql = "SELECT xml_sample from xml_import_feeds where feed_id='".jb_escape_sql($feed_id)."'";
	$result = jb_mysql_query($sql);
	$row = mysql_fetch_row($result);

	?>
	<h3>Setup feed structure</h3>
	<P>- Please identify the Sequence Element, ie. The element which conatins the job post data structure. Click on the radio button next to the opening XML tag, and then click 'Submit'<br>The importing tool will loop through each Sequence Element as it imports the data from your feed.</p>
	<p>If you want to change the structure, please go to <a href="xmlimport.php?action=edit_feed&feed_id=<?php echo $feed_id;?>">edit the feed settings</a> and upload a fresh <b>XML Sample file</b>.</p>

	<div style="display:none; position:absolute; background-color:green" id="v_line">|</div>
	<FORM method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF'])?>">

	<input type="hidden" name="feed_id" value="<?php echo jb_escape_html($_REQUEST['feed_id']);?>">

	<?php

	$xmlFeedForm = new xmlFeedStructForm($row[0]);

	?>
	<p>
	<input type="submit" name="set_sequence" value="Submit">
	</p>
	</FORM>

	<?php

}

///////////////////////
// load the feed in to an array
// note: $row['feed_metadata'] is always returned as an instance 
// of JB_XMLImportFeedMetaData in $row['FMD']

function JB_XMLIMP_load_feed_row($feed_id) {

	$sql = "SELECT * FROM xml_import_feeds WHERE feed_id='".jb_escape_sql($feed_id)."'";
	$result = jb_mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['feed_metadata']!='') {
		$row['FMD'] = unserialize($row['feed_metadata']);
	} else {
		$row['FMD'] = new JB_XMLImportFeedMetaData($feed_id);
	}
	$row['feed_metadata'] = '';
	
	return $row;

}

//////////////////////

function jb_xml_import_update_status(&$feed_row) {

	$status = $feed_row['status'];

	if (!isset($feed_row['FMD'])) {
		if ($feed_row['feed_metadata']!='') {
			$feed_row['FMD'] = unserialize($feed_row['feed_metadata']);
			
		} else {
			return 'NEW_SAMPLE';
		}

	}

	
	switch ($status) {

		case 'NEW_SAMPLE': // user uploaded a new xml sample
			// check to see if the sequence element was set
			if ($feed_row['FMD']->seq != false) {
				$status = 'SET_FIELDS';
			}
			
			break;

		case 'SET_FIELDS': // user can setup the fields
		
			if ($feed_row['FMD']->seq != false) {

				// check to see if the fields were set up
				$feed_row['FMD']->fillRequestFromOptions();
				$error = JB_XMLIMP_validate_field_setup_form();

				if ($error=='') {
					$status = "READY"; // ready to import
				}

			} else {
				$status = 'NEW_SAMPLE';
			}
			break;

		case 'READY': // ready to import
			if ($feed_row['FMD']->seq != false) {

				// check to see if the fields were set up
				$feed_row['FMD']->fillRequestFromOptions();
				
				$error = JB_XMLIMP_validate_field_setup_form();
				if ($error!='') {
					$status = "SET_FIELDS"; // go back to SET_FIELDS state
				}


			} else {
				$status = 'NEW_SAMPLE'; // go back to new sample
			}
			break;
			
	}

	// update the status

	$sql = "UPDATE `xml_import_feeds` SET `status`='".jb_escape_sql($status)."' WHERE feed_id='".$feed_row['feed_id']."' ";
	jb_mysql_query($sql);

	return $status;


}


/*

+========================+
| Feed Meta Data - (FMD) |
+========================+

	An instance of the FMD object is automatically created 
	when JB_XMLIMP_load_feed_row() is called
	Returs array $feed_row
	Here is a description of all the attributes and options
	of FMD.

	- sequence element
	$feed_row['FMD']->seq - string, eg. 'jamit|jobsFeed|job'


	OPTIONS

	The $feed_row['FMD'] object has the following options
	Options are fetched like this
	$my_option = $feed_row['FMD']->getOption('option_name');
	

	Option: 'job_map'

	- Get the field mappings
	(Each element is mapped to a database field, and parameters set how
	it is to be imported)


	$job_map = $feed_row['FMD']->getOption('job_map');

	structure for $job_map:

	$job_map[$field_id]['element'] - the element is, eg jamit|jobsFeed|job
	

	// how to deal if its a code typ?
	$job_map[$field_id]['code_mode'] - what to do if its a new code, possible values:
		ADD_NEW - add using first 3 chars as the code (attempt to match label, 
		ADD_PAIR - add code from another field (pair),
		IS_CODE - The value is imported as a code
		ERROR - thow an error if code does not exist, 
		IGNORE - ignore
	$job_map[$field_id]['code_pair'] - if ADD_PAIR, the field to get the codes from

	// how to deal if its a category?
	$job_map[$field_id]['cat_mode'] = what to do, possible values:
	ADD_NEW,  ADD_MATCH, ERROR, IGNORE

	$job_map[$field_id]['cat_match'] = Attempt to match the category name with text from this field_id ADD_MATCH
	$job_map[$field_id]['parent_category'] = which category to put under for ADD_NEW
	
	// how to validate?
	$job_map[$field_id]['validate'] : 'not_blank', 'alphanumeric', 'numeric', 'email', 'currency', 'url'
	$job_map[$field_id]['allow_html'] = 'allow_html'.$field_id ($field_id is number, allow html)
	//Ignore field & Replace
	$job_map[$field_id]['ignore'] = 'ignore'.$field_id; ($field_id is a number, Y then ignore and replace with $job_map[$field_id]['replace'])
	$job_map[$field_id]['replace'] = 'replace'.$field_id ($field_id is a number)

	Option: 'account_create' - 
		IMPORT_REJECT - Insert using the employer's username. Reject if a user/pass does not authenticate
		IMPORT_DEFAULT - Insert using the employer's username, but insert using the <b>default username</b> if user/pass do not authenticate, 
		IMPORT_CREATE - Insert using the employer's username, create a new account if user/pass does not authenticate, 
		ONLY_DEFAULT - Always import the jobs under the default username

	// set the default user
	Option: default_user - default user to import as if no employer account given
	
	
	// Map employers fields
	Option: account_map
	- $account_map['ac_'.$field_id]['element'], $field_id is prefixed with 'ac_'

	// set the password hash switch
	Option: 'pass_md5' - 'Y' if enabled

	// set the commands
	Option: 'command_field' // which element contains the command
	Option: 'insert_command' // Default: Add
	Option: 'update_command' // Default: Update
	Option: 'delete_command' // Default: Delete

	// application URL
	Option: 'app_url' // Application URL
	
	// GUID
	Option: 'guid' // Unique global post id

	Option: 'post_mode' // What kind of post. eg. premium, free, normal

	Option: 'post_date'

*/

class JB_XMLImportFeedMetaData {

	var $feed_id;
	var $seq;
	var $options = array();

	function JB_XMLImportFeedMetaData($feed_id) {
		$this->feed_id = $feed_id;

	}

	function setSequenceElement($element_key) {
		$this->seq = $element_key;
	}

	function setOption($key, $value) {
		$this->options[$key]=$value;

	}

	function getOption($key) {
		return $this->options[$key];
	}

	function fillOptionsFromRequest() {

		// set the sequence element

		// set how to import employers accounts
		$this->setOption('account_create', $_REQUEST['account_create']);

		// set the default user
		$this->setOption('default_user', trim($_REQUEST['default_user']));

		$this->setOption('deduct_credits', trim($_REQUEST['deduct_credits']));

		$account_map = array();
		reset($_REQUEST);
		foreach ($_REQUEST as $key=>$val) {
			if (preg_match ('#^ac_#', $key)) { // get all the keys that start with ac_
				$key = str_replace('ac_', '', $key);
				$account_map[$key]['element'] = $val;
			}
		}
		
		$this->setOption('account_map', $account_map);

		// set the password hash switch
		$this->setOption('pass_md5', $_REQUEST['pass_md5']);

		// set the commands
		$this->setOption('command_field', $_REQUEST['command_field']); // does it have a command?
		$this->setOption('insert_command', trim($_REQUEST['insert_command']));
		$this->setOption('update_command', trim($_REQUEST['update_command']));
		$this->setOption('delete_command', trim($_REQUEST['delete_command']));

		// application URL
		$this->setOption('app_url', $_REQUEST['app_url']);
		$this->setOption('default_app_type', $_REQUEST['default_app_type']);
		
		// GUID
		$this->setOption('guid', $_REQUEST['guid']);

		// post_mode
		$this->setOption('post_mode', $_REQUEST['post_mode']);

		// approval
		$this->setOption('approved', $_REQUEST['approved']);
		$this->setOption('default_approved', $_REQUEST['default_approved']);

		// post_date
		$this->setOption('post_date', $_REQUEST['post_date']);

		$job_map = array();
		reset($_REQUEST);
		foreach ($_REQUEST as $key=>$val) {
			if (preg_match ('#^xml_element_#', $key)) { // get all the keys that start with xml_element_
				$key = str_replace('xml_element_', '', $key);
				// save the mapping
				$job_map[$key]['element'] = $val;
				// how to deal if its a code?
				$job_map[$key]['code_mode'] = $_REQUEST['code_mode'.$key];
				$job_map[$key]['code_pair'] = $_REQUEST['code_pair'.$key];
				// how to deal if its a category?
				$job_map[$key]['cat_mode'] = $_REQUEST['cat_mode'.$key];
				$job_map[$key]['cat_match'] = $_REQUEST['cat_match'.$key];
				$job_map[$key]['parent_category'] = $_REQUEST['parent_category'.$key];
				
				// how to validate?
				$job_map[$key]['validate'] = $_REQUEST['validate'.$key];
				if ($_REQUEST['allow_html'.$key]=='') {
					$_REQUEST['allow_html'.$key]='N';
				}
				$job_map[$key]['allow_html'] = $_REQUEST['allow_html'.$key];
				//Ignore field & Replace
				$job_map[$key]['ignore'] = $_REQUEST['ignore'.$key];
				$job_map[$key]['replace'] = trim($_REQUEST['replace'.$key]);
			}
		}
		$this->setOption('job_map', $job_map);

		reset($_REQUEST);

	}

	function fillRequestFromOptions() {
		$_REQUEST['account_create'] = $this->getOption('account_create');
		$_REQUEST['default_user'] = $this->getOption('default_user');
		$_REQUEST['deduct_credits'] = $this->getOption('deduct_credits');
		$account_map = array();
		$account_map = $this->getOption('account_map');
		if (is_array($account_map)) {
			foreach ($account_map as $key=>$val) {
				$_REQUEST['ac_'.$key] = $account_map[$key]['element'];
			}
		}
		$_REQUEST['pass_md5'] = $this->getOption('pass_md5');
		$_REQUEST['command_field'] = $this->getOption('command_field');
		$_REQUEST['insert_command'] = $this->getOption('insert_command');
		$_REQUEST['update_command'] = $this->getOption('update_command');
		$_REQUEST['delete_command'] = $this->getOption('delete_command');
		$_REQUEST['app_url'] = $this->getOption('app_url');
		$_REQUEST['default_app_type'] = $this->getOption('default_app_type');
		$_REQUEST['guid'] = $this->getOption('guid');
		$_REQUEST['post_mode'] = $this->getOption('post_mode');
		$_REQUEST['approved'] = $this->getOption('approved');
		$_REQUEST['default_approved'] = $this->getOption('default_approved');
		$_REQUEST['post_date'] = $this->getOption('post_date');
		$job_map = array();
		$job_map = $this->getOption('job_map');
		if (is_array($job_map)) {
			foreach ($job_map as $key=>$val) {
				// mappings:
				$_REQUEST['xml_element_'.$key] = $job_map[$key]['element'];
				// and the options:
				$_REQUEST['code_mode'.$key] = $job_map[$key]['code_mode'];
				$_REQUEST['code_pair'.$key] = $job_map[$key]['code_pair'];
				$_REQUEST['cat_mode'.$key] = $job_map[$key]['cat_mode'];
				$_REQUEST['cat_match'.$key] = $job_map[$key]['cat_match'];
				$_REQUEST['parent_category'.$key] = $job_map[$key]['parent_category'];
				$_REQUEST['validate'.$key] = $job_map[$key]['validate'];
				$_REQUEST['allow_html'.$key]= $job_map[$key]['allow_html'];
				$_REQUEST['ignore'.$key]= $job_map[$key]['ignore'];
				$_REQUEST['replace'.$key]= $job_map[$key]['replace'];

			}
		}
	}


	function save() {

		$sql = "UPDATE xml_import_feeds SET `feed_metadata`='".jb_escape_sql(serialize($this))."' WHERE feed_id='".jb_escape_sql($this->feed_id)."' ";
		//echo $sql;
		jb_mysql_query($sql);
		return JB_mysql_affected_rows();

	}


}

function jb_xml_import_process($feed_id) {

	$importer = new xmlFeedImporter($fp, $feed_id);

}



?>