<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
#########################################################################################
# Employer API for the user interface-to-back-end methods.
# Note: The plan is to extend this API in the future to provide a
# JSON service for AJAX powered interfaces

class JBEmployer {

	# $resume_ids - array of resume ids to save
	# $user_id
	function save_resumes($user_id, $resume_ids) {
		$user_id = (int) $user_id;
		if (is_array($resume_ids) && (sizeof($resume_ids)>0)) {
			$i=0;
			foreach ($resume_ids as $resume_id) {		
				$resume_id = (int) $resume_id;
				$sql = "REPLACE INTO `saved_resumes` (`resume_id`, `user_id`, `save_date`) VALUES ('".jb_escape_sql($resume_id)."', '".jb_escape_sql($user_id)."', NOW())";
				jb_mysql_query($sql);
			}
			return true;
		} else {
			return false;
		}
	}

	##########################################

	function delete_saved_resumes($user_id, $resume_ids) {

		$user_id = (int) $user_id;
		if (is_array($resume_ids) && (sizeof($resume_ids)>0)) {	
			foreach ($resume_ids as $resume_id) {		
				$resume_id = (int) $resume_id;
				$sql = "DELETE FROM `saved_resumes` WHERE resume_id='".jb_escape_sql($resume_id)."' AND user_id='".jb_escape_sql($user_id)."' ";
				jb_mysql_query($sql);
			}
			return true;
		}
		return false;

	}

	##########################################

	/*

	$user_id - the employer id, who is viewing the resume / resume database


	*/

	function get_resume_view_flags($user_id, $resume_id) {

		$user_id = (int) $user_id;
		$resume_id = (int) $resume_id;

		$CAN_VIEW_RESUMES = false;  // can the user view the resumes? boolean
		$OVER_QUOTA = false; // is the user over their quote for resume views? boolean
		$FIRST_POST = false; // does the user need to post first? boolean
		$NOT_VALIDATED  = false;

		if ($user_id) {

			if (JB_SUBSCRIPTION_FEE_ENABLED=='NO') { // free resume access?

				$CAN_VIEW_RESUMES = true;
				if (JB_EM_NEEDS_ACTIVATION=='NO_RESUME') { // Must be validated to view resumes? employers who are not validated cannot view resumes
					
					$sql = "SELECT * from employers where ID='".jb_escape_sql($user_id)."'";
					$result = JB_mysql_query($sql) or die(mysql_error());
					$row = mysql_fetch_array($result, MYSQL_ASSOC);
					if ($row['Validated']=='1') {
						$CAN_VIEW_RESUMES = true;
					} else {
						
						$CAN_VIEW_RESUMES = false;
						$NOT_VALIDATED = true;
					}

				} elseif (JB_EM_NEEDS_ACTIVATION=='FIRST_POST') { // must post a job before viewing the resumes for free?
					$sql = "SELECT post_id from posts_table where user_id='".jb_escape_sql($user_id)."'";
					
					$result = JB_mysql_query($sql) or die(mysql_error());
					if (mysql_num_rows($result)>0) {
						$CAN_VIEW_RESUMES = true;

					} else {
						$NOT_VALIDATED = false; // not validated until they can post
						$CAN_VIEW_RESUMES = false;
						$FIRST_POST = true;
					}
				}			

			} else { // subscriptions enabled

				// check if subscription is active
				$subscr_row = jb_get_active_subscription_invoice($user_id);
				if ($subscr_row['can_view_resumes']=='Y') { // active subscription
					
					// - V_QUOTA is the views_quota column from `employers` table
					// - If ($user_id==true) then it means that user clicked to view the resume.

					if (($subscr_row['V_QUOTA']>-1) && ($user_id==true)) { // is a quota imposed?

						if (($subscr_row['V_QUOTA']-($subscr_row['views_quota_tally']))>0) {				
							$enough_quota = TRUE;
						}
						
						if ($enough_quota==false) { // inc $enough_quota is false which means we are over quota			
							$CAN_VIEW_RESUMES = false;
							$OVER_QUOTA = true;

						} else { // There is quota remaining
							
							$CAN_VIEW_RESUMES = true; 
							// get the quota message ready
							$views_stat_label =  $label['employer_resume_view_stat'];
							$views_stat_label = str_replace("%TALLY%", $subscr_row['views_quota_tally']+1, $views_stat_label);
							$views_stat_label = str_replace("%QUOTA%", $subscr_row['V_QUOTA'], $views_stat_label);
						} 
					} else { // views_quota is either -1 or not viewing the resume
						$CAN_VIEW_RESUMES = true; // no quota
					}
				} else {
					// special situations for when the user is not subscribed.
					// but is still can view resumes			
					$CAN_VIEW_RESUMES = JB_is_privileged_user($user_id, 'resume');			
					if (JB_FIELD_BLOCK_SWITCH=="YES") { 
						$CAN_VIEW_RESUMES = true; // Can view but some fields will be blocked
					}			
				}
			}
		} else {

			// user id (of viewer) not given

			if (JB_FIELD_BLOCK_SWITCH=="YES") { 
				$CAN_VIEW_RESUMES = true; // Can view but some fields will be blocked
			}

		}

		return (
			array(
				$CAN_VIEW_RESUMES,  // can the user view the resumes? boolean
				$OVER_QUOTA, // is the user over their quote for resume views? boolean
				$FIRST_POST, // does the user need to post first? boolean
				$NOT_VALIDATED) // is the user's account validated? boolean
			);
	}

	###########################################################################

	function display_credit_status() {

		global $label;

		if ((JB_POSTING_FEE_ENABLED == 'YES') || (JB_PREMIUM_POSTING_FEE_ENABLED == 'YES')) {
			$_FEES_ENABLED = "YES";
		}

		if ($_FEES_ENABLED == "YES") {

			if (JB_POSTING_FEE_ENABLED == "YES") {
				self::display_standard_credit_balance();
			} else {
				echo "- ".$label['credit_status_free'];
				?><br>&nbsp;&nbsp; |_
				<a href="post.php">
				<img border="0" align="middle" alt="post" src="<?php echo JB_THEME_URL; ?>images/Postit-large.gif"> <?php echo $label['credit_status_post_free'];?></a><br>
				<?php
			}

			if (JB_PREMIUM_POSTING_FEE_ENABLED == "YES") {
				self::display_premium_credit_balance();

			}

		} else {

			?>
			<p style="text-align:center;">
			<img border="0" align="middle" alt="Post" src="<?php echo JB_THEME_URL; ?>images/Postit-large.gif"> <a href="post.php">
			<?php echo $label['credit_status_post_new'];?></a><br>
			</p>
			<?php

		}

	}

	###########################################################################

	function display_standard_credit_balance () {

		global $label;

		$posts = JB_get_num_posts_remaining($_SESSION['JB_ID']);

		$str = self::get_no_std_posts_subscr_msg();

		if (($posts==0)  ) {
			?>
			- <?php echo $label['std_post_post_no_credits'].' '.$str; ?> <br>
			<?php

		} else {

			if ($posts==-1) {
				$label['std_post_post_balance'] = $label['std_post_unlimited'];
			}

			$label['std_post_post_balance'] = str_replace ("%POSTS%", $posts, $label['std_post_post_balance']);
			?>
			- <?php echo $label['std_post_post_balance']; ?> <br>
	&nbsp;&nbsp;  |_ <a href="post.php"><img border="0" align="middle" alt="post" src="<?php echo JB_THEME_URL ; ?>images/Postit-large.gif"> <?php echo $label['std_post_post']; ?></a> <br>


			<?php

		}
	}

	###########################################################################

	function display_premium_credit_balance () {

		global $label;


		$p_posts = JB_get_num_premium_posts_remaining($_SESSION['JB_ID']);

		$str = self::get_no_pr_posts_subscr_msg();

		if (($p_posts==0) ) {
			?>

			- <?php echo $label['prem_post_no_credits']; ?> [<a href="" onclick="window.open('adsinfo.php', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=600,height=600,left=50,top=50');return false;"><b><?php echo $label['prem_post_more_info']; ?></b></a>] <?php echo $str;?><br>

			<?php

		} else {

			if ($p_posts==-1) {

				$label['prem_post_balance'] = $label['prem_post_unlimited'];

			}

			$label['prem_post_balance'] = str_replace ("%P_POSTS%", $p_posts, $label['prem_post_balance']);

			?>

			- <?php echo $label['prem_post_balance']; ?>
	<br>&nbsp;&nbsp;  |_ <a href="post.php?type=premium"><img border="0" align="middle"  alt="Premium Post" src="<?php echo JB_THEME_URL; ?>images/PremiumPostit-large.gif"> <?php echo $label['prem_post_post']; ?></a> <br>


			<?php

		}
	}
	##################################################

	function JB_get_special_offer_msg() {

		global $label;

		if (((JB_POSTING_FEE_ENABLED=='YES') || (JB_PREMIUM_POSTING_FEE_ENABLED=='YES')) && (JB_SUBSCRIPTION_FEE_ENABLED=='YES') && (JB_free_posting_subscription_exists('S') || JB_free_posting_subscription_exists('P')) && (!jb_get_active_subscription_invoice($_SESSION['JB_ID']))) {
			$str = $label['subscribe_bonus_info'] ;
			return $str;
		}

	}
	##################################################

	function get_no_std_posts_subscr_msg() {

		global $label;

		if (JB_SUBSCRIPTION_FEE_ENABLED=='YES') {

			$subscr_row = jb_get_active_subscription_invoice($_SESSION['JB_ID']);

			if ($subscr_row['posts_quota']>0) {

				$str = $label['subscr_no_free_posts'];
				$new = $label['subscr_allowed_free_posts'];
				$new = str_replace('%CREDITS%', $subscr_row['posts_quota'], $new);
				$str .= " ".$new;

			}

		}

		return $str;


	}
	################################################################

	function get_no_pr_posts_subscr_msg() {

			global $label;

			if (JB_SUBSCRIPTION_FEE_ENABLED=='YES') {

			$subscr_row = jb_get_active_subscription_invoice($_SESSION['JB_ID']);

			if ($subscr_row['posts_quota']>0) {


				$new = $label['subscr_allowed_free_p_posts'];
				$new = str_replace('%CREDITS%', $subscr_row['p_posts_quota'], $new);

				$str = $label['subscr_no_free_p_posts'];
				$str .= " ".$new;

			}

		}
		return $str;


	}


	################################################################

	function get_recent_subscription_invoices($employer_id) {

		$employer_id = (int) $employer_id;
		$ret = array();

		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "SELECT * FROM `subscription_invoices` WHERE employer_id='".jb_escape_sql($employer_id)."' AND DATE_SUB('$now', INTERVAL 90 DAY) <= `invoice_date`  ORDER BY invoice_date DESC ";
		$result = JB_mysql_query ($sql);

		if (mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$ret[] = $row;
			}
		}

		return $ret;

	}

	#########################################

	function update_subscription_quota ($employer_id) {
		
		return jb_update_subscription_quota($employer_id); // This will update the subscription quotas, if the user is subscribed to the resume database.

	}


	#########################################

	function void_subscription_invoice($invoice_id, $employer_id) {

		$invoice_id = (int) $invoice_id;
		$employer_id = (int) $employer_id;
		
		return JB_void_subscription_invoice($invoice_id, $employer_id);


	}

	#############################################

	function get_active_subscription_invoice($employer_id) {
		return jb_get_active_subscription_invoice($employer_id);

	}




}


?>