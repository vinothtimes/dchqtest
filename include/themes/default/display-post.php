<?php

/*

The following variables are pre-loaded for you:
$TITLE
$POSTED_BY
$FORMATTED_DATE
$LOCATION
$DESCRIPTION


Here is how you can fetch additional variables using the template tag

$SALARY = $DynamicForm->get_template_value('SALARY_TAG');
echo $SALARY;

Where you can replace 'TEMPLATE_TAG' with any template tag.

The get_template_value(..) method will also escape any html for
you, making it safe to output in to the browser.

How to get the template tag for a field?

Have a look at Admin->Resume form, then click 'Edit Fields'. Click on the field name that you want to see.

Then, look at the 'Template Tag' setting of the field.

It is also to access the field by their database column

eg. $post_id = $DynamicForm->get_value('post_id');

This will fetch the raw database value. Please be sure to use
JB_escape_html() before outputting any raw database value

###############################

$display_mode - post can be displayed for different modes

# FULL  == display on the index page (front page)
# HALF == display in the member's only section, when a user logs in.


*/

	

if ($post_id=='') {

	// The post does not exist!
	// it was deleted from the database

	$label["post_not_found_error"] = str_replace ("%BASE_HTTP_PATH%", JB_BASE_HTTP_PATH , $label["post_not_found_error"]);
	echo '<p style="text-align:center;padding-top:10%;padding-bottom:20%;">'.$label['post_not_found_error']." </p>";
	
} elseif (($DynamicForm->get_value('approved')=='N') && ($display_mode=='FULL')) {

	echo '<p style="text-align:center" style="padding-top:10%;padding-bottom:20%;">'.$label['post_not_approved'].'</p>';
	
	
} else {

	?>
	<table cellpadding="10" style="margin: 0 auto; width:100%; border:0px;" id="job_post" class="job_post"><tr><td class="header">
	<?php
	if (($display_mode=="FULL") || ($display_mode=="HALF")) { // DISPLAY THE POST AS NORMAL

		# FULL MODE = display on the index page (front page)
		# HALF MODE = display in the member's only section, when a user logs in.



		if ($display_mode == 'FULL') {
			?>
			<p style="text-align:center;"><a class="go_back" href="<?php echo htmlentities(JB_get_go_back_link()); ?>"><b><?php echo $label['post_display_goback_list']; ?></b></a><br>
			<br>
			<?php
		}

		// is the post expired?
		if ($DynamicForm->get_value('expired')=='Y') {
			echo '<span class="expired_msg">';

			$post_time = strtotime ($DynamicForm->get_value('post_date'));
			$duration = time() - $post_time;
			$days = floor($duration / 24 / 60 / 60);
			
			$label["post_expired"] = str_replace ("%POSTS_DISPLAY_DAYS%", $days , $label["post_expired"]);

			echo $label['post_expired'];
			echo '</span>';
		} else {
			// not expired
			echo '<span class="mention_us_msg">';
			$label["post_display_mention_us"] = str_replace ("%SITE_NAME%", jb_escape_html(JB_SITE_NAME) , $label["post_display_mention_us"]);
			echo $label['post_display_mention_us'];
			echo '</span>';
		}?></p>
		</td></tr>
		<?php

		if (($_SESSION['JB_Domain']!='EMPLOYER') && (!$admin)) {

			// Display the top links

		?>
			<tr><td class="top_links">
			<table width="100%" border="0">
			<tr>
			<?php 
			if ($_SESSION['JB_ID'] != '') { // user is logged in? Check if the user saved this post
				$is_saved = JB_is_job_saved($_SESSION['JB_ID'], $_REQUEST['post_id']); 
			}
			if ($is_saved) {
				$link = JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."search.php";
			} else {
				$link = htmlentities($_SERVER['PHP_SELF']);
			}

			//if (($_SESSION['JB_Domain']!='EMPLOYER') && (!$admin)) {
				// show the 'See all jobs by this Advertiser' links
			?>
			<td align="left" class="top_links">
			<?php
			if ($DynamicForm->get_value('guid')=='') { // the job is form this site.	(Jobs that were cross-posted to Jamit gave a guid which is the URL to the original posting)
				?>
				<a href="<?php echo $link; ?>?show_emp=<?php echo $POSTED_BY_ID; ?>"><?php echo $label['post_display_see_all']; ?></a>
				<?php 
			} 
				
			?></td><td style="float: right;" class="top_links">
			<?php
			
			if (($_SESSION['JB_Domain']!='EMPLOYER') && (!$admin)) { // Not logged in as Employer, not admin
				if (($_SESSION['JB_ID']!='') && (JB_SAVE_JOB_ENABLED=='YES')) { // only logged in candidates can save..

					if ($is_saved) {
						echo $label["post_display_job_saved"];
					} else { 
							
						?><a href="<?php echo JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER; ?>save.php?<?php echo ("action=save&post_id=". jb_escape_html($_REQUEST['post_id']));?>"><?php echo $label['post_display_save']; ?></a><?php 
					}
					
				}

				// Tell a friend enabled?
				// * TAF enabled all the time
				// * TAF enabled for logged in users
				// * Taf disabled
				if (((JB_TAF_ENABLED=='YES') && (JB_TAF_SIGN_IN !='YES')) || ((JB_TAF_ENABLED=='YES') && (JB_TAF_SIGN_IN =='YES') && $_SESSION['JB_ID']!='')) {
					$TAF = "YES";
				}
				if (($TAF=='YES') && ((JB_SAVE_JOB_ENABLED=='YES') && (($_SESSION['JB_ID']!='')))) {
					echo " | ";
				}
				if ($TAF=='YES') { 
									?> <a href="#"  onclick="
				   window.open('<?php echo JB_BASE_HTTP_PATH; ?>email_job_window.php?post_id=<?php echo jb_escape_html($_REQUEST['post_id']);?>', '', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=380,height=560,left = 50,top = 50');return false;" ><img alt="Email to friend" src='<?php echo JB_THEME_URL; ?>images/mail.gif' border='0' width="20" height="11"><?php echo $label['post_display_email']; ?></a><?php
				}
			}

			?></td>
			
		</tr>
		</table>
		</td>
		</tr>

	<?php
		} // end of top_links
	}


	?>
   <tr><td class="job_post_body">

	<h1 class="job_title"><?php echo $TITLE;?></h1>
	<?php
	if ($POSTED_BY!='') {
		?>
		<b><?php echo $label['post_display_posted_by']; ?></b>: <i><?php echo $POSTED_BY;?></i><p>
		<?php
	}

	?>
	<b><?php echo $label['post_display_posted_date']; ?></b>: <i><?php echo $FORMATTED_DATE;?></i><p>

	<b><?php echo $label['post_display_location']; ?></b>: <i><?php echo $LOCATION;?></i><br>
	<p>

	<?php


	if (((($display_mode=="FULL") || ($display_mode=="HALF"))) && ($APP==true)) {

		// the application button
		// app_type
		// O = Online application
		// R = Redirect
		// N = None
	
		switch ($DynamicForm->get_value('app_type')) {

			case 'O': // Online Application
				?>

				<input type="button" name="apply"  onclick="showDIV(document.getElementById('app_form'), 'app_form_tmp', true); this.disabled=true;" class="form_apply_button" value="<?php echo $label['post_apply_online'];?>">

				<p>
				<span id="app_form">
				</span>
				<span id="app_form_blank" >
				</span>
				<span id="app_form_tmp" style="display: none">

				<iframe width="100%" height="510" frameborder=0 MARGINWIDTH=0 MARGINheight=0   src="<?php echo JB_BASE_HTTP_PATH;?>apply_iframe.php?post_id=<?php echo jb_escape_html($_REQUEST['post_id']); if (($_SESSION['JB_ID'] != '') && ($_SESSION['JB_Domain'] == 'CANDIDATE')) echo "&user_id=".$_SESSION['JB_ID']; ?>">
					</iframe>

				</span>

				<?php
				break;
			case 'R': /* Redirect (Open appurl_iframe.php in a new winow, it will check the login and
						 then redirect the user to the application URL) */
				?>
				<input type="button" name="apply"  onclick="window.open('<?php echo JB_BASE_HTTP_PATH;?>appurl_iframe.php?post_id=<?php echo jb_escape_html($_REQUEST['post_id']); if (($_SESSION['JB_ID'] != '') && ($_SESSION['JB_Domain'] == 'CANDIDATE')) echo "&user_id=".$_SESSION['JB_ID']; ?>', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=750,height=500,left = 50,top = 50');return false;" class="form_apply_button" value="<?php echo $label['post_apply_online'];?>">


				</span>
				
				<?php
				break;
			case 'N':
				break;

		}
	
	}
		
	$DESCRIPTION = JB_process_for_html_output ($DESCRIPTION);
	?>
	<div class="post_description">
	<?php echo $DESCRIPTION; ?>
	</div>
	<?php

		$mode = "view";
		$DynamicForm->display_form($mode, $admin);
	?>

	</td></tr>
	</table>

	<?php
	
}

