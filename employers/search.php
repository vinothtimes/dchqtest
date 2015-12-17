<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require "../config.php";
require_once('../include/resumes.inc.php');
include('login_functions.php'); 



JB_process_login(); 

$resume_id = (int) $_REQUEST['resume_id'];
$order_by = $_REQUEST['order_by'];
$offset = (int) $_REQUEST['offset'];
$views_stat_label = '';


$key_test_passed = false;

if (isset($_REQUEST['key']) && isset($_REQUEST['id'])) {
	$key = $_REQUEST['key'];
	$id = (int) $_REQUEST['id'];
	$sql = "SELECT * FROM `users` WHERE `ID`='".jb_escape_sql($id)."'";
	$result = JB_mysql_query($sql) or die(mysql_error());
	$c_row = mysql_fetch_array($result, MYSQL_ASSOC);
	$comp_key = substr(md5($_REQUEST['a'].$resume_id.$c_row['Password'].$id), 0,10);
	if ($key == $comp_key) {
		$key_test_passed = true;

		if (JB_FIELD_BLOCK_APP_SWITCH == 'YES') {
			// Yes - only employers subscribed to the resume database will be able to see blocked fields on the application list.
			// check subscription, is the employer subscribed?
			$subscr_block_status = JB_get_employer_view_block_status($_SESSION['JB_ID']);

			if ($subscr_block_status!='Y') {
				$key_test_passed =  false;
			}
		}
	}
}




/*

The $CAN_VIEW_RESUMES (boolean) means: If true then they can view the resume 
page. If false, then they can't view it and a 'please subscribe' message is 
shown. So 'false' will mean that they cannot view this page at all.

Now, JB_FIELD_BLOCK_SWITCH from config.php means: If YES, then some fields are 
blocked. They are blocked unless the employer has an active subscription which
then they are un-blocked.

When JB_FIELD_BLOCK_SWITCH is set to YES then we can show the resume page. This 
is because the fields are blocked, so it is safe to show the resume without 
revealing the blocked fields.


*/


$CAN_VIEW_RESUMES = false;

list
(
	$CAN_VIEW_RESUMES,  // can the user view the resumes? boolean
	$OVER_QUOTA, // is the user over their quote for resume views? boolean
	$FIRST_POST, // does the user need to post first? boolean
	$NOT_VALIDATED // is the user's account validated? boolean 
) = JBEmployer::get_resume_view_flags($_SESSION['JB_ID'], $_REQUEST['resume_id']);


if ($key_test_passed) {
	$CAN_VIEW_RESUMES = true;
}


if ($CAN_VIEW_RESUMES && isset($_REQUEST['resume_id'])) {
	$JBPage = new JBResumePage($_REQUEST['resume_id']);

}



##############################################################
# End of initialization
##############################################################

JB_template_employers_header();


?>

<script language="JavaScript" type="text/javascript">
function showDIV(obj, bool) {
	obj.setAttribute("style", "display: none", 0);
	if (bool == false) {
		//obj.style.visibility = "hidden";
		document.getElementById ('app_form_tmp').innerHTML=document.getElementById('app_form').innerHTML;
		document.getElementById ('app_form').innerHTML=document.getElementById('app_form_blank').innerHTML;
	}
	else {
	 
		obj.innerHTML =
		document.getElementById('app_form_tmp').innerHTML;
		obj.setAttribute("style", "display: block", 0);
	 
	}

   return bool;
   
}
</script>

<?php
#########################################


if (isset($_REQUEST['save'])) {

	if (JBEmployer::save_resumes($_SESSION['JB_ID'], $_REQUEST['resumes'])) {
		echo $JBMarkup->ok_msg($label['employer_resume_saved']);
	} else {
		echo $JBMarkup->error_msg($label['employer_resume_cannot_save']);
	}
}

#########################################


if ($CAN_VIEW_RESUMES == true) { // has a subscription to view resumes, or if no subscription is allowed to view resumes

	JB_display_dynamic_search_form(2);

	// Display Category tree code
	// do we have a CATEGORY type field? (field_type)

	$DynamicForm = jb_get_DynamicFormObject(2);


	foreach ($DynamicForm->get_tag_to_field_id() as $field) {

		// If it does have a CATEGORY, display the category tree and
		// break out from the loop
		if (($field['field_type']=='CATEGORY') && ($_REQUEST['action']!='search') && ($_REQUEST['resume_id']==false)) {
	
			?>
				<div style="padding-bottom:3.5em; text-align:left">
					<div style="float:left; ">
						<span class="category_name"> <?php echo jb_escape_html(JB_getCatName($_REQUEST['cat']));?></span><br>
						<span class="category_path"><?php echo JB_getPath_templated($_REQUEST['cat']);?></span>
					</div>
				<?php
				if (is_numeric($_REQUEST['cat'])) {
				
					?>
					<div style="float: right;">
						<a href="index.php"><a href="search.php"><?php echo $label['c_back2top'];?></a></a>
					</div>
					<?php
				}
				?>
				</div>

			<?php


			$categories = JB_getCatStruct($_REQUEST['cat'], $_SESSION["LANG"], 2);
			JB_display_categories($categories, JB_CAT_COLS);
			break; 
		}
	
	}



	

	##############################################\
	$is_blocked = 'N';
	switch (JB_FIELD_BLOCK_SWITCH) {

		case 'YES':
			$subscr_block_status = JB_get_employer_view_block_status($_SESSION['JB_ID']);
			
			if (($subscr_block_status=='N') && (JB_SUBSCRIPTION_FEE_ENABLED=='YES') && (!$key_test_passed)) {
				echo "<p>".$label["resume_some_fields_blocked"]."</p>";
				echo JBEmployer::JB_get_special_offer_msg();
				$is_blocked = 'Y';
			}
			break;
		case 'NO':
			break;

	

	}
	$REQUEST_FEATURE == false;

	switch (JB_RESUME_REQUEST_SWITCH) {
		
		case 'YES':

			//
			if ((JB_NEED_SUBSCR_FOR_REQUEST=='YES') ) {
				$subscr_status = JB_get_employer_subscription_status($_SESSION['JB_ID']);
				if ($subscr_status=='Active') {
					$REQUEST_FEATURE = true;
				} else {
					$REQUEST_FEATURE = false;
				}
			} else {		
				$REQUEST_FEATURE = true;
			}
			
			break;
		case 'NO':
			$REQUEST_FEATURE = false;
			break;
	}
	if ($key_test_passed) { 
		// viewing form an special that came form an application.
		if ($_REQUEST['a']=='N') {
			// Anonymous is N, turn off the request feature
			$REQUEST_FEATURE = false;
		}
		$DynamicForm->set_value('anon', $_REQUEST['a']);
	}

	if ($_REQUEST['action'] == 'search') {
		$q_string = JB_generate_q_string(2); 
	}

	
	/*


	Display the resume


	*/

	if ($_REQUEST['resume_id']!= '') {
		
		

		// The following will put all the variables in $JBPage->vars
		// in to the local scope. This includes $admin, $DynamicForm,
		// resume_id

		extract($JBPage->get_vars(), EXTR_REFS); 

		$data = &$DynamicForm->get_values();

	

		/*
		Only increment the view tally if:
		- there is enough quota, $enough_quota
		- subscriptions are enabled, ($subscr_row['can_view_resumes']=='Y')
		- the subscriber can view resumes, ($subscr_row['can_view_resumes']=='Y')
		- the resume's status is active, ($data['status']=='ACT')
		
		- fields are not blocked
		- resume not anonymous to the user
		*/

		if (($enough_quota) && 
			($subscr_row['can_view_resumes']=='Y') && 
			($data['status']=='ACT') && 
			
			($is_blocked!='Y') &&
			($data['anon']!='Y') || JB_is_request_granted($data['user_id'], $_SESSION['JB_ID'])
			) {

			// increment the view tally (if subscriptions are enabled 
			JB_increment_views_tally($_SESSION['JB_ID']);
			echo $views_stat_label; // If views_quota is > 0 then this will print something like: 1 / 10
		}

		?>
		<div style="text-align:center"><A HREF="<?php echo htmlentities(JB_get_go_back_link());?>"><?php echo $label["resume_display_go_back"];?></a></div>

		<?php

		

		if ($data['status']!=='ACT') {
			$JBMarkup->error_msg($label['employer_resume_desctive']);
		} else {


			if (($is_blocked=='N') && ($key=='') && (JB_FIELD_BLOCK_SWITCH=='NO') && (JB_RESUME_REPLY_ENABLED=='YES') && (($data['anon']!='Y') || ((JB_is_request_granted($data['user_id'], $_SESSION['JB_ID'])))) ) {
			?>

				<input type="button" name="apply"  onclick="showDIV(document.getElementById('app_form'), true); this.disabled=true;" class="form_apply_button" value="<?php echo $label['employer_search_send_email']; ?>"><p>
				<span id="app_form">
				</span>
				<span id="app_form_blank" >
				</span>
				<span id="app_form_tmp" style="display: none">
				 <iframe width="100%" height="380" frameborder="0" MARGINWIDTH="0" MARGINheight="0" VSPACE="0" HSPACE="0"  src="<?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER;?>email_iframe.php?resume_id=<?php echo $_REQUEST['resume_id']."&step=1"; ?>"></iframe>
				</span>
			
			<?php

			}

			


			if ($REQUEST_FEATURE && ($data['anon']=='Y')) {
			
				$req_status = JB_is_request_granted($data['user_id'], $_SESSION['JB_ID']);

				if ($req_status===true) {
					// request granted
					echo  "<Br>".$label["c_resume_hide_allowed"];

				} elseif ($req_status===false) {
					// request was made, it is refused or waiting
						
					echo "<p class='request_msg_sent_label'>";
					//echo "<i>".$label["c_resume_hide"]."</i>";
					echo $label["resume_display_request_sent"];
					echo "</p>";

				} else {

					

					// display a request button
					

					?>
					<p style="text-align:center">
					<i><?php echo $label["c_resume_hide"]; ?></i><br>
					<input  type="button" onclick="window.location='request.php?user_id=<?php echo jb_escape_html($data['user_id']);?>'" value="<?php echo $label["resume_display_request"]; ?>" >
					</p>
					<?php

				}

			} else {

				// resume is not hidden!


			}


			$JBPage->output();
			$JBPage->increment_hits();
		
		}

	} else {
		   
		JB_list_resumes ('EMPLOYER');
	}


} elseif ($NOT_VALIDATED) { // cannot view resumes unless the account is validated
	?>
	
	<center>
	<h3><?php echo $label['employer_cannot_resume_browse'];?></h3>
	<?php 

	$label["employer_resume_must_activate"] = str_replace ("%SITE_NAME%", JB_SITE_NAME , $label["employer_resume_must_activate"]);

	echo '<div class="explanation_note">'.$label['employer_resume_must_activate']."</div>"; ?>
	</center>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<?php

} elseif ($FIRST_POST ) {

?>

	
	<center>
	<h3><?php echo $label['employer_cannot_resume_browse'];?></h3>
	<?php 

	$label["employer_resume_must_first_post"] = str_replace ("%SITE_NAME%", JB_SITE_NAME , $label["employer_resume_must_first_post"]);

	echo '<div class="explanation_note">'.$label['employer_resume_must_first_post'].'</div>'; ?>
	</center>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>

	<?php


} elseif ($OVER_QUOTA) {



	$t = $subscr_row['quota_timestamp'];
	// calculate timestamp for 1 month in the future
	$t_next_month = mktime(date('H', $t), date('i', $t), date('s', $t), date('n', $t)+1, date('j', $t), date('Y', $t));

	
	$str = $label['employer_resume_noquota']."<br>";

	$str = str_replace("%SITE_CONTACT%", JB_SITE_CONTACT_EMAIL, $str);
	$str = str_replace("%QUOTA%", $subscr_row['V_QUOTA'], $str);
	$str = str_replace("%FROM_DATE%", date(JB_DATE_FORMAT, $t), $str);
	$str = str_replace("%TO_DATE%",  date(JB_DATE_FORMAT, $t_next_month), $str);
	$str = str_replace("%VIEWS%", $sub_row['views_quota'], $str);

	echo '<h3>'.$label['employer_resume_noquota_head'].'</h3>';
	$JBMarkup->error_msg($sre); 
	echo '<p>'.$label['employer_resume_more_details'].'</p>';
	
	
	
} else {

?>
	<p>&nbsp;</p>
	<center>
	<h3><?php echo $label['employer_resume_browse'];?></h3>
	<?php 

	$label["employer_resume_must_sub"] = str_replace ("%SITE_NAME%", JB_SITE_NAME , $label["employer_resume_must_sub"]);

	echo '<div class="explanation_note">'.$label['employer_resume_must_sub'].'</div>'; ?><p>
	<?php
	$offer_active_msg = JBEmployer::JB_get_special_offer_msg();
	echo $offer_active_msg;
	?>
	<a href="subscriptions.php"><IMG src="<?php echo JB_THEME_URL; ?>images/<?php echo $label['subscribe_now_button_img'];?>" width="187" height="41" border="0" alt=""></a>
	</center>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<?php

}

?>


<?php 

JB_template_employers_footer(); 

?>