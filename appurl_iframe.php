<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

ini_set('max_execution_time', 120);

define ('NO_HOUSE_KEEPING', true);
require ("config.php");
$user_id = $_SESSION['JB_ID'];
echo $JBMarkup->get_doctype();

$JBMarkup->markup_open(); // <html>
$JBMarkup->head_open(); // open the <HEAD> part

$JBMarkup->title_meta_tag($label["c_loginform_title"]);


$JBMarkup->stylesheet_link(JB_get_maincss_url());// <link> to main.css
$JBMarkup->charset_meta_tag();  // character set 
$JBMarkup->no_robots_meta_tag(); // do not follow, do not index


$JBMarkup->head_close(); // close the </HEAD> part

$JBMarkup->body_open('style="background-color:white; background-image: none;"');

require_once (dirname(__FILE__).'/'.JB_CANDIDATE_FOLDER."login_functions.php");
require_once (dirname(__FILE__)."/include/posts.inc.php");

if ($_REQUEST['username'] != '') {
	$_REQUEST['silent'] = 'yes';
	JB_validate_candidate_login(htmlentities($_SERVER['PHP_SELF']));

}

if ($_REQUEST['post_id']!='') {
	$_SESSION['app_post_id'] = (int) $_REQUEST['post_id'];
}
$post_id = (int) $_SESSION['app_post_id'];


if (((($_SESSION['JB_ID'] == '') || ($_SESSION['JB_Domain']!='CANDIDATE'))) && (JB_ONLINE_APP_SIGN_IN=='YES')) { // is the user logged in??


	JB_can_login_form(JB_BASE_HTTP_PATH."appurl_iframe.php");


} else {

	if (!is_numeric($post_id)) { die(); }

	
	$data = JB_load_post_data($post_id);



	if ((($_SESSION['JB_ID']!='') && ($_SESSION['JB_Domain']=='CANDIDATE')) || JB_ONLINE_APP_SIGN_IN!='YES') { 

		$sql = "SELECT app_id FROM applications WHERE post_id='".jb_escape_sql($post_id)."' AND user_id='".jb_escape_sql($_SESSION['JB_ID'])."' ";
		$result = JB_mysql_query ($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		if ($row['app_id']!='') {
			echo $label["app_already_applied"]."<br>";
		} else {
			// increment the application count
			$sql = "UPDATE `posts_table` SET `applications`=`applications`+1 WHERE `post_id`='".jb_escape_sql($post_id)."' ";
			JB_mysql_query ($sql) or die (mysql_error());
		}
		
		
		// load the users' resume (if one exists)
		$sql = "SELECT resume_id FROM resumes_table WHERE user_id='".jb_escape_sql($_SESSION['JB_ID'])."'";
		$resume_result = JB_mysql_query($sql) or die (mysql_error());
		$resume_row = mysql_fetch_array($resume_result, MYSQL_ASSOC);
		if ($resume_row['resume_id'] !='') {
			
			require_once (dirname(__FILE__)."/include/resumes.inc.php");
			
			$resume_data = JB_load_resume_data($resume_row['resume_id']);
		}
	

		if ($data['post_mode']!='premium') { // standard post?
			
			if ((JB_ONLINE_APP_REVEAL_STD=='YES') && ($resume_data['anon']=='Y')) { // reveal candidate's resume, even if hidden?
				JB_grant_request ($resume_data['user_id'], $data['user_id']);

			}

		} elseif ($data['post_mode']=='premium') { // premium posts?
			
			if ((JB_ONLINE_APP_REVEAL_PREMIUM=='YES') && ($resume_data['anon']=='Y')){ // reveal candidate's resume, even if hidden?
				JB_grant_request ($resume_data['user_id'], $data['user_id']);
			}
		}

		// redirect the user to a custom URL

		jb_app_redirect_script($data);



	} elseif (JB_ONLINE_APP_SIGN_IN!='YES') {

		// users can apply without logging in

		jb_app_redirect_script($data);

	}


}

function jb_app_redirect_script(&$data) {

		?>

		<script type="text/javascript">
			function js_redirect() {
				// //parent.window.location = 'http://www.cnn.com/';
				window.location = '<?php echo jb_escape_html($data['app_url']);?>';
			  }
			  window.onload = js_redirect;
		</script>

		<?php


}

$JBMarkup->body_close();

$JBMarkup->markup_close();