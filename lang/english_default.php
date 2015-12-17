<?php
# Copyright 2005-2009 Jamit Software
# http://www.jamit.com/
// Main Index page
 
$label["candidate_intro"] = "Announce your  availability and submit your resume to hundreds of Employers that visit this site. <br> Employers can contact you directly as soon as a suitable position becomes available."; #  - front page
$label["candidate_forgot_your_pass"] = "Forgotten your Password?";
$label["candidate_forgot_submit"] = "Submit";
$label["candidate_join_now_link"] = "<a href='%CANDIDATE_FOLDER%signup.php'><b>Join now!</b></a>"; #  - front page
$label['root_category_link'] = "Jobs by Category:"; # index.php - front page (when displaying categories)
$label['go_to_site_home']  = " Go to %SITE_NAME% Home"; # index.php - front page (when displaying categories)
$label['listing_jobs_by_emp']="Listing jobs posted by %EMPLOYER_NAME%"; # index.php - browse by employer
$label['category_header'] = "Jobs by Category"; # index.php - browse by category
$label['category_expand_more'] = "More..."; # category.inc.php ('More' link to expand the category)
$label["employer_intro_text"] = "Employers and Recruiters can post jobs and view resumes."; #  - front page
$label["post_resume_link"] = "<a href=\"%CANDIDATE_FOLDER%\">Post your Resume to %SITE_NAME%!</a>"; # intro.inc.php - front page 
$label["index_employers_services"] = "Employer's Services:";
$label["post_job_link"] = "Post a Job!"; #  - front page 
$label["manage_posts_link"] = "Manage Jobs Posts";#  - front page 
$label["view_resumes_link"] = "View Resumes";#  - front page 

//$label["navigation_page"] = "Page";
$label["navigation_page"] = "Page %CUR_PAGE% of %PAGES% - "; # label for navigational links for browsing lists of resumes, posts and profiles
$label["navigation_prev"] = "&lt;- Previous"; # label for navigational links for browsing lists of resumes, posts and profiles
$label["navigation_next"] = "Next -&gt;"; # label for navigational links for browsing lists of resumes, posts and profiles
$label['index_employer_jobs'] ="Jobs";
$label['index_search_box_heading']="Find Jobs";

$label['nav_page_title'] = "Page %PAGE% | %SITE_NAME%";

# post_display.inc.php

$label['job_post_404'] = 'Job post not found, it appears to have been deleted.';
$label['post_not_found_error'] = "Error: This job vacancy does not exist on our website anymore. The possible cause could be that the employer deleted it from this website because the job vacancy is now filled. You may <a href='%BASE_HTTP_PATH%'>Click Here</a> to view the latest jobs on offer. We hope you'll find something that you'll like!</p>"; # include/post_display.inc.php
$label['post_not_approved'] = "This post is currently not approved for public viewing.";
$label['post_expired'] = "This post has expired! It was posted more than %POSTS_DISPLAY_DAYS% days ago."; # post_display.inc.php - Display expired post.
$label['post_not_approved_cause'] = "NOT APPROVED BECAUSE:";

$label['post_display_goback_list'] = "Go back to the Job List"; # include/post_display.inc.php (display a post)
$label['post_display_mention_us'] = "** Please mention %SITE_NAME% when replying to this advertisement  **"; # include/post_display.inc.php (display a post)
$label['post_display_see_all'] = "See all jobs by this advertiser"; # include/post_display.inc.php (display a post)
$label['post_display_save'] = "Save this Job"; # include/post_display.inc.php (display a post)
$label['post_display_email'] = "Email this Job"; # include/post_display.inc.php (display a post)
$label['post_display_posted_by'] = "Posted by"; # include/post_display.inc.php (display a post)
$label['post_display_posted_date'] = "Posted date"; # include/post_display.inc.php (display a post)
$label['post_display_location'] = "Location"; # include/post_display.inc.php (display a post)

$label["post_apply_online"] = "Apply Online"; # post_display.inc.php - Button for applying online
$label["post_display_job_saved"] = "Job Saved"; # post_display.inc.php - when user is logged in & job is saved by the user.
$label["posted_by_unknown"] ="Unknown";

$label['job_post_meta_description'] = 'Job listed by %POSTED_BY%, %LOCATION%'; // MATA tag for description. other possible tags: %DESCRIPTION% %DATE%

$label['job_post_pr_meta_title'] = 'Premium Jobs List, page %PAGE% - %SITE_NAME%';
$label['job_post_pr_meta_description'] = 'Listing all premium jobs posted to %SITE_NAME% ';

// apply_iframe.php
$label['app_input_letter'] = "Please write your cover letter below. ";
$label["app_resume_notpres"] = "Online resume not present.";
$label["app_please_log_in"] = "Please Log in. If you are a new job seeker, please <a href='%CANDIDATE_FOLDER%signup.php' target='_parent' ><b>sign up</b></a> now.";
$label["app_already_applied"]  = "Note: You have already applied for this job!";
$label["app_email_sent_from_sig"] = "This Email was sent from "; # apply_iframe.php - email signiture
$label["app_letter_error"] = "- Application Letter is not filled in"; # apply_iframe.php - Online applications
$label["app_email_error"] = "- Your Email is not filled in"; # apply_iframe.php - Online applications
$label["app_email_invalid"] = "- Your Email address seems to be invalid"; # apply_iframe.php - Online applications
$label["app_name_error"] = "- Your Name was not filled in."; # apply_iframe.php - Online applications
$label["app_error"] = "ERROR: Could not send your application due to the following errors:"; # apply_iframe.php - Online applications
$label['app_employer_email_invalid'] = "Cannot apply online: Employer email invalid. Please apply to this employer directly."; # apply_iframe.php - Online applications
$label['app_sent'] = "Your application was sent."; # apply_iframe.php - Online applications
$label['app_receipt_subject'] = "online application receipt"; # apply_iframe.php - Online applications
$label['app_confirm_title']="You have sent the following -"; # apply_iframe.php - Online applications

$label['app_confirm_name'] ="Your name:"; # apply_iframe.php - Online applications
$label['app_confirm_email'] ="Your email address:"; # apply_iframe.php - Online applications
$label['app_confirm_subject'] ="Subject:"; # apply_iframe.php - Online applications
$label['app_confirm_lettter'] ="Application Letter:"; # apply_iframe.php - Online applications
$label['app_confirm_att1'] ="Attachment 1:"; # apply_iframe.php - Online applications
$label['app_confirm_att2'] ="Attachment 2:"; # apply_iframe.php - Online applications
$label['app_confirm_att3'] ="Attachment 3:"; # apply_iframe.php - Online applications

$label['app_email_subject'] = "Reply to your ad posted on %SITE_NAME%, %DATE% %TITLE% "; # apply_iframe.php - Online applications

$label['app_input_name'] ="Your name:"; # apply_iframe.php - Online applications
$label['app_input_email'] ="Your email address:"; # apply_iframe.php - Online applications
$label['app_input_subject'] ="Subject:"; # apply_iframe.php - Online applications
$label['app_input_lettter'] ="Application Letter:"; # apply_iframe.php - Online applications
$label['app_input_att1'] ="Attachment 1:"; # apply_iframe.php - Online applications
$label['app_input_att2'] ="Attachment 2:"; # apply_iframe.php - Online applications
$label['app_input_att3'] ="Attachment 3:"; # apply_iframe.php - Online applications

$label['app_send_button'] = "Send Application"; # apply_iframe.php - Online applications

$label['app_input_optional'] = "(optional)"; # apply_iframe.php - Online applications

$label['app_att_not_allowed']="- Cannot attach <b>%FILE_NAME%</b> This website does not allow you to attach those kind of files for security reasons.";


$label['app_att_too_big']="- Cannot upload %FILE_NAME% - The file is too big!";

$label['app_account_links'] = "<a href=\"%CANDIDATE_FOLDER%index.php\" target=\"_parent\">Go to your account</a> / <A href=\"%CANDIDATE_FOLDER%edit.php\" target=\"_parent\">Edit your resume</a> / <A href=\"%CANDIDATE_FOLDER%apps.php\" target=\"_parent\">View your application history</a>";

$label['app_member_only'] = "You will need to complete your membership payment before you can apply for this job. Please <a target=\"_parent\" href=\"%MEMBERSHIP_URL%\">continue to the Membership page</a>";

# Email application 

// email_iframe.php
$label['email_sent_ok'] = "Email Sent.";
$label["em_email_sent_from_sig"] = "This Email was sent from "; # email_iframe.php - Employers replying to resume
$label["em_letter_error"] = "- Letter is not filled in"; # email_iframe.php - Employers replying to resume
$label["em_email_error"] = "- 'To:' Email is not filled in"; # email_iframe.php - Employers replying to resume
$label["em_email_invalid"] = "- 'To:' Email address seems to be invalid"; # email_iframe.php - Employers replying to resume
$label["em_name_error"] = "- 'To Name:' in to filled in."; # email_iframe.php - Employers replying to resume
$label["em_error"] = "ERROR: Could not send your email due to the following errors:"; # email_iframe.php - Employers replying to resume
$label['em_employer_email_invalid'] = "Cannot email: User's email is invalid. Please contact this candidate directly."; # email_iframe.php - Employers replying to resume
$label['em_sent'] = "Your email was sent."; # email_iframe.php - Employers replying to resume

$label['em_confirm_title']="You have sent the following -"; # email_iframe.php - Employers replying to resume

$label['em_confirm_name'] ="To Name:"; # email_iframe.php - Employers replying to resume
$label['em_confirm_email'] ="To Email Address:"; # email_iframe.php - Employers replying to resume
$label['em_confirm_subject'] ="Subject:"; # email_iframe.php - Employers replying to resume
$label['em_confirm_lettter'] ="Letter:"; # email_iframe.php - Employers replying to resume
$label['em_confirm_att1'] ="Attachment 1:"; # email_iframe.php - Employers replying to resume
$label['em_confirm_att2'] ="Attachment 2:"; # email_iframe.php - Employers replying to resume
$label['em_confirm_att3'] ="Attachment 3:"; # email_iframe.php - Employers replying to resume

$label['em_email_subject'] = "RE: Your resume on %SITE_NAME%, %DATE% %TITLE% "; # email_iframe.php - Employers replying to resume

$label['em_input_name'] ="To Name:"; # email_iframe.php - Employers replying to resume
$label['em_input_email'] ="To Email Address:"; # email_iframe.php - Employers replying to resume
$label['em_input_subject'] ="Subject:"; # email_iframe.php - Employers replying to resume
$label['em_input_lettter'] ="Letter:"; # email_iframe.php - Employers replying to resume

$label['em_send_button'] = "Send Email"; # email_iframe.php - Employers replying to resume


####
# Email job to a friend

$label['taf_email_blank'] = "* Your email is blank"; # email_job_window.php - Tell a friend
$label['taf_email_invalid'] = "* Your email is invalid"; # email_job_window.php - Tell a friend
$label['taf_name_blank'] = "* Your name is blank"; # email_job_window.php - Tell a friend
$label['taf_f_email_blank'] = "* Your friend's email is blank"; # email_job_window.php - Tell a friend
$label['taf_f_email_invalid'] = "* Your friend's email is invalid!"; # email_job_window.php - Tell a friend
$label['taf_subject_blank'] = "* The subject is blank"; # email_job_window.php - Tell a friend
$label['taf_msg_too_long'] = "Message is limited to 140 letters";
$label['taf_subj_too_long'] = "Subject is limited to 35 letters";
$label['taf_no_url'] = "No links are allowed in the message";

$label['taf_msg_to'] = "To:"; # email_job_window.php - Tell a friend
$label['taf_msg_from'] = "From:"; # email_job_window.php - Tell a friend
$label['taf_msg_line'] = "I thought you'd be interested in this page from %SITE_NAME%"; # email_job_window.php - Tell a friend
$label['taf_msg_link'] = "Link:"; # email_job_window.php - Tell a friend
$label['taf_msg_comments'] = "Comments:"; # email_job_window.php - Tell a friend

$label['taf_sending_email'] = "Sending Email..."; # email_job_window.php - Tell a friend
$label['taf_email_sent'] = "Email was sent to:"; # email_job_window.php - Tell a friend
$label['taf_default_subject'] = "I saw this on %SITE_NAME%";
// (buttons)

$label['taf_button_send_again'] = "Send to another friend"; # email_job_window.php - Tell a friend
$label['taf_button_close_window'] = "Close Window"; # email_job_window.php - Tell a friend

$label['taf_failed']="Failed sending an email, please contact us and provide your username in your error report.";  # email_job_window.php - Tell a friend

$label['taf_error'] = "Error, cannot send the email because:"; # email_job_window.php - Tell a friend

$label['taf_heading'] = "Email This Job to a Friend"; # email_job_window.php - Tell a friend
$label['taf_url'] = "URL:"; # email_job_window.php - Tell a friend

$label['taf_input_email'] = "Your email:"; # email_job_window.php - Tell a friend
$label['taf_input_name'] = "Your name:"; # email_job_window.php - Tell a friend
$label['taf_input_f_email'] = "Your friend's email:"; # email_job_window.php - Tell a friend
$label['taf_input_f_name'] = "Your friend's name:"; # email_job_window.php - Tell a friend
$label['taf_input_subject'] = "Subject of email:"; # email_job_window.php - Tell a friend
$label['taf_input_message'] = "Your message: (optional)"; # email_job_window.php - Tell a friend
$label['taf_button_email'] = "Send Email!"; # email_job_window.php - Tell a friend
$label['taf_button_cancel'] = "Cancel"; # email_job_window.php - Tell a friend

// sunscription button
$label['subscr_button_head'] = "Click on the button below to make a secure Credit Card payment."; # subscription_button_iframe.php - subscription PayPal payment page  # button_iframe.php - subscription PayPal payment page
$label['subscr_button_confirm'] = "Please confirm your order to continue."; # button_iframe.php - subscription PayPal payment page

$label['employer_credits_order_confirm'] = "Please confirm your order";


// Employer's Sign-up

$label["employer_signup_continue"] = "Click Here to Continue";# Employers: signup.php
$label["employer_signup_goback"] = "Back to the <a href='../index.php'>Job Board.</a>";# Employers: signup.php

$label["employer_join_now"] = "Join Now"; # Employers - displayed when a user who is not logged in tries to access the Employer's section (advertiser/login_functions.php)
$label["employer_login"] = "Login"; # Employers - Button to log in. (advertiser/login_functions.php)

$label["employer_signup_heading1"] = "Post job advertisements and view resumes on %SITE_NAME% "; # Employers - signup.php
$label["employer_signup_heading2"] = "Sign up for an account"; # Employers - signup.php
$label["employer_signup_infobox"] = "Sign up as a new Employer / Advertiser"; # Employers - signup.php
$label["employer_signup_first_name"] = "First Name"; # Employers - signup.php
$label["employer_signup_last_name"] = "Last Name"; # Employers - signup.php
$label["employer_signup_business_name"] = "Business Name"; # Employers - signup.php
$label["employer_signup_business_name2"] = "Enter school or company name"; # Employers - signup.php
$label["employer_signup_member_id"] = "Username"; # Employers - signup.php
$label["employer_signup_member_id2"] = "(Choose a unique Login ID that you will use to log in, but do not use spaces)"; # Employers - signup.php
$label["employer_signup_password"] = "Password"; # Employers - signup.php
$label["employer_signup_password_confirm"] = "Confirm Password"; # Employers - signup.php
$label["employer_signup_your_email"] = "E-mail"; # Employers - signup.php

$label["employer_signup_newsletter"] = "Receive Newsletter?"; # Employers - signup.php
$label["employer_signup_new_resumes"] = "Notification on new Resumes?"; # Employers - signup.php
$label["employer_signup_submit"] = "Submit"; # Employers - signup.php
$label["employer_signup_reset"] = "Reset"; # Employers - signup.php
$label["employer_signup_error"] = "Cannot continue due to the following errors:<p>"; # Employers - signup.php
$label["employer_signup_error_name"] = "- Please fill in your first name<br>"; # Employers - signup.php
$label["employer_signup_error_ln"] = "- Please fill in your last name<br>"; # Employers - signup.php
$label["employer_signup_error_user"] = "- Please fill in your username.<br>"; # Employers - signup.php
$label["employer_signup_error_inuse"] = "- The username '%username%' is in use. Please choose a different username <br>"; # Employers - signup.php
$label["employer_signup_error_p"] = "- Please fill in your password <br>"; # Employers - signup.php
$label["employer_signup_error_p2"] = "- Please confirm your password <br>"; # Employers - signup.php
$label["employer_signup_error_pw_too_weak"] = "- Your password is too weak. It must consist of 6 characters or more.<br>"; # Employers - signup.php
$label["employer_signup_error_email"] = "- Please fill in your Email <br>"; # Employers - signup.php
$label["employer_signup_error_pmatch"] = "- Passwords do not match <br>"; # Employers - signup.php
$label["employer_signup_error_uname"] = "- Your username may only contain letters, or numbers";
$label["employer_signup_success"] = "%FirstName% %LastName%, You have successfully signed up to the %SITE_NAME% Employer's System. You will soon receive a validation e-mail. If you ever encounter any problems, bugs or just have any questions or suggestions, feel free to contact  %SITE_CONTACT_EMAIL%"; # Employers - signup.php
$label["employer_signup_error_invemail"] = "- Invalid email address";
$label["employer_signup_email_in_use"] = "- Cannot create a new account: The email address is already in use. (Did you <a href='forgot.php'>forget your username or password?</a>) "; # Employers - signup.php

$label['employer_login_error']="<b>Error: Username/Password combination is incorrect. </b><a href=\"index.php\">Try again...</a><p> If you have forgotten your password, please <a href=\"forgot.php\">Click Here</a>.<br>Please <a href=\"signup.php\">Sign Up</a> if you are a new user. "; # Employers - login.php
$label["employer_login_disabled"] = "You cannot login because this account hasn't been activated yet. You will need to wait until your account is reviewed by our staff and activated. Thank you for your patience. You may go back to the <a href='%BASE_HTTP_PATH%'>Job Board</a>"; # Employers - login.php
$label["employer_login_success"] = "Welcome back %firstname% %lastname%. You have successfully signed in as '%username%' <br>Processing Login... If this page appears for more than 5 seconds <a href='index.php'>click here to reload.</a><p>"; # Employers - login.php
$label["employer_logging_in"] = "Logging in to %SITE_NAME% ...  "; # Employers - login.php
$label["employer_loginform_title"] = "Employer's Login"; # Employers - Login form (login_functions.php)
$label['employer_new_user_created']= "New user created"; # Employers - Signup (login_functions.php)
$label['employer_could_not_signup'] = "Could not sign up, try using another username"; # Employers - Signup error (login_functions.php)

$label['employer_logout_ok'] = "You have logged out."; # Employers - logout.php
$label['employer_logout_home'] = "%SITE_NAME% Home"; # Employers - logout.php (link to home page)


$label["yes_option"] = "Yes"; # Employers : signup.php (Option for radio buttons)
$label["no_option"] = "No"; # Employers: signup.php (Option for radio button)

$label["employer_signup_language"] = "Select your preferred language";  # Employers - signup.php; Candidates - signup.php


$label["employer_pass_forgotten"] = "Forgotten your Password?"; # Employer - login form (login_functions.php) this is shown directly below the login form...
$label["employer_forgot_title"] = "Forgot my password"; # Employer - forgot.php (Page heading)
$label["employer_forgot_enter_email"] = "Enter your Email address";  # Employer - forgot.php
$label["employer_forgot_email_notfound"] = "Email not found on the system. Try again";  # Employer - forgot.php
$label["employer_forgot_submit"] = "Submit";  # Employer - forgot.php (submit button)
$label["employer_forgot_error1"]= "You cannot reset your password because your account is not enabled. Please wait for your account to be validated. Contact %SITE_CONTACT_EMAIL% if you have any questions."; # Employer - forgot.php
$label["employer_forgot_success1"] = "A new password was emailed to you. Please check your email in a few minutes. You will be able to log in with the new password here: <a href='%BASE_HTTP_PATH%%EMPLOYER_FOLDER%'>%BASE_HTTP_PATH%%EMPLOYER_FOLDER%</a>"; # Employer - forgot.php
$label["employer_forgot_fail1"] = "Failed sending an email, please contact support by clicking here and provide include your username, first name and last name in your error report."; # Employer - forgot.php 
$label["employer_forgot_job_board"] = "Job Board"; # Employer - forgot.php (Link to the front page)

$label["post_new_intro"] = "Post a new job here. Fields marked with <span class=\"is_required_mark\">*</span> are required."; # Employer: post_iframe.php (posting form)

$label['post_no_credits']="Not enough credits to post!";
$label["post_iframe_title"] = "%SITE_NAME%  - Post a new Job";# Employer: post_iframe.php (posting form)
$label["post_iframe_post_prm"] ="Post a Premium job."; # Employer: post_iframe.php (posting form)
$label["post_iframe_read_more"] ="Read More Information about premium ads."; # Employer: post_iframe.php (posting form)
$label["post_iframe_prm_remain"] ="You have <b>%P_POSTS% premium posts</b> remaining."; # Employer: post_iframe.php (posting form)
//$label["post_iframe_clickhere"] ="Click here to get credits for premium posting."; # Employer: post_iframe.php (posting form)
$label["post_iframe_pst_click"] ="Click on the following button to order credits for posting."; # Employer: post_iframe.php (posting form)
$label["post_iframe_posts_remain"] ="You have <b>%POSTS% posts remaining</b>. "; # Employer: post_iframe.php (posting form)
$label['post_iframe_posts_delete_old']="You have exceeded the number of free posts that are allowed per user. Please delete your old posts, then you may post again.";  # Employer: post_iframe.php (posting form)
$label["post_iframe_add"] ="Add credits here."; # Employer: post_iframe.php (posting form)
$label["post_iframe_p_saved"] ="Post Saved. <a href='manager.php' target='_parent'>Continue</a> or <a href='post.php?%POST_TYPE%' target=\"_parent\">Post Another Job</a>";  # Employer: post_iframe.php (posting form)

$label["post_iframe_reposthead"] ="Repost - Post your old ad as a new job post";
$label["post_iframe_repostdesc"] ="The data from your original post has been pre-filled in to the form below. Please delete your old post later.";


$label["post_iframe_remainmax"] = "You have %POSTS_REMAIN% free standard posts remaining. (Maximum posts: %JB_FREE_POST_LIMIT_MAX% ";
$label["post_iframe_ulimitedfree"] ="You can make unlimited free posts.";


$label['buy_p_posts_msg'] = "<p>To post a new premium job you will need to have some Premium Posting Credits. Please continue to our <a target=\"_parent\" href=\"credits.php\"><b>price list and ordering page</b></a>. </p><p>Once you have some posting credits, please come back here to post a new premium job instantly!</p>";

$label['buy_posts_msg'] = "<p>To post a new job you will need to have some Posting Credits. Please continue to our <a target=\"_parent\" href=\"credits.php\"><b>price list and ordering page</b></a>. </p><p>Once you have some posting credits, please come back here to post a new job instantly!</p>";


$label['post_save_error']="Cannot Save your post due to the following errors:";  # Employer: posts.inc.php (posting form, saving)


$label['post_save_app_url_blank'] = "- Your custom application URL is blank";
$label['post_save_app_url_bad'] = "- Your custom URL must start with http:// or https://";

$label['post_list_field_label_date']="Date";
$label['post_list_field_label_views']="Views";

$label['post_list_field_label_nar']="Not Approved Reason";
$label['post_list_field_label_postid']="Post ID";
$label['post_list_field_label_userid']='Employer ID';
$label['post_list_field_label_jobtitle']="Job Title";
$label['post_list_field_label_postmode']="Post Mode";
$label['post_list_field_label_mapx']="Map X";
$label['post_list_field_label_mapy']="Map Y";
$label['post_list_field_label_appr']="Approved";
$label['post_list_field_label_app']="Applications";
$label['post_list_field_label_descr']="Description";
$label['post_list_field_label_app_url'] = 'App URL';
$label['post_list_field_label_app_t'] = 'App Type';
$label['post_list_field_label_expired'] = 'Expired';
$label['post_list_field_label_src'] = 'Import Source';
$label['post_list_field_label_guid'] = 'Import GUID';

$label['post_save_button'] = "Save"; # Employer: posts.inc.php (Save post button)

$label['post_list_posted_by'] = "Posted By:"; # posts.inc.php (shown when listing posts)
$label['post_list_category'] = "Category:"; # posts.inc.php (shown when listing posts)

$label['post_search_no_result']="No job posts found."; # posts.inc.php
$label['post_list_sponsored']="Sponsored Listings"; # posts.inc.php
$label['post_list_more_sponsored'] = "Browse more sponsored listings: "; # posts.inc.php

$label['post_list_count']="Listing %COUNT% jobs posted within %POSTS_DISPLAY_DAYS% days!"; # posts.inc.php
$label['post_list_cat_count']="Listing %COUNT% jobs posted to this category within %POSTS_DISPLAY_DAYS% days!"; # posts.inc.php

$label['post_list_today'] = "Today"; # posts.inc.php (Listing posts: day of week on post list)
$label['post_list_3_weeks']= "3 weeks ago,"; # posts.inc.php (Listing posts)
$label['post_list_2_weeks']= "2 weeks ago,"; # posts.inc.php (Listing posts)
$label['post_list_1_week']= "1 week ago,"; # posts.inc.php (Listing posts)

$label['post_list_dow_0'] = "Sunday"; # posts.inc.php (Listing posts)
$label['post_list_dow_1'] = "Monday"; # posts.inc.php (Listing posts)
$label['post_list_dow_2'] = "Tuesday"; # posts.inc.php (Listing posts)
$label['post_list_dow_3'] = "Wednesday"; # posts.inc.php (Listing posts)
$label['post_list_dow_4'] = "Thursday"; # posts.inc.php (Listing posts)
$label['post_list_dow_5'] = "Friday"; # posts.inc.php (Listing posts)
$label['post_list_dow_6'] = "Saturday"; # posts.inc.php (Listing posts)

$label['post_delete_confirm']= "Delete what's selected, are you sure?"; # Employer: posts.inc.php (deleting posts)
$label['post_delete_confirm2']= "Delete this Post, are you sure?"; # Employer - Post Manager: posts.inc.php (delete a post)
$label['post_edit_button'] ="Edit"; # Employer - Post Manager: posts.inc.php (Edit button)
$label['post_repost_button'] ="Re-post"; # Employer - Post Manager: posts.inc.php (Edit button)
$label['post_delete_button'] = "Delete"; # Employer - Post Manager: posts.inc.php (Delete button)
$label['post_unexpire_button'] = "Undo Expire"; # Employer - Post Manager: posts.inc.php (Un Expire)
$label['post_unexpire_ok'] = "Post expiration un-done";
$label['post_delete_confirm'] = "Delete this Post, are you sure?";
$label['profile_save_error'] = "Cannot Save your profile due to the following errors:";# Employer - Profiles: profiles.inc.php 
$label['profile_save_button'] = "Save";
$label['profile_list_date_field_label'] = "Date Posted";

$label["employer_section_heading"] = "Access to the Employer's system. Post Job Advertisements &amp; Browse resumes!<br> "; # Employer - login_functions.php (displayed on top of employer's login form)

$label["employer_flogin_emp"] = "Employer's / Advertiser's Login<br> "; # Employer - login_functions.php 

$label["employer_link_to_jobseeker"] = "Are you a job seeker?<br> "; # Employer - login_functions.php 

###############################################################################################
# EMPLOYERS - The following section is the English version of the employers section
# after the employer logs in.
###############################################################################################

$label["employer_menu_logged_in"]="Logged in as:";
$label["employer_menu_apps"]="Applications";
$label["employer_menu_app_man"]="Application Manager";
$label["employer_menu_subscr"]="Subscription..."; # Employer - menu.php
$label["employer_menu_prm_post"]="Post a Premium Job";  # Employer - menu.php
$label["employer_menu_credits"]="Posting Credits..."; # Employer - menu.php
$label["employer_menu_account"] = "Account"; # Employer - menu.php
$label["employer_menu_ac_details"] = "Account Details"; # Employer - menu.php
$label["employer_menu_main_page"] = "Main Page"; # Employer - menu.php
$label["employer_menu_change_pw"] = "Change Password"; # Employer - menu.php
$label["employer_menu_logout"] = "Logout"; # Employer - menu.php
$label["employer_menu_profile"] = "Profile"; # Employer - menu.php
$label["employer_menu_view_profile"] = "View Profile"; # Employer - menu.php
$label["employer_menu_edit_profile"] = "Edit Profile"; # Employer - menu.php
$label["employer_menu_resumes"] = "Resumes"; # Employer - menu.php
$label["employer_menu_resume_alerts"] = "Email Alerts"; # Employer - menu.php
$label["employer_menu_browse_resumes"] = "Browse Resumes"; # Employer - menu.php
$label["employer_menu_saved_resumes"] = "Saved Resumes";
$label["employer_menu_posts"] = "Posts"; # Employer - menu.php
$label["employer_menu_job_post_manager"] = "Job Post Manager"; # Employer - menu.php
$label["employer_menu_post_a_new_job"] = "Post a New Job"; # Employer - menu.php
$label["employer_menu_help"] = "Help"; # Employer - menu.php
$label["employer_menu_contents_and_index"] = "Help Page"; # Employer - menu.php
$label["employer_menu_select_lang"] = "Language..."; # Employer - menu.php
$label["employer_menu_membership"] = "Membership Details"; # Employer - menu.php


$label["employer_home_welcome_title"] = "Employer's Account: Hi, %firstname% %lastname%! Welcome to %SITE_NAME%.  "; # Employer - index.php
$label["employer_home_welcome_text"] = "<p>The Employer's account allows you to post job advertisements to the %SITE_NAME% Job Board. It also allows you to browse resumes that were posted to %SITE_NAME%. You can also create a business profile, which is similar to an online business card which you can then use to promote your business to potential candidates. </p>"; # Employer - index.php
$label["employer_home_stats"] = "<p>You have <b>%postcount%</b> jobs posts on %SITE_NAME% (<b>%approvedpostcount%</b> posts were approved). There are currently <b>%usercount%</b> users registered with %SITE_NAME% (<b>%resumecount%</b> of them have posted their resume). Go to <a href=\"manager.php\" >Job Post Manager</a> to manage your Job Posts, or you can <a href=\"search.php\"> Browse Resumes.</a></p> "; # Employer - index.php
$label['employer_home_status_summary'] = "Status Summary"; # Employer - index.php

$label["employer_home_main_menu"] = "Main Menu"; # Employer - index.php
$label["employer_home_main_account"] = "<b>Account</b> - <a href=\"index.php\">Main Page</a> and <a href=\"logout.php\" >Logout</a> "; # Employer - index.php
$label["employer_home_main_profile"] = "<b>Profile</b> - <a href=\"edit.php\" >Edit Profile</a> / <a href=\"profile.php\" >View Profile</a> business profile."; # Employer - index.php
$label["employer_home_main_resumes"] = "<b>Resumes</b> - <a href=\"search.php\" >Browse Resumes</a>."; # Employer - index.php
$label["employer_home_main_posts"] = "<b>Posts</b> - To<a href=\"post.php\" > Post a New Job</a> and <a href=\"manager.php\" > Manage Job Posts</a> "; # Employer - index.php
$label["employer_home_main_help"] = "<b>Help</b> - <a href=\"help.php\" >Help Page</a>";
#Change password page
$label["employer_pass_title"] = "Change Password"; # Employer - password.php
$label["employer_pass_note"] = "Here you can change your password <p>Click 'Submit' after you have finished making the changes."; # Employer - password.php
$label["employer_pass_old_pass_label"] = "Old Password"; # Employer - password.php
$label["employer_pass_new_pass_label"] = "New Password"; # Employer - password.php
$label["employer_pass_new_pass_confirm_label"] = "Confirm New Password"; # Employer - password.php
$label["employer_pass_button_label"] = "Submit"; # Employer - password.php (button)
$label["employer_pass_change_success"] = "Your password was changed."; # Employer - password.php
$label["employer_pass_error"] = "Cannot continue due to the following errors:"; # Employers - password.php
$label["employer_pass_error_old_pass_incorrect"] = "Your Old Password is incorrect."; # Employer - password.php
$label["employer_pass_error_old_pass_blank"] = "Your Old Password was not filled in."; # Employer - password.php
$label["employer_pass_error_new_pass_blank"] = "Your New Password was not filled in."; # Employer - password.php
$label["employer_pass_error_new_conf_pass_blank"] = "Your Confirmed New Password was not filled in."; # Employer - password.php
$label["employer_pass_change_pass_not_match"] = "Your New Password doesn't match with the Confirmed Password."; # Employer - password.php

#select langauge page language.php
$label["employer_lang_title"] = "Change Language"; # Employer - language.php
$label["employer_lang_note"] = "Select Your Preferred Language <p> Click \"Save\" after you have finished making changes"; # Employer - language.php
$label["employer_lang_label"] = "Select Your Language:"; # Employer - language.php
$label["employer_lang_button_label"] = "Submit"; # Employer - language.php
$label["employer_lang_saved"] ="Language Saved"; # Employer - language.php

# Change account details account.php
$label["employer_ac_error"] = "Cannot continue due to the following errors:"; # Employers - account.php
$label["employer_ac_note"] = "Here you can change your account details <p>Click 'Submit' after you have finished making the changes."; # Employer - account.php
$label["employer_ac_updated"] = "Your account details were updated"; # Employer - account.php
$label["employer_ac_button_label"] = "Submit"; # Employer - account.php
$label["employer_ac_intro"]="Here you can change your account details"; # Employer - account.php
$label["employer_ac_fname_error"]="Your First Name was not filled in!"; # Employer - account.php
$label["employer_ac_lname_error"]="Your Last Name was not filled in!"; # Employer - account.php
$label["employer_ac_email_error"]="Your Email was not filled in!"; # Employer - account.php
$label["employer_ac_email_invalid"]="Your Email is invalid!"; # Employer - account.php
$label["employer_ac_fname"]="Your First Name:"; # Employer - account.php
$label["employer_ac_lname"]="Your Last Name:"; # Employer - account.php
$label["employer_ac_compname"]="Your Organisation or Business name"; # Employer - account.php
$label["employer_ac_email"]="Your Contact Email"; # Employer - account.php
$label["employer_save_error"]="Cannot save due to the following reasons";
# List applications

# View profile profile.php

$label['employer_profile_posted_by'] = 'User #%USER_ID%';

$label["employer_vprofile_title"] = "View Business Profile"; # Employer - profile.php

$label["employer_vprofile_noprof"] = "You do not have a profile yet!";

$label["employer_vprofile_note"] = "Your profile is like a business card. Job Seekers will look at your profile to learn more information about your business, including your contact details, website address, and other important information.";

$label["employer_vprofile_editlink"] = "<a href=\"edit.php\">Click here</a> to edit your profile.";

# Edit profile edit.php

$label["employer_eprofile_title"] = "Edit Profile"; # Employer - edit.php (edit profile)
$label["employer_eprofile_intro"] = "Edit your profile here. Your profile is like a business card. Job Seekers will look at your profile to learn more information about your business, including your contact details, website address, and other important information. <p>Click \"Save\" after you have finished making changes. <p>Fields marked with <FONT SIZE=\"4\" COLOR=\"#FF0000\">*</FONT> are required."; # Employer - edit.php (edit profile)
$label["employer_eprofile_saved"] = "Profile Saved. <a href='profile.php'>Continue</a>"; # Employer - edit.php (edit profile)



# Resume search

# search.php

$label['employer_cannot_resume_browse'] = "Cannot View Resumes"; # Employer - search.php (resumes)

$label['employer_resume_view_stat'] = "<div align='right'><small>Views: %TALLY% / %QUOTA%</small></div>";

$label['employer_search_send_email'] = "Send Email";
$label['employer_resume_browse'] = "Browse Resumes"; # Employer - search.php (resumes)

$label['employer_resume_must_sub'] = "You must subscribe to %SITE_NAME% to browse resumes. <p>Subscribers are able to browse the resumes submitted to %SITE_NAME% by our job seekers, and contact them directly."; # Employer - search.php (resumes)

$label['employer_resume_must_activate'] = "Your account must be manually reviewed by %SITE_NAME% staff before you can browse resumes. <p>We will review your account soon, and you will be able to browse the resume list as soon as your account is activated. We apologize for any inconvenience."; # Employer - search.php (resumes)

$label['employer_resume_must_first_post'] = "You will need to post one or more job advertisements before you can access the resume database. <p> We apologize for any inconvenience."; # Employer - search.php (resumes)


$label['employer_resume_desctive']= "We are sorry, this user has deactivated their resume and it is no-longer available for public viewing at this time...";

$label['employer_resume_noquota'] = "Sorry, it looks like you have used up your monthly allowance for the number of resume views that you can do per month...(From %FROM_DATE% to %TO_DATE% you could do %QUOTA% views). Please wait until %TO_DATE% for your quota to refresh. If you have any questions, please email %SITE_CONTACT%";

$label['employer_resume_noquota_head'] = "Sorry - Your quota is all used up for the month";

$label['employer_resume_more_details'] = "You may return to your <a href=\"subscriptions.php\">subscription details</a> page to view more details about your subscription";

$label['search_start_new'] = "Start a new Search"; # search resume (start a new search link)
# Resume 
$label["employer_resume_list_count"] = " resumes matched."; # Employer - resumes.inc.php (list resumes)
$label["employer_resume_list_nav_prev"] = "&lt;- Previous"; # Employer - resumes.inc.php (list resumes)
$label["employer_resume_list_nav_next"] = "Next -&gt; "; # Employer - resumes.inc.php (list resumes)

$label["employer_resume_list_days_ago"] = "Days ago";  # Employer - resumes.inc.php (list resumes)
$label["employer_resume_list_day_ago"] = "Day ago";  # Employer - resumes.inc.php (list resumes)
$label["employer_resume_list_today"] = "Today!"; # Employer - resumes.inc.php (list resumes)
$label["employer_resume_list_no_image"] = "No Image."; # Employer - resumes.inc.php (list resumes)
$label["employer_resume_list_image_hidden"]="Image Hidden"; # Employer - resumes.inc.php (list resumes)
$label['resume_details_hidden']  = "Details Hidden";# dynamic_forms.php - 
$label['resume_value_hidden']  = "Hidden";# dynamic_forms.php 
$label['resume_details_blocked'] = "Details Blocked";# dynamic_forms.php
$label['member_only_please_log_in']="Only subscribed members can view this field.";
$label['delete_image_button'] ="Delete Image"; # dynamic_forms.php - delete image button
$label['upload_image']="Upload Image";
$label['upload_file']="Upload File";
$label['delete_video_button'] ="Remove Video"; # dynamic_forms.php - delete video button
$label['enter_youtube_url'] ="Enter the URL to your video on Youtube.com:";
$label['delete_file_button'] ="Delete File"; # dynamic_forms.php - delete file button
$label['no_file_uploaded']="No file uploaded.";# dynamic_forms.php
$label['bytes']="bytes";# dynamic_forms.php
$label['kilobytes']="kilobytes";
$label["employer_resume_list_not_found"]  = "0 People Found";  # Employer - resumes.inc.php, (list resumes)

$label['employer_resume_saved'] = 'Saved. You can go to the <a href="saved.php">Saved Resumes</a> page to view them.';
$label['employer_resume_cannot_save'] = 'Cannot save, no resumes were selected.';

# saved.php - employer's saved resumes
$label['emp_save_button'] = 'Save';
$label['emp_saved_heading'] = 'Saved Resumes';

$label['emp_saved_notselected'] = 'Cannot delete from the saved list, no resumes were selected.';
$label['emp_saved_deleted'] = 'Resume(s) deleted from your saved list.';
$label['emp_saved_delete_button'] = 'Delete';
$label['emp_saved_delete_confirm'] = 'Delete from list, are you sure?';

$label['only_with_image']="Only with an image";
$label['only_with_image']="Only with a file";
$label['only_with_youtube']="Only with a YouTube video";
$label['only_with_flixin']="Only with a Flixn Video";
$label['only_with_file']="Only where a file was uploaded";

$label["profiles_not_found"] = "0 profiles found"; # profiles.inc.php

$label['employer_resume_hits'] = "Hits"; # Employers: resumes.inc.php
$label["employer_resume_list_date"] = "Date Updated"; # Employers: resumes.inc.php

$label['employer_resume_resume_id']="Resume ID"; 
$label['employer_resume_user_id']="User ID";
$label['employer_resume_list_on_w']="List on web?";
$label['employer_resume_cba']="Can be anonymous?";
$label['employer_resume_status']="Status";
$label['employer_resume_isapproved']="Approved";

# Employer Resume display

//resume


$label['no_image_on_file'] = "No image on file."; # dynamic_forms.php - Field Type: Image

$label["resume_priv_notice"] = "<B>Keep my identifiable details anonymous on this website.</B> This option is only recommended if you don't want your current employer to learn about your intentions. Your name, photo, DOB and your contact details will not be shown to any employer, unless they request it and you give them your permission."; # Candidates - resumes.inc.php - checkbox text on top of the Resume form
$label["resume_save_error"] = "Cannot Save your resume due to the following errors:"; # Candidates: resumes.inc.php
$label["resume_save_button"] = "Save"; # Candidates - resumes.inc.php (save button)

$label["resume_display_go_back"] = "&lt;-- Go Back to the Search Results";
$label['resume_some_fields_blocked']="<center><b>Some fields have been blocked. Please <a href=\"subscriptions.php\">subscribe</a> to get full access to the resume database.</b></center>";
$label["resume_display_request"] = "Request Contact Details"; # Employers - search.php - request contact details.
$label["resume_display_request_sent"] = "Reveal hidden details - a request to was already sent to this person."; # Employers - request.php


#request details
$label["employer_request_details_head"] = "Request user's Contact Details"; # Employers - request.php
$label["employer_request_details_to"] = "To:"; # Employers - request.php
$label["employer_request_details_from"] = "From:"; # Employers - request.php
$label["employer_request_details_reply"] = "Reply-to Email:"; # Employers - request.php
$label["employer_request_details_msg"] = "Your Message (optional):"; # Employers - request.php

$label["employer_request_details_error"] = "Error, Could not send request because"; # Employers - request.php

$label["employer_request_details_error_msg1"] = "- From field is blank"; # Employers - request.php
$label["employer_request_details_error_msg2"] = "- Reply-to field is blank"; # Employers - request.php
$label["employer_request_details_error_msg3"] = "- Invalid reply-to email address"; # Employers - request.php
$label["employer_request_letter_subject"] = "Request for your contact details by an employer on %SITE_NAME%"; # Employers - request.php

$label["employer_request_sent"] = "Your request to the candidate was sent by email. You will be able to view their contact details and other information once they accept the request. "; # Employers - request.php
$label["employer_request_continue"] = "Continue"; # Employers - request.php
$label["employer_request_send_button"] = "Send Request"; # Employers - request.php

$label['request_history_date']="Request Date";
$label['request_history_employer']="Employer";
$label['request_history_has_permission']="Permission to view?";
$label['request_history_permission']="Change Permission";
$label['request_history_requested']="Requested";
$label['request_history_granted']="Granted";
$label['request_history_refused']="Refused";
$label['request_history_yes_grant']="Yes - Grant";
$label['request_history_no_refuse']="No - Refuse";

# Employer resume alerts

$label["employer_resume_alerts_head"] = "Email Daily Resume Alerts"; # Employers - alerts.php
$label["employer_resume_alerts_intro"] = "Here you can setup your Email Alerts<br>Click \"Save\" after you have finished making changes."; # Employers - alerts.php
$label["employer_resume_alerts_activate"] = "<b>Do you want to receive <br>daily alerts to your Inbox?</b>"; # Employers - alerts.php 
$label["employer_resume_alerts_yes"] = "YES"; # Employers - alerts.php
$label["employer_resume_alerts_no"] = "NO"; # Employers - alerts.php 
$label["employer_resume_alerts_email"] = "Your Email Address:"; # Employers - alerts.php 
$label["employer_resume_alerts_keywords"] = "Only send an alert for the following keyword(s):"; # Employers - alerts.php
$label["employer_resume_alerts_keywords_eg"] = "(eg. USA America Canada) "; # Employers - alerts.php
$label["employer_resume_alerts_submit"] = "Submit"; # Employers - alerts.php (button)
$label["employer_resume_alerts_saved"] = "Changes Saved."; # Employers - alerts.php
$label['package_resume_alerts_link'] = "<a href=\"search.php\">Click here</a> to view resumes."; # Employers - alerts.php
$label["employer_resume_alerts_optional"] = "Optional: You may filter which resumes will be emailed to you. Fill in one or more of the options that must match your preference. Check 'Enable Filter' to activate the filter. (Tip: Filling in less can give you more results)";
$label["employer_resume_alerts_filter_enable"] = "Enable filter.";
# manager.
$label["employer_manager_head"]="Job Post Manager"; # Employers - manager.php

$label["employer_manager_online"]="Posts - Online"; # Employers - manager.php
$label["employer_manager_offline"]="Posts - Offline (Waiting for approval, expired, or not approved)"; # Employers - manager.php
$label["employer_manager_deleted_posts"] = "Deleted %COUNT% post(s)"; # Employers - manager.php
$label["employer_manager_not_selected_del"] = "Please select one or more posts to delete";
$label["employer_manager_not_selected_exp"] = "Please select one or more posts to expire";
$label["0_posts_on_file"] = "0 posts on file";
$label['employer_manager_expired_posts'] = "Expired %COUNT% post(s)"; # Employers - manager.php
# post window
$label["employer_post_window_header"]  = "Job Post Preview";  # Employers - post_window.php

$label['employer_post_delete_button'] = "Delete";
$label['employer_post_delete_confirm'] = "Delete the selected posts(s), are you sure?";

$label['employer_post_expire_button'] = "Expire";
$label['employer_post_expire_confirm'] = "Expire the selected post(s), are you sure?";
####

# Employer applications

# Employer's Applications 

$label["emp_app_head"]="Manage Applications"; # Employer - apps.php (online application history)
$label["emp_app_intro"]="Here you can view a log of all the applications that have been emailed to you."; # Employer - apps.php (online application history)
$label["emp_app_date"]="Date";  # Employer - apps.php (online application history)
$label["emp_app_title"]="Job Title";  # Employer - apps.php (online application history)
$label["emp_app_name"]="Applicant's Name";  # Employer - apps.php (online application history)
$label["emp_app_location"]="Location";  # Employer - apps.php (online application history)
$label["emp_app_advertisor"]="Advertiser";  # Employer - apps.php (online application history)
$label["emp_app_email"]="Email";  # Employer - apps.php (online application history)
$label["emp_app_cover_letter"]="Cover Letter:";  # Employer - apps.php (online application history)
$label["emp_app_no_apps"]="- No applications found.";  # Employer - apps.php (online application history)
$label["emp_app_name_hidden"]="Name Hidden";
$label["emp_app_email_hidden"]="Email Hidden";

$label['emp_app_delete']= "Delete what's selected, are you sure?"; # Employer - apps.php
$label['emp_app_del_button']="Delete";
$label['emp_app_deleted']="Application(s) deleted";
$label['emp_app_no_select']="No application(s) selected";
$label['emp_app_name_block'] ="Name Blocked";
$label['emp_app_email_block']="Email Blocked";
$label['emp_app_post_title_plural'] = "There are currently %COUNT% applications for the job post titled '<i>%TITLE%</i>', posted on %DATE%";
$label['emp_app_post_title_singular'] = "There is currently 1 application for the job post titled '<i>%TITLE%</i>', posted on %DATE%";
$label['emp_app_list_by_post_heading'] = "Go back to the <a href=\"manager.php\">Post Manager</a> or View <A href=\"apps.php\">All Applications</a>";

# edit.php

#####################
# Orders / Package payment
$label['package_shopping_cart_contents'] = "Shopping Cart Contents"; # Employers posting credits - credits.php (shopping cart heading)


$label['package_invoice_no']="Order #"; # Employer's posting credits -
$label['package_invoice_desr']="Order Description:";# Employer's posting credits -  
$label['package_invoice_quantity']="Number of Posts:";# Employer's posting credits - 
$label['package_invoice_price']="Price:";# Employer's posting credits - 
$label['package_invoice_p_type']="Product Type:"; 
$label['package_invoice_date']="Order Date:";# Employer's posting credits -  
$label['package_invoice_confirm']="Confirm &amp; Pay &gt;&gt;";# Employer's posting credits - 
$label['package_invoice_cancel']="Cancel this order";# Employer's posting credits - 
$label['package_invoice_r_u_sure']="Cancel this order, are you sure?";# Employer's posting credits - 
$label['package_invoice_pr_posts'] = "Premium Posts";
$label['package_invoice_std_posts'] = "Standard Posts";
$label['package_invoice_status'] = "Status";
#####

####  Invoice statuses
$label['invoice_status_in_cart'] = "Selected";
$label['invoice_status_confirmed'] = "Confirmed";
$label['invoice_status_completed'] = "Completed";
$label['invoice_status_cancelled'] = "Cancelled";
$label['invoice_status_pending'] = "Pending";
$label['invoice_status_reserved'] = "Reversed";
$label['invoice_status_expired'] = "Expired";
$label['invoice_status_void'] = "Void";

# PAYMENT MODULES

##################
# 2Checkout
 
$label['_2chekout_payment_completed']="Thank you. Your order was successfully completed. Please <a href=\"%EMPLOYER_LINK%\">click here</a> to continue."; # 2Checkout successfully completed %EMPLOYER_LINK%

$label['_2checkout_payment_pending']="<h3>Thank you. Your order is pending while the funds are cleared by 2Checkout. This may take a few days. Please <a href='%%EMPLOYER_LINK'>click here.</a> to continue</h3>"; # 2Checkout payment pending %EMPLOYER_LINK%

$label['package_payment_confirm']="Please confirm your order to continue.";# Employer's posting credits - 
$label['package_invoice_note']="Note: If you have already made a payment for the above order, please allow some time for the order to be processed.";# Employer's posting credits - accounting_functions.php 
# 
$label['package_cart_head']="Go Back, or confirm and make a payment:";# Employer's posting credits - 
$label['package_cart_goback']="&lt;- Go Back";# Employer's posting credits -
$label['package_cart_confirm']=", or confirm and make a payment:";# Employer's posting credits - 
$label['package_cart_credits']="Posting Credits";# Employer's posting credits - credits.php
$label['package_cart_items']="You have %ITEMS% item(s) in your cart.";# Employer's posting credits - 
$label['package_cart_checkout']="Click here to go to checkout";# Employer's posting credits - 

$label['package_header']="Here you can order credits for posting job advertisements on this website. Select the package that you want, and press the button to place an order. Payments are processed instantly via secure credit card transaction. We accept Visa, MasterCard, Discover and American Express, and more. Please contact us for questions or support.";# Employer's posting credits - 

$label['package_std_head']="Order Posting Credits";# Employer's posting credits - credits.php
$label['package_std_select']="Select an option to place an order.";# Employer's posting credits - 

$label['package_std_option']="Option";# Employer's posting credits - credits.php
$label['package_std_posts']="Quantity";# Employer's posting credits - credits.php
$label['package_std_price']="Price";# Employer's posting credits - credits.php
$label['package_std_place_order']="Place Order &gt;&gt;";# Employer's posting credits - credits.php
$label['package_prm_head']="Order Premium Ads";# Employer's posting credits - credits.php
$label['package_prm_head2']="Make your ad stand out above the rest.";# Employer's posting credits - 
$label['package_prm_readmore']="Read more Information about Premium Posts";# Employer's posting credits -p
$label['package_prm_select']="Select an option to place an order.";# Employer's posting credits - 

$label['package_prm_option']="Option";# Employer's posting credits -
$label['package_prm_posts']="Quantity";# Employer's posting credits -
$label['package_prm_price']="Price";# Employer's posting credits - 
$label['package_prm_place_order']="Place Order &gt;&gt;";# Employer's posting credits -

$label['package_credit_balance']="Posting Credit Balance";# Employer's posting credits -
$label['package_std_remain']="Posts Remaining:";# Employer's posting credits - 
$label['package_prm_remain']="Premium Posts Remaining:";# Employer's posting credits - 
$label['package_remain_ultd'] = "Unlimited";

$label['package_rcnt_tansactions']="Recent Orders";# Employer's posting credits -
$label['package_trn_date']="Date";# Employer's posting credits - 
$label['package_trn_id'] = "ID"; # Employer's posting credits - id
$label['package_trn_status']="Status";# Employer's posting credits - 
$label['package_trn_item']="Item";# Employer's posting credits - 
$label['package_trn_amount']="Amount";# Employer's posting credits - 
$label['package_trn_no_data']="No data available.";# Employer's posting credits - 


#####################
# Invoice / Subscription payment
$label['subscription_shopping_cart_contents'] = "Shopping Cart Contents";
$label['subscription_invoice_no']="Order #";# Employer's subscription - 
$label['subscription_invoice_descr']="Order Description:";# Employer's subscription - 
$label['subscription_invoice_status']="Status:";# Employer's subscription 
$label['subscription_invoice_quantity']="Subscription Period (Months):";# Employer's subscription - 
$label['subscription_invoice_price']="Price:"; # Employer's subscription -  
$label['subscription_invoice_date']="Order Date:"; # Employer's subscription - 
$label['subscription_invoice_confirm']="Confirm &amp; Pay &gt;&gt;"; # Employer's subscription - 
$label['subscription_invoice_cancel']="Cancel this order"; # Employer's subscription - 
$label['subscription_invoice_r_u_sure']="Cancel this order, are you sure?"; # Employer's subscription -
$label['subscription_invoice_note']="Note: If you have already made a payment for the above order, please allow some time for the order to be processed."; # Employer's subscription - 

$label['subscription_head1'] = "Select a Subscription Plan";# Employer's subscription 
$label['subscription_head2'] = "Please select a subscription plan:"; # Employer's subscription 
$label['subscription_option'] = "Option"; # Employer's subscription - 
$label['subscription_price_'] = "Price"; # Employer's subscription
$label['subscription_description_'] = "Description"; # Employer's subscription
$label['subscription_add_to_cart'] = "Place Order &gt;&gt;"; # Employer's subscription

$label['subscription_invoice_awaiting']="Awaiting Payment...";
$label['subscription_invoice_confirm']="Confirm...";

$label['subscription_is_now_active']="Your subscription is now active. <a href='search.php'>You can view resumes here.</a>";
$label['subscription_quota'] = "<i>For the period between %START_DATE% and %END_DATE% -</i>";
$label['subscription_views_quota']="You can do <b>%QUOTA%</b> resume views. (<b>%TOTAL%</b> views)"; 
$label['subscription_posts_quota']="You can post <b>%QUOTA%</b> jobs (<b>%TOTAL%</b> posted)."; 
$label['subscription_p_posts_quota']="You can post <b>%QUOTA%</b> premium jobs (<b>%TOTAL%</b> posted)."; 
$label['subscription_quota_u'] = "Until %DATE% you can:";
$label['subscription_views_quota_u']="- View an unlimited number of resumes."; 
$label['subscription_posts_quota_u']="- Post an unlimited number of jobs."; 
$label['subscription_p_posts_quota_u']="- Post an unlimited number of premium jobs.";

$label['package_invoice_awaiting']="Awaiting Payment...";
$label['package_invoice_confirm']="Confirm...";

##
$label['subscription_go_back']="&lt;- Go Back"; # Employer's subscription -
$label['subscription_confirm']=", or confirm and make a payment:"; # Employer's subscription 
$label['subscription_sub_to_view']="Subscription to view resumes"; # Employer's subscription

$label['subscription_details']="Subscription Details"; # Employer's subscription - 
$label['subscription_date']="Subscription Date"; # Employer's subscription
$label['subscription_duration']="Duration"; # Employer's subscription 
$label['subscription_until']="Until"; # Employer's subscription
$label['subscription_status']="Status"; # Employer's subscription
$label['subscription_months_singular']="month"; # Employer's subscription 
$label['subscription_months_plural']="months"; # Employer's subscription 
$label['subscription_cancelled']="Cancelled"; # Employer's subscription 
$label['subscription_subscribe_to']="Subscribe to %SITE_NAME%"; # Employer's subscription 
$label['subscription_you_have']="You have %COUNT% item(s) in your cart.";  # Employer's subscription - 
$label['subscription_chekout']="Click here to go to checkout"; # Employer's subscription - 
$label['subscription_status_info']="Status Information"; # Employer's subscription - 

$label['subscription_status_info_list']="<b>Completed</b> = <i>Your subscription is active, and it will be active until the end of the term.</i></p>
<p><b>Selected</b> =<i> You have selected a subscription plan, it has been placed on order.</i></p><p>
<p><b>Awaiting Payment</b> =<i> You have confirmed this subscription and now we are waiting to receive your payment. </i></p><p>
<p><b>Pending</b> =<i> Your payment is Pending. Your subscription will be Active once your payment is processed.</i></p>
<p><b>Reversed</b> = <i>This payment was refunded / reversed.</i></p>
<p><b>Expired</b> = <i>This subscription expired.</i></p>"; # Employer's subscription 


$label['subscription_recent_trn']="Recent Subscription Orders"; # Employer's subscription - hp

$label['subscription_hist_date']="Date"; # Employer's subscription -
$label['subscription_hist_id']="ID"; # Employer's subscription -

$label['subscription_hist_status']="Status"; # Employer's subscription 
$label['subscription_hist_item']="Item"; # Employer's subscription 
$label['subscription_hist_amount']="Amount"; # Employer's subscription 
$label['subscription_hist_refund']="Refund for"; # Employer's subscription 
$label['subscription_hist_payment']="Payment for"; # Employer's subscription 
$label['subscription_hist_nodata']="No data available."; # Employer's subscriptio

$label['subscr_can_view_resumes'] = "View resumes";
$label['subscr_can_view_blocked'] = "(Access to blocked fields)";
$label['subscr_can_post_unlimited'] ="Post unlimited job ads";
$label['subscr_can_post_unlimited_pr'] = "Post unlimited premium job ads";


$label['subscr_can_post_quota']="<b>%QUOTA%</b> free Job Posts per month";
$label['subscr_can_prost_quota']="<b>%QUOTA%</b> free Premium Job Posts per month";

$label['subscr_can_view_resumes_q'] = " (Quota of %QUOTA% views per month)";
$label['subscr_can_post_q']="Post jobs (%QUOTA% free posts per month)";
$label['subscr_can_post_pr_q']="Post premium jobs (%QUOTA% free premium posts per month)";
#payment button, payment options

# Employers Membership  

$label['emp_member_header']  = "Select a Membership Option";
$label['emp_member_sub_head']  = "Please select a Membership option from below and click on the 'Place Order' button to continue.";
$label['emp_member_option']  = "Option";
$label['emp_member_price']  = "Price";
$label['emp_member_descr']  = "Description";
$label['emp_member_placeorder']  = "Place Order &gt;&gt;";
$label['emp_member_your']  = "Your Membership";
$label['emp_member_details']  = "Membership Details";
$label['emp_member_date']  = "Membership Date";
$label['emp_member_id']  = "ID";
$label['emp_member_duration']  = "Membership Duration";
$label['emp_member_ends']  = "Membership Ends";
$label['emp_member_start']  = "Membership Status";
$label['emp_member_cancel']  = "Membership cancelled";
$label['emp_member_active']  = "Your membership is now active";
$label['emp_member_statusinf']  = "Status Information";
$label['emp_member_recent']  = "Recent Transactions";
$label['emp_member_date']  = "Date";
$label['emp_member_item']  = "Item";
$label['emp_member_stat']  = "Status";
$label['emp_member_amount']  = "Amount";
$label['emp_member_await']  = "Awaiting Payment..";
$label['emp_member_confirm']  = "Confirm &amp; Pay &gt;&gt;";
$label['emp_member_nodata']  = "No data available.";

$label['emp_plan_order_nosel'] = 'Please go back and select a posting plan';
$label['emp_sub_order_nosel'] = 'Please go back and select a subscription plan';
$label['emp_mem_order_nosel'] = 'Please go back and select a membership plan';
$label['can_mem_order_nosel'] = 'Please go back and select a membership plan';
$label['membership_please_wait']  = "<center>Please Wait.. You will be now taken to the <b>Membership  page</b>. <a href=\"membership.php\">Click here</a> if you do not want to wait. </center>";

$label['e_membership_status_info_list']="<b>Completed</b> = <i>Your membership is active, and it will be active until the end of the term.</i></p>
<p><b>Selected</b> =<i> You have selected a membership plan, it has been placed on order.</i></p><p>
<p><b>Confirmed</b> =<i> You have confirmed this membership. </i></p><p>
<p><b>Pending</b> =<i> Your payment is Pending. Your membership will be Active once your payment is processed.</i></p>
<p><b>Reversed</b> = <i>This payment was refunded / reversed.</i></p>
<p><b>Expired</b> = <i>This membership expired.</i></p>"; # Employer's membership 

$label['member_order_id'] = "Order #";
$label['member_ord_descr'] = "Order Description:";
$label['member_duration'] = "Membership Period (Months)";
$label['member_unlimited'] = "Unlimited";
$label['member_price'] = "Price";
$label['member_status'] = "Status";
$label['member_not_expire'] = "Does not expire";
$label['membership_go_back']="&lt;- Go Back"; # Employer's subscription -

$label['member_membership_forever'] = "Continual";
$label['member_membership_not_end'] = "Does not end";

$label['candidate_order_confirm']="Please confirm your order";  # myjobs/order.php

$label['member_months_singular']="month"; # Employer's subscription 
$label['member_months_plural']="months"; # Employer's subscription 

# advertisers/thanks.php

$label['e_thanks_payment_return']= "Thank you!";

$label['payment_please_select']="Please select a payment option.";

$label['payment_bank_heading']="Please deposit %INVOICE_AMOUNT% to the following account:";
$label['payment_bank_name'] ="Bank";
$label['payment_bank_ac_name']="Account Name:";
$label['payment_bank_ac_number']="Account Number:";
$label['payment_bank_address']="Branch Address:";
$label['payment_bank_swift']="SWIFT code:";
$label['payment_bank_note']="To speed up your payment, please quote your Order code (%INVOICE_CODE%). Send an email to %CONTACT_EMAIL% after you have completed making the payment. Thank you.";
$label['payment_select_option']="Please select your preferred payment option:";
$label['payment_paypal_option']="PayPal (Instant & Secure Card Payment)";
$label['payment_bank_option'] = "Bank Transfer";

$label['payment_check_option']= "Check / Money Order";
$label['payment_nochex_option']= "NOCHEX (Secure Card Payment)";
$label['payment_2co_option']= "2CO (Secure Card Payment)";
$label['payment_check_heading']="Please make your Check / Money Order out to:";
$label['payment_check_to_name']="Name:";
$label['payment_check_to_address']="Address:";
$label['payment_check_amount']="<b>Amount:</b> %INVOICE_AMOUNT%";
$label['payment_check_sub_head'] = "<h3>Invoice</h3><br><br>Number: %INVOICE_CODE%<br>Terms: DUE ON RECEIPT<br>";
##################################################################
# Candidates
##################################################################

$label['c_membership_heading'] = "Select a Membership Option"; # membership.php
$label['c_membership_description'] = "Please select a Membership option from below and click on the 'Place Order' button to continue.";

$label['c_membership_opt_col'] = "Option";
$label['c_membership_price_col'] = "Price";
$label['c_membership_desc_col'] = "Description";
$label['c_membership_button_order'] = "Place Order &gt;&gt;";

$label['c_membership_your_mem'] = "Your Membership";
$label['c_membership_m_details'] = "Membership Details";

$label['c_membership_history_date'] = "Membership Date";
$label['c_membership_history_duration'] = "Membership Duration";
$label['c_membership_history_ends'] ="Membership Ends";
$label['c_membership_history_status'] ="Membership Status";
$label['c_membership_months_singular'] = "Month";
$label['c_membership_months_plural'] = "Months";
$label['c_membership_membership_forever'] = "Continual";
$label['c_membership_membership_not_end'] = "Does not end";
$label['c_membership_cancelled'] = "Membership cancelled";
$label['c_membership_active'] = "Your membership is now active";
$label['c_membership_stausinfo'] = "Status Information";
$label['c_membership_recnt_trn'] = "Recent Transactions";

$label['c_membership_trn_date'] = "Date";
$label['c_membership_trn_item'] = "Item";
$label['c_membership_trn_status'] = "Status";
$label['c_membership_trn_amount'] = "Amount";

$label['c_membership_trn_awaiting'] = "Awaiting Payment..";
$label['c_membership_trn_confirm'] = "Confirm &amp; Pay &gt;&gt;";

$label['c_membership_nodata'] = "No data available.";




//////////////////

$label["candidate_login_seeker_id"] = "Job Seeker ID"; #  Candidate's login form on the front page
$label["candidate_login_password"] = "Password"; #  Candidate's login form on the front page
$label["candidate_login_button"] = "Login"; #  Candidate's login form on the front page

$label["c_alert_head"] ="Email Daily Job Alerts"; # Candidates - alerts.php
$label["c_alert_head2"] ="Here you can setup your Job Alerts";  # Candidates - alerts.php
$label["c_alert_intro"] ="Click \"Save\" after you have finished making changes.";  # Candidates - alerts.php
$label["c_alert_receive"] ="Do you want to receive <br>daily alerts to your Inbox?";  # Candidates - alerts.php
$label["c_alert_yes"] ="YES"; # Candidates - alerts.php
$label["c_alert_no"] ="NO"; # Candidates - alerts.php
$label["c_alert_email"] ="Your Email Address:";  # Candidates - alerts.php
$label["c_alert_keywords"] ="Only send an alert <br>if the <b>job description</b><br> contains the following words:"; # Candidates - alerts.php

$label["c_alert_optional"] = "Optional: You may filter which jobs will be emailed to you. Fill in one or more of the options that must match your preference. Check 'Enable Filter' to activate the filter. (Tip: Filling in less can give you more results)";
$label["c_alert_filter_enable"] = "Enable filter.";
$label['c_alert_saved']="Changes Saved.";
$label['c_alert_submit_button']="Save";
# Candidate's Applications

$label["c_app_head"]="My Applications"; # Candidates - apps.php (online application history)
$label["c_app_intro"]="Here you can view your past applications. Applications will be shown here for %POSTS_DISPLAY_DAYS% days."; # Candidates - apps.php (online application history)
$label["c_app_date"]="Date";  # Candidates - apps.php (online application history)
$label["c_app_title"]="Job Title";  # Candidates - apps.php (online application history)
$label["c_app_name"]="Applicant's Name";  # Candidates - apps.php (online application history)
$label["c_app_location"]="Location";  # Candidates - apps.php (online application history)
$label["c_app_advertisor"]="Advertiser";  # Candidates - apps.php (online application history)
$label["c_app_email"]="Email";  # Candidates - apps.php (online application history)
$label["c_app_cover_letter"]="Cover Letter:";  # Candidates - apps.php (online application history)
$label["c_app_no_apps"]="- No applications found.";  # Candidates - apps.php (online application history)
$label["c_app_name_hidden"]="Name Hidden";
$label["c_app_email_hidden"]="Email Hidden";
$label['c_app_delete']= "Delete what's selected, are you sure?"; # Candidates - apps.php
$label['c_app_delete_button'] = "Delete";
$label['c_app_deleted']="Application(s) deleted";
$label['c_app_no_select']="No application(s) selected";
# edit.php

$label['edit_status_upd'] = "Your status was updated.";

$label["c_edit_intro"]="Please complete your resume carefully. The more details given, the better chance you have of an employer contacting you. For the Experience and Education fields, you may simply copy and paste it from your original resume. <p> Click \"Save\" after you have finished making changes.</p> <p> Fields marked with <FONT SIZE=\"4\" COLOR=\"#FF0000\">*</FONT> are required.</p>"; # Candidates - edit.php (edit resume)

$label["c_edit_intro2"]="Edit Online Resume & Announce Availability"; # Candidates - edit.php (edit resume)

$label["c_edit_saved"]="Your Resume was saved. <a href='resume.php'>View your resume.</a>"; # Candidates - edit.php (edit resume)

# forgot.php

$label["c_forgot_head"]="Forgot my password"; # Candidates - forgot.php
$label["c_forgot_enter_email"]="Enter your Email address:"; # Candidates - forgot.php
$label["c_forgot_changed"]="A new password was emailed to %SEND_TO%. Please check your email in a few minutes. You will be able to log in with the new password here:"; # Candidates - forgot.php
$label["c_forgot_failed"]="Failed sending an email, please contact us to report this problem."; # Candidates - forgot.php
$label["c_forgot_not_found"]="Email not found on the system. Try again."; # Candidates - forgot.php
$label["c_forgot_continue"]="Go back to the Job Board"; # Candidates - forgot.php

# index.php 

$label["c_index_greeting"]="Hi, %FIRST_NAME% %LAST_NAME%! Welcome to %SITE_NAME%"; # Candidates - index.php
$label["c_index_views"]="Your resume was viewed %COUNT% times by employers."; # Candidates - index.php
$label["c_index_resume_act"]= "Your resume is currently active.";
$label["c_index_resume_sus"]= "Your resume is currently suspended and employers are not be able to view it.";
$label["c_index_status"]="Status Summary"; # Candidates - index.php
$label["c_index_no_resume"]="<b><FONT COLOR=\"#FF0000\">A Tip From Us:</b></h3><b> Want to increase your chances of finding a job? Then go to <a href=\"edit.php\">Submit your Resume</a> now! Only fill in the fields that you want, and there is an option for keeping your details anonymous too! Once filled in, employers will be able to search for you. Hundreds of subscribed employers will also receive an alert by email, informing them of you.</b></p>"; # Candidates - index.php
$label["c_index_menu"]="Main Menu"; # Candidates - index.php
$label["c_index_edit"]="- <a href=\"edit.php\">Edit your Resume</a> to get Employers contact you! Announce your availability to the 100's of Employers that visit this site."; # Candidates - index.php
$label["c_index_view"]="- <a href=\"resume.php\">View your resume</a>, view it as seen by the Employers";
$label["c_index_jobs"]="- <a href=\"search.php\">Job Search</a>. Search all job openings. (<a href=\"save.php\">My Saved Jobs</a>, <a href=\"apps.php\">My Applications</a>)"; # Candidates - index.php
$label["c_index_alerts"]="- <a href=\"alerts.php\">Email Alerts</a>. Get alerts of new jobs straight to your Inbox."; # Candidates - index.php
$label["c_index_manage"]="- Manage your account: <a href=\"password.php\">Change password</a> / <a href=\"logout.php\">Logout</a> from the system."; # Candidates - index.php

# login.php
$label["c_loginform_title"] = "Candidate's Login";
$label["c_login_logging"]="Logging in to %SITE_NAME% ..."; # Candidates - login.php

$label["c_login_notvalidated"]="This account is not activated. Go back to the <a href='%BASE_HTTP_PATH%'>Job Board</a>"; # Candidates - login.php

$label["c_login_welcome"]="Welcome back %FNAME% %LNAME% You have successfully signed in as %USERNAME%<p>You will be redirected to the index page in 2 seconds or click <a href=\"index.php\">here</a> if you do not want to wait."; # Candidates - login.php, the index.php will be replaced by whatever page is in $_REQUEST['page']


 
$label["c_login_invalid_msg"]="<p>Error: Username/Password combination is incorrect. <a target=\"_parent\" href=\"%LOGIN_PAGE%\">Try again...</a><p> If you have forgotten your password, please <a target=\"_parent\" href=\"%FORGOT_PAGE%\">Click Here</a>.<br>Please <a target=\"_top\" href=\"%SIGNUP_PAGE%\">Sign Up</a> if you are a new user."; # Candidates - login.php

#longin_functions.php

$label["c_flogin_header"]="Login to your personal area"; # Candidates - login_functions.php
$label["c_flogin_username"]="Username"; # Candidates - login_functions.php
$label["c_flogin_password"]="Password"; # CAndidates - login_functions.php
$label["c_flogin_login"]="Login"; # candidates - login_functions.php (button)
$label["c_flogin_forgotten"]="Forgotten your Password?"; # Candidates - login_functions.php
$label["c_flogin_join_now"]="Join now!"; # candidates - login_functions.php

$label["c_flogin_advertiser"]="Are you an employer / advertiser?";
$label["c_flogin_jobseek"]="Job Seeker's Login";

# signup
$label['c_signup_title'] = "Job Seeker's Signup";
$label["c_signup_header"]="Announce your availability to employers, post your resume, receive daily job alerts and more!"; # Candidates - signup.php
$label["c_signup_intro_seeker"]="Sign up as a new Job Seeker."; # Candidates - signup.php
$label["c_signup_error1"]="- Passwords do not match"; # Candidates - signup.php
$label["c_signup_error2"]="- Please fill in your first name";# Candidates - signup.php
$label["c_signup_error3"]="- Please fill in your last name";# Candidates - signup.php
$label["c_signup_error4"]="- Please fill in Your username";# Candidates - signup.php
$label["c_signup_error5"]="- The username %USERNAME% is in use. Please choose a different username";# Candidates - signup.php

$label["c_signup_error6"]="- Please fill in Your Password";# Candidates - signup.php
$label["c_signup_error7"]="- Please confirm your password";# Candidates - signup.php
$label["c_signup_error_pw_too_weak"] = "- Password is too weak. It must consist of 6 characters or more.<br>"; # Employers - signup.php

$label["c_signup_error8"]="- Please fill in a valid email address";# Candidates - signup.php
$label["c_signup_error9"]="Cannot continue due to the following Errors:";# Candidates - signup.php
$label["c_signup_error10"] = "- Cannot create a new account: This email address is already in use. (Did you <a href='forgot.php'>forget your username or password?</a>) "; # Candidates - signup.php
$label["c_signup_ok"]="You have successfully signed up to %SITE_NAME%! You can now sign in to your account and announce your availability.<br><br>If you ever encounter any problems, bugs or just have a question or suggestion, feel free to contact %SITE_CONTACT_EMAIL%";# Candidates - signup.php
$label["c_signup_error11"] = "- Your username may contain letters, or numbers, but not any other characters.";
$label["c_signup_continue"]="Click Here to Continue";# Candidates - signup.php

$label["c_signup_failed"]="Failed sending an email, please contact support and provide include your username, first name and last name in your error report."; # Candidates - signup.php
$label["c_signup_fname"]="First Name"; # Candidates - signup.php
$label["c_signup_lname"]="Last Name"; # Candidates - signup.php
$label["c_signup_memberid"]="Username"; # Candidates - signup.php
$label["c_signup_memberid2"]="(Choose Your sign-in ID. Use letters, or numbers, but don't use spaces.)"; # Candidates - signup.php
$label["c_signup_password"]="Password"; # Candidates - signup.php
$label["c_signup_password2"]="Confirm Password";# Candidates - signup.php
$label["c_signup_email"]="Your E-mail";# Candidates - signup.php
$label["c_signup_newsletter"]="Receive Newsletter";# Candidates - signup.php
$label["c_signup_alerts"]="Auto Notification on new Jobs";# Candidates - signup.php
$label["c_signup_yes"]="Yes";# Candidates - signup.php
$label["c_signup_no"]="No";# Candidates - signup.php
$label["c_signup_submit"]="Submit";# Candidates - signup.php
$label["c_signup_goback"] = "Back to the <a href='../index.php'>Job Board.</a>";# Candidates: signup.php
#select langauge page language.php
$label["c_lang_title"] = "Change Language"; # Candidate - language.php
$label["c_lang_note"] = "Select Your Preferred Language <p> Click \"Save\" after you have finished making changes"; # Employer - language.php
$label["c_lang_label"] = "Select Your Language:"; # Candidate - language.php
$label["c_lang_button_label"] = "Submit"; # Candidate - language.php
$label["c_lang_saved"] ="Language Saved"; # Employer - language.php

# logout

$label["c_logout_msg"]="<h3>You have logged out.</h3> <a href='../'>Go to the Job Board</a>"; # Candidates - logout.php

# menu.php
$label["c_menu_select_lang"] = "Language..."; # Employer - menu.php
$label["c_menu_account"]="Account"; # Candidates - menu.php
$label["c_menu_main"]="Main Page"; # Candidates - menu.php
$label["c_menu_pwchange"]="Change Password..."; # Candidates - menu.php
$label["c_menu_ac_details"]="Account Details..."; # Candidates - menu.php
$label["c_menu_logout"]="Logout"; # Candidates - menu.php
$label["c_menu_resume"]="R&#233;sum&#233;"; # Candidates - menu.php
$label["c_menu_view"]="View Resume"; # Candidates - menu.php
$label["c_menu_edit"]="Edit Resume"; # Candidates - menu.php
$label["c_menu_jobs"]="Jobs"; # Candidates - menu.php
$label["c_menu_search"]="Search Jobs"; # Candidates - menu.php
$label["c_menu_category"] = "Browse Jobs"; # Candidates - menu.php
$label["c_menu_saved"]="My Saved Jobs"; # Candidates - menu.php
$label["c_menu_apps"]="My Applications"; # Candidates - menu.php
$label["c_menu_alerts"]="Job Alerts"; # Candidates - menu.php
$label["c_menu_help"]="Help"; # Candidates - menu.php
$label["c_menu_contents"]="Contents and Index"; # Candidates - menu.php
$label["c_menu_about"]="About %SITE_NAME%"; # Candidates - menu.php
$label["c_menu_logout"]= "Logout"; # Candidates - menu.php
$label["c_menu_membership"] = "Membership Details";


#password

$label["c_menu_pass_header"]= "Change Password"; # Candidates - password.php
$label["c_pass_change_error"] = "Cannot change your password due to the following errors:";
$label["c_menu_pass_incorrect"]= "Your Old Password is incorrect!"; # Candidates - password.php
$label["c_menu_pass_oldblank"]= "Your Old Password is blank!"; # Candidates - password.php
$label["c_menu_pass_new_blank"]= "Your New Password is blank!"; # Candidates - password.php
$label["c_menu_pass_new_blank2"]= "Your Confirmed New Password is blank!"; # Candidates - password.php
$label["c_menu_pass_notmatch"]= "Your New Password doesn't match with the Confirmed New Password!"; # Candidates - password.php
$label["c_menu_pass_ok"]= "Password Changed."; # Candidates - password.php
$label["c_menu_pass_intro"]= "Here you can change your password"; # Candidates - password.php
$label["c_menu_pass_intro2"]= "Click \"Save\" after you have finished making changes."; # Candidates - password.php
$label["c_menu_pass_oldpass"]= "Old Password"; # Candidates - password.php
$label["c_menu_pass_newpass"]= "New Password"; # Candidates - password.php
$label["c_menu_pass_newpass2"]= "Confirm New Password"; # Candidates - password.php
$label["c_menu_pass_submit"]= "Submit";
#permit

$label["c_permit_success"] = "The request for your contact details was successfully granted. Continue to <a href='%BASE_HTTP_PATH%'>%SITE_NAME%</a>"; # Candidates - permit.php
$label["c_permit_weclome"] = " Welcome to <a href='%BASE_HTTP_PATH%'>%SITE_NAME%</a>"; # Candidates - permit.php

$label['c_request_delete_button'] = "Delete";
$label['c_request_delete']="Delete what's selected, are you sure?"; 
#resume

$label["c_resume_header"]= "View My Resume"; # Candidates - resume.php
$label["c_resume_intro"]= "Here is your resume as seen by the employers. You may suspend your resume at any time."; # Candidates - resume.php
$label["c_resume_status"]= "Resume Status"; # Candidates - resume.php
$label["c_resume_set_to"]= "Set my resume to:"; # Candidates - resume.php
$label["c_resume_active"]= "Active"; # Candidates - resume.php
$label["c_resume_Suspended"]= "Suspended"; # Candidates - resume.php
$label["c_resume_note_text"]= "Note: You have selected to remain anonymous on this website. Your name, telephone numbers, email address and photo will not be shown to an employer, unless an employer sends a request, and you give your explicit permission."; # Candidates - resume.php
$label["c_resume_note"]= "Note:"; # Candidates - resume.php
$label["c_resume_notfound"]= "You don't have a resume on file."; # Candidates - resume.php
$label["c_resume_hide"] = "This candidate has chosen to hide their personal details"; # Candidates - resume.php
$label["c_resume_hide_allowed"] ="<b>*** This candidate allowed you to view their hidden details!</b>";
#save.php
$label['save_job_delete_button'] = "Delete";
$label['save_job_deleted'] ='Deleted %COUNT% saved post(s)';
$label["c_save_postid"]= "Job #%POST_ID% saved"; # Candidates - save.php
$label["c_save_my_jobs"]= "My Saved jobs"; # Candidates - save.php
$label["c_save_intro"]= "Here you can view your saved jobs."; # Candidates - save.php
$label["c_save_notfound"]= "- No saved jobs found."; # Candidates - save.php

# help
$label["c_help_heading"]= "Help"; # Candidates - help.php
$label["c_help_text"] = "Help is not available yet. You can email us if you have any comments / questions. We would be glad to help you out."; # Candidates - help.php main body (edit this message from Admin->Help Pages)
$label['c_about_text'] = "Copyright 2008 by %SITE_NAME%. You can email %SITE_CONTACT_EMAIL% if you have any comments / questions."; # candidates - about.php
$label['c_about_head'] = "About"; # candidates - about.php

$label['c_back2top'] = "Go Back to Top"; # candidates - browse.php
##################################################################
# MISC INCLUDES
##################################################################

# dynamic_forms.php
$label['bad_words_not_accept'] = "Bad words are not accepted!"; # # include/dynamic_forms.php (validation)

$label['cat_records_not_allow'] ="Cannot add to this category. Please choose from a category below.";
$label['cat_option_choose_another'] = "- choose from below";
$label["find_button"] = "Find"; # include/dynamic_forms.php (button for search)

$label['sel_month_1'] =  "Jan"; # include/dynamic_forms.php (date field - month)
$label['sel_month_2'] =  "Feb"; # include/dynamic_forms.php (date field - month)
$label['sel_month_3'] =  "Mar"; # include/dynamic_forms.php (date field - month)
$label['sel_month_4'] =  "Apr"; # include/dynamic_forms.php (date field - month)
$label['sel_month_5'] =  "May"; # include/dynamic_forms.php (date field - month)
$label['sel_month_6'] =  "Jun"; # include/dynamic_forms.php (date field - month)
$label['sel_month_7'] =  "Jul"; # include/dynamic_forms.php (date field - month)
$label['sel_month_8'] =  "Aug"; # include/dynamic_forms.php (date field - month)
$label['sel_month_9'] =  "Sep"; # include/dynamic_forms.php (date field - month)
$label['sel_month_10'] =  "Oct"; # include/dynamic_forms.php (date field - month)
$label['sel_month_11'] =  "Nov"; # include/dynamic_forms.php (date field - month)
$label['sel_month_12'] =  "Dec"; # include/dynamic_forms.php (date field - month)
$label['sel_box_select'] = "[Select]";
$label['sel_category_select'] = "[Select]"; # include/dynamic_forms.php ([select] - 1st line in categories selection)
$label['sel_category_select_all'] = "Select All"; # include/dynamic_forms.php (select all in categories)
$label['skill_matrix_label_1'] = "Skill or Technology Name"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_label_2'] = "Years of Experience"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_label_3'] = "Skill Rating"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel'] = "[Select]"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel0'] = "less than 1"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel1'] = "1 year"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel2'] = "2 years"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel3'] = "3 years"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel4'] = "4 years"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel5'] = "5 years"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel6'] = "6-10 years"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel7'] = "11-15 years"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel8'] = "16-20 years"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel9'] = "21-25 years"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col2_sel10'] = "over 25"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel'] = "[Select]"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel10'] = "10 (best)"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel9'] = "9"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel8'] = "8"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel7'] = "7"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel6'] = "6"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel5'] = "5"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel4'] = "4"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel3'] = "3"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel2'] = "2"; # include/skill_matrix_files.php (skill matrix field)
$label['skill_matrix_col3_sel1'] = "1 (very basic)"; # include/skill_matrix_files.php (skill matrix field)

$label['vaild_file_ext_error']="- The file type with the ending of %EXT% is not accepted by this website. File types accepted are: %EXT_LIST%";

$label['vaild_image_ext_error']="- The image type with the ending of %EXT% is not accepted by this website. Image types accepted are: %EXT_LIST%";

$label['valid_file_size_error']="- The file %FILE_NAME% is too large.";
# functions.php
$label['available_languages'] = "Available in these languages:"; // include/functions.php (Language selector)

$label['subscribe_bonus_info'] = "<p><i>Subscription Bonus: Subscribe to the Resume Database and <b>receive free posting credits each month!</b> Please see the <a href='subscriptions.php' target='_parent'>subscription page</a> for more details.</i></p>";

$label['subscr_no_free_posts'] = "Note: You have used up all your free credits for the month.";
$label['subscr_allowed_free_posts'] = "Each month you are allowed %CREDITS% free posting(s)";
$label['subscr_allowed_free_p_posts'] = " Each month you are allowed %CREDITS% free premium posting(s)";
$label['subscr_no_free_p_posts'] = "Note: You have used up all your free premium credits for the month.";

##
$label['prem_post_no_credits']="You can make <b>0</b> Premium posts.  <a href='credits.php'>Fill up your credits here.</a>"; # include/functions.php (used by manager.php in Employer's)
$label['prem_post_more_info']="More Information"; # include/functions.php (used by manager.php in Employer's)

$label['prem_post_balance']="You can make <b>%P_POSTS%</b> Premium post(s).";# include/functions.php (used by manager.php in Employer's)
$label['prem_post_unlimited']="You can make unlimited premium posts";

$label['prem_post_post']="Post a new permium job ad."; # include/functions.php (used by manager.php in Employer's)

$label['std_post_post_no_credits'] = "You can make <b>0</b> Standard Job posts.  <a href='credits.php'>Fill up your credits here.</a>"; # include/functions.php (used by manager.php in Employer's)
$label['std_post_post_balance']="You can make <b>%POSTS%</b> Standard Job post(s)."; # include/functions.php (used by manager.php in Employer's)
$label['std_post_unlimited']="You can make unlimited job posts";
$label['prem_post_post'] = "Post a new Premium Job ad."; # include/functions.php (used by manager.php in Employer's)
$label['std_post_post'] = "Post a new Job ad."; # include/functions.php (used by manager.php in Employer's)
$label['credit_status_free']="Standard Job Posts are <b>FREE</b>. No credits are needed."; # include/functions.php (used by manager.php in Employer's)
$label['credit_status_post_free']="Post a new job ad. (Free)"; # include/functions.php (used by manager.php in Employer's)
$label['credit_status_post_new']="Post a new Job ad."; # include/functions.php (used by manager.php in Employer's)


$label["seeker_ac_note"] = "Here you can change your account details <p>Click 'Submit' after you have finished making the changes."; # Seeker - account.php
$label["seeker_ac_updated"] = "Your account details were updated"; # Seeker - account.php
$label["seeker_ac_intro"]="Here you can change your account details"; # Seeker - account.php


$label["payment_posts_completed2"] = "Thank you. Your order was successfully completed. You may <a href='%URL%'> post your jobs </a> now.";


$label["payment_subscription_completed2"] = "Thank you. Your order was successfully completed. You may go to <a href='%URL%'> view resumes </a> now!";

$label["payment_membership_completed"] = "Thank you. Your membership payment was successfully completed!";
$label["payment_return_pending"] = "Your order is pending while the funds are cleared by %PAYMENT_GW%. Please refer to your statement for further details. You will be able to post ads as soon as the payment is cleared.</a>";
$label["payment_return_denied"] = "Sorry - your payment was denied by %PAYMENT_GW%";
$label["payment_return_error"] = "Sorry - there was an error with the payment. Please try again later / try a different payment method";

$label['paypal_ipn_fail'] = "<p align=\"center\">It looks like there was a technical difficulty while processing the payment. To get the status of your order, please go to the <a href=\"credits.php\">Credits Page</a> or <a href=\"subscriptions.php\">Subscription Page</a></p>";

$label['paypal_subscr_manual_review'] = "Dear %NAME%,\n\n".
		"Thank you for your payment.\n".
		"The Administrator of %SITE_NAME% has decided to manually review all\n". 
		"transactions which give access to the resume database.\n".
		"A review should not take long. If you have any questions, please email\n".
		"%SITE_EMAIL%\n\n".
		"Best Regards,\n".
		"Administrator - %SITE_NAME%\n\n".
		"(Order ID:S%INVOICE_ID%)\n";

$label['paypal_subscr_manual_admin'] = "Dear Admin,\n\nA subscription payment is waiting for you to manually Complete in Admin->Subscription Orders.\n"."Here is a copy of the email that was sent.\n==========================================\n";

$label['paypal_subscr_manual_sbj'] = "Pending Subscription Payment";


$label['employer_payment_processing'] = "<p align=\"center\"> Your order is being processed. To get the status of your order, please go to the <a href=\"credits.php\">Credits Page</a> or <a href=\"subscriptions.php\">Subscription Page</a></p>";

$label['employer_payment_processing'] = "<p align=\"center\"> Your order is being processed.</p>";

# Google checkout

$label['payment_google_name'] = 'Google Checkout&trade;'; // displayed on the payment.php page for selecting the payment option
$label['payment_google_descr'] =  'Secure Credit Card Payment with Google Checkout&trade;';// displayed on the payment.php page for choosing the payment option
$label['payment_google_msg'] = 'Thank you for your payment - it is being processed. Please return to <a href="%RETURN_URL%">the site</a> to see the status for this order. If you have any questions, please contact %CONTACT_EMAIL% with your order details'; // displayed after payment on Google Checkout
$label['payment_google_processed'] = 'Google Checkout has processed your order sucessfully. You may view the <a href="%ORD_PAGE%">order details</a>'; // payment/googleCheckout.php - function process_payment_return()
$label['payment_google_pending'] = 'Your order is processing via Google Checkout. Your order\'s status will change to \'Completed\' once it is processed. Please refresh this page in a moment.'; // payment/googleCheckout.php - function process_payment_return()
#paypal
$label['google_status_heading'] = 'Your Google Checkout order status'; // heading for the thanks.php page, after returning from Google Checkout

$label['payment_paypal_name'] = "PayPal";
$label['payment_paypal_descr'] =  "PayPal Secure Credit Card Payment";
$label['payment_paypal_head'] = "Pay with PayPal (Secure credit card payment)";
$label['payment_paypal_accepts'] = "PayPal accepts: Visa, MasterCard";
$label['payment_paypal_bttn_alt'] = "Make payments with PayPal - it's fast, free and secure!";

# 2 checkout

$label['payment_2co_name']="2Checkout";
$label['payment_2co_descr']= "2Checkout - Accepts: Visa, MasterCard, American Express, Discover, JCB, Diners";
$label['payment_2co_submit_butt']="Buy From 2Checkout.com";



# Bannk payment
$label['payment_bank_name'] = "Bank";
$label['payment_bank_descr'] = "Wire Transfer - Funds transfer to a bank account.";
$label['payment_bank_name'] ="Bank";
$label['payment_bank_addr'] ="Bank Address:";
$label['payment_bank_ac_name']="Account Name:";
$label['payment_bank_ac_number']="Account Number:";
$label['payment_bank_branch_number']="Branch number:";
$label['payment_bank_swift']="SWIFT code:";
$label['payment_bank_note']="To speed up your payment, please quote your Order code (%INVOICE_CODE%). Send an email to %CONTACT_EMAIL% after you have completed making the payment. Thank you.";
$label['payment_bank_button']="Wire Transfer";
$label['payment_bank_go_back'] = "<a href=\"%ADV_LINK%\">Go back</a> to your account";
$label['payment_bank_tax'] = "(Tax: %INVOICE_TAX%)";
# Check / money Order 
$label['payment_check_name']="Check / Money Order";
$label['payment_check_descr']= "Mail funds by Check / Money Order.";
$label['payment_check_button']="Check / Money Order";
$label['payment_check_heading']="Send %INVOICE_AMOUNT% to the following:";
$label['payment_check_tax'] = "(Tax: %INVOICE_TAX%)";
//$label['payment_check_note']="To speed up your payment, please quote your Order code (%INVOICE_CODE%). Send an email to %CONTACT_EMAIL% after you have completed making the payment. Thank you.";
$label['payment_check_payable'] = "Payable to:";
$label['payment_check_address'] = "Address to:";


# CC Avenue

$label['payment_ccavenue_name']="CCAvenue"; 
$label['payment_ccavenue_descr']="CCAvenue - Secure credit card payment."; 

$label['pay_by_ccavenue_button']="Pay by CCAvenue";

$label['payment_ccave_go_back'] = "<a href=\"%ADV_LINK%\">Go back</a> to your account";




# Money bookers
$label['pay_by_moneybookers_button']="Pay by moneybookers.com";
$label['payment_moneybookers_descr']="Payment to:";

$label['payment_moneybookers_description']="moneybookers.com Secure Credit Card Payment";
$label['payment_moneybookers_name'] = "MoneyBookers.com";
# egold

$label['pay_by_egold_button']="Pay by e-gold.com";
$label['payment_egold_description'] = "Internet payments backed by 100% Gold";
$label['payment_egold_name'] = "E-gold";

# Authorize.net (SIM)
$label['payment_authnet_description'] = "Authorize.Net - Secure credit card payments";
$label['pay_by_authnet_button']="Pay via Authorize.net";
$label['payment_authnet_name'] = "Authorize.Net";



# NOCHEX

$label['payment_nochex_description']="NOCHEX - Credit Card Payments. Accepts British Pounds.";
$label['payment_nochex_name'] = "NOCHEX";

## payment_manager.php

$label['payment_mab_btt']="Payment Button"; # deprecated
$label['payment_mab_name']="Payment Method";
$label['payment_man_pt']="Payment Type";
$label['payment_man_descr']="Description";
$label['payment_man_butt_proc'] = "Proceed to Payment &gt;&gt;";
$label['payment_man_butt_cancel'] = "Cancel Payment";

# payment.php

$label['payment_cancelled']="Payment cancelled";
$label['payment_mem_cancelled']="Membership payment cancelled";
$label['payment_sub_cancelled']="Subscription payment cancelled";

# Simple date widget (SCW)

$label['scw_today']="Today:";
$label['scw_drag']="click here to drag";
$label['scw_jan']="Jan";
$label['scw_feb']="Feb";
$label['scw_mar']="Mar";
$label['scw_apr']="Apr";
$label['scw_may']="May";
$label['scw_jun']="Jun";
$label['scw_jul']="Jul";
$label['scw_aug']="Aug";
$label['scw_sep']="Sep";
$label['scw_oct']="Oct";
$label['scw_now']="Nov";
$label['scw_dec']="Dec";
$label['scw_sun0']="S";
$label['scw_mon1']="M";
$label['scw_tue2']="T";
$label['scw_wed3']="W";
$label['scw_thu4']="T";
$label['scw_fri5']="F";
$label['scw_sat6']="S";
$label['scw_inv']="The entered date is invalid.";
$label['scw_range']="The entered date is out of range.";
$label['scw_exist']="The entered date does not exist.";
$label['scw_ign']="Invalid date (";
$label['scw_ign2'] =") ignored.";
$label['scw_err1']="Error ";
$label['scw_err1obj']="is not a Date object.";
$label['scw_err2']="Error ";
$label['scw_err2elem']="should consist of two elements.";
$label['ads_info']="<p >There are two different ad formats to choose. Premium Job Posts and Standard Job Posts.</p>
      <p ><b>Premium Job Posts.</b></p>
      <p >Get better visibility and improve the chance of 
      getting more exposure. Premium Posts are always displayed on top of the 
      main page with highlighted colors.</p>
      <p ><b>Standard Job Posts.</b></p>
      <p >Standard job posts are displayed below premium posts, in default 
      colors.</p>"; 

$label['invoice_stat_pending_unpaid'] = "(Invoice Unpaid)";

$label['disp_post_app_url'] = "* To apply, please visit the following page: <A target=\"_blank\" href=\"%APP_URL%\">%APP_URL%</a>";

$label['post_form_app_pref'] = "Application Preference";
$label['post_form_all_online'] = "Allow Online Applications via %SITE_NAME% (recommended)";
$label['post_form_app_url'] = "Re-direct to a custom URL:";
$label['post_form_app_none'] = "Disable / Specify instructions in the description";
$label['rss_subscribe'] = "<a href=\"%RSS_LINK%\">Subscribe</a> to <i>%CATEGORY_NAME%</i>.";

# Images

$label['subscribe_now_button_img'] = "subscribe-now.gif"; # filename to the subscribe now button in your theme's images/ directroy
$label['buy_posts_button_img'] = "buy_standard.gif"; # filename to the buy posts now button in your theme's images/ directroy
$label['buy_p_posts_button_img'] = "buy_premium.gif"; # filename to the buy premium posts now button in your theme's images/ directroy
$label['gmap_move_marker'] = 'Please move the marker on the map by dragging it to the desired location.';


?>