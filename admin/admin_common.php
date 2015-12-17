<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

if (!defined('MAIN_PHP')) define('MAIN_PHP', 0);

if (JB_DEMO_MODE=='YES') $_SESSION['ADMIN'] = 1;


$JB_ADMIN_PASSWORD = JB_ADMIN_PASSWORD;


// security feature: wait 60 sec after 3 failed attempts
if ($_SESSION['ADMIN']==false) {

	// load in the vars
	if (function_exists('jb_get_variable')) {
		$retries = jb_get_variable('ADMIN_PASS_FAILED');
		$failed_t = jb_get_variable('ADMIN_PASS_FAILED_TIME');
	} else {
		$retries=0; $failed_t=0;
	}

	if ($failed_t>0) {
		$diff = time() - $failed_t;
		if ($diff > 60) { 
			
			// reset timer after 60 sec and 3 failed login attempts
			$sql = "UPDATE jb_variables SET `val`='' WHERE `key`='ADMIN_PASS_FAILED_TIME' ";
			JB_mysql_query($sql);

			// reset failed login counter
			$sql = "UPDATE jb_variables SET `val`='' WHERE `key`='ADMIN_PASS_FAILED' ";
			JB_mysql_query($sql);

		} else {
			echo "Please wait another ".(60-$diff)." seconds before trying again.";
			die();

		}

	}

}

#copyright Jamit Software 2005-2009, www.jamit.com

# Compare the password, log in or fail
if ((isset($_REQUEST['pass'])) && (MAIN_PHP=='1')) {
	if ($_REQUEST['pass']==JB_ADMIN_PASSWORD) {
		$_SESSION['ADMIN'] = '1';
	} else {

		// count each failed attampt.
		// print a 'Good Bye' message on 3rd unsucessful attempt
		// and record the time

		$sql = "UPDATE jb_variables SET `val`=`val`+1 WHERE `key`='ADMIN_PASS_FAILED' ";
		JB_mysql_query($sql) ;
		if (JB_mysql_affected_rows()==0) {
			$sql = "REPLACE into jb_variables (`key`, `val`) VALUES ('ADMIN_PASS_FAILED', '1') ";
			JB_mysql_query($sql) ;
		}

		$retries++;

		if ($retries > 2) {

			echo "Good Bye.";
			$sql = "UPDATE jb_variables SET `val`='".time()."' WHERE `key`='ADMIN_PASS_FAILED_TIME' ";
			JB_mysql_query($sql);
			if (JB_mysql_affected_rows()==0) {
				$sql = "REPLACE into jb_variables (`key`, `val`) VALUES ('ADMIN_PASS_FAILED_TIME', '".time()."') ";
				JB_mysql_query($sql) ;
			}
			die();

		}


	}
}

if (!function_exists('JB_escape_html_local')) {
	function JB_escape_html_local ($str) {
		$trans = array(
			"<" => '&lt;', 
			">" => '&gt;', 
			'"' => '&quot;',
			'(' => '&#40;',
			')' => '&#41;',
			'&' => '&amp;'
			);
		return strtr($str, $trans);
	}

}

if (($_SESSION['ADMIN']=='')) {

	if (MAIN_PHP=='1') {
	?>
	<head>
	<title>Admin - <?php echo JB_escape_html_local(JB_SITE_NAME); ?> - Jamit Job Board </title>
	</head>
Please input admin password:<br>
<form method='post'>
<input type="password" name='pass'>
<input type="submit" value="OK">
</form>
	<?php

	}

	die();

}

function JB_admin_header($title, $extra_header='') {

	global $JBMarkup;
	echo $JBMarkup->get_admin_doctype();
	$JBMarkup->markup_open();

	$JBMarkup->head_open();
	
	$JBMarkup->title_meta_tag($title);
	$JBMarkup->stylesheet_link(JB_get_admin_maincss_url());
	$JBMarkup->charset_meta_tag();
	
	if ($extra_header == 'xmlimport') {		
		?>
		<script type="text/javascript">

		function selectSeqElement (name) {
			
			
			if (selectSeqElement.previousName) {
				document.getElementById(selectSeqElement.previousName).style.background='#ffffff';document.getElementById(selectSeqElement.previousName+'_end').style.background='#ffffff';
			}
			document.getElementById(name).style.background='#66FF66';document.getElementById(name+'_end').style.background='#66FF66';

			start_x = findPosX(document.getElementById(name+''));
			start_y = findPosY(document.getElementById(name+''));
			end_x = findPosX(document.getElementById(name+'_end'));
			end_y = findPosY(document.getElementById(name+'_end'));
			var v_line = document.getElementById('v_line');
			v_line.style.display='inline';
			v_line.style.left = start_x-3;
			v_line.style.top = start_y;
			v_line.style.height = end_y-start_y+"px";
			selectSeqElement.previousName=name;
		}

		function findPosX(obj)
		{
			var curleft = 0;
			if (obj.offsetParent)
			{
				while (obj.offsetParent)
				{
					curleft += obj.offsetLeft
					obj = obj.offsetParent;
				}
			}
			else if (obj.x)
				curleft += obj.x;
			return curleft;
		}

		//Taken from http://www.quirksmode.org/js/findpos.html; but modified
		function findPosY(obj)
		{
			var curtop = 0;
			if (obj.offsetParent)
			{
				while (obj.offsetParent)
				{
					curtop += obj.offsetTop
					obj = obj.offsetParent;
				}
			}
			else if (obj.y)
				curtop += obj.y;
			return curtop;
		}

		</script>

		<style>

		.XMLelement {
			font-weight: bold; 
			font-family: Courier;
			font-size: 8pt;
		}
		.is_required_mark {
			color: red;
		}
		</style>

<?php

	} elseif ($extra_header=='main_config') {
		?>

		
		<script type="text/javascript">

			function test_email_window () {

				var user = escape(document.form1.email_smtp_user.value);
				var pass = escape(document.form1.email_smtp_pass.value);

				prams = 
					'host='+document.form1.email_hostname.value+
					'&pop='+document.form1.email_pop_server.value+
					'&user='+user+
					'&pass='+pass+
					'&auth_host='+document.form1.email_smtp_auth_host.value+
					'&php3_port='+document.form1.pop3_port.value;

				window.open('test_email.php?'+prams, '', 'toolbar=no, scrollbars=yes, location=no, statusbar=no, menubar=no, resizable=1, width=800, height=500, left = 50, top = 50');

			}

			function suggest_permissions_window () {


				window.open('suggest_permissions.php', '', 'toolbar=no, scrollbars=yes, location=no, statusbar=no, menubar=no, resizable=1, width=400, height=200, left = 50, top = 50');

			}

			function fix_permissions_window () {


				window.open('fix_permissions.php', '', 'toolbar=no, scrollbars=yes, location=no, statusbar=no, menubar=no, resizable=1, width=600, height=400, left = 50, top = 50');

			}

		</script>

	<?php

	} elseif ($extra_header == 'xmlimport_iframe') {

		?>

		<script type="text/javascript">
		function scroll_iframe() {
				window.scrollBy(0,1000);
				
				var x,y;
				if (self.pageYOffset) // all except Explorer
				{
					x = self.pageXOffset;
					y = self.pageYOffset;
				}
				else if (document.documentElement && document.documentElement.scrollTop)
					// Explorer 6 Strict
				{
					x = document.documentElement.scrollLeft;
					y = document.documentElement.scrollTop;
				}
				else if (document.body) // all other Explorers
				{
					x = document.body.scrollLeft;
					y = document.body.scrollTop;
				}
				document.getElementById('status').style.top=y+'px';
				document.getElementById('status').style.left='0px';

				

				
			}

			function import_init() {

				
				setTimeout('clearInterval(document.my_interval)', 2000);
				document.getElementById('status').style.visibility='hidden';

			}

			document.my_interval = setInterval ( 'scroll_iframe()', 500 );

			

			window.onload= import_init; 

			
			

			</script>

		<?php
		
	} else {
		echo $extra_header;

	}
	?>

	<style>

		.config_form {
			
			background-color: white;
			border: 0px groove;
			width: 100%;

		}

		.config_form_heading {
			font-size: 13px;
			background-color: #e6f2ea;
			font-weight: bold;
			background:#fff url(../include/themes/default/images/grgrad.gif) repeat-x;
			text-shadow:0 1px 0 white; 

		}

		.config_form_field {
			font-size: 12px;
			background-color: #e6f2ea;
			color: black;
		}

		.config_form_label {
			font-size: 11px;
			background-color: #e6f2ea;
			color: black;
			font-weight: bold;
			width: 120;
		}

	</style>

	<?php
	$JBMarkup->head_close();
	$JBMarkup->body_open();

	

}

function JB_admin_footer() {

	global $JBMarkup;

	$JBMarkup->body_close();
	$JBMarkup->markup_close();


}

?>