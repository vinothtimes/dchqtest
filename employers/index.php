<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################


require "../config.php";
include('login_functions.php');
include('../include/motd_functions.php'); 


JB_process_login(); 

JB_template_employers_header();  ?>

      <h3 class='welcome_title'><?php 
       $msg = str_replace ( "%firstname%", JB_escape_html($_SESSION['JB_FirstName']), $label["employer_home_welcome_title"]);
       $msg = str_replace ( "%lastname%", JB_escape_html($_SESSION['JB_LastName']), $msg);
		$msg = str_replace ( "%SITE_NAME%", JB_escape_html(JB_SITE_NAME), $msg);
      
	  echo $msg; 
	  
	  ?></h3>
      <div  class='welcome_text'><?php 
	  $label["employer_home_welcome_text"] = str_replace ( "%SITE_NAME%", JB_escape_html(JB_SITE_NAME), $label["employer_home_welcome_text"]);
	  echo $label["employer_home_welcome_text"]; ?></div>
	   
      

      <?php

	  JBPLUG_do_callback('employers_index_top', $A = false); 
      
      $sql = "SELECT count(*) FROM `users`";
      $result = JB_mysql_query($sql);
	  $row = mysql_fetch_row($result);
      $users = $row[0];

	  $now = (gmdate("Y-m-d"));
	  

      $sql = "SELECT count(*) FROM `posts_table` WHERE  user_id='".jb_escape_sql($_SESSION["JB_ID"])."' AND expired='N' ";
      $result = JB_mysql_query($sql) or die(mysql_error());
	  $row = mysql_fetch_row($result);
      $posts = $row[0];
	  
      $sql = "SELECT count(*) FROM `posts_table` WHERE user_id='".jb_escape_sql($_SESSION["JB_ID"])."' AND `approved`='Y' AND expired='N' ";
      $result = JB_mysql_query($sql)or die(mysql_error());
	  $row = mysql_fetch_row($result);
      $ap = $row[0];

      $sql = "SELECT count(*)  FROM `resumes_table` WHERE status='ACT' AND `approved`='Y' ";
      $result = JB_mysql_query($sql) or die(mysql_error());
	  $row = mysql_fetch_row($result);
      $resume = $row[0];

  
      
	  if (JB_display_motd('E')) { echo '<p>';}
      

      $msg = str_replace ( "%postcount%", $posts, $label["employer_home_stats"]);
      $msg = str_replace ( "%approvedpostcount%", $ap, $msg);
      $msg = str_replace ( "%usercount%", $users, $msg);
      $msg = str_replace ( "%resumecount%", $resume, $msg);
	  $msg = str_replace ( "%SITE_NAME%", JB_SITE_NAME, $msg);
      
     // echo $msg;
      
      ?>

	<?php 
	JB_render_box_top(100,  $label['employer_home_status_summary']);
	echo $msg;
	
	?>
	
	<?php
		JB_render_box_bottom();
	?>
     <br>
	<?php JB_render_box_top(100,  $label['employer_home_main_menu']); ?>
					
      <p class="home_menu_items"><?php echo $label["employer_home_main_account"]; ?></p>
      <p class="home_menu_items"><?php echo $label["employer_home_main_profile"]; ?> </p>
      <p class="home_menu_items"><?php echo $label["employer_home_main_resumes"]; ?></p>
      <p class="home_menu_items"><?php echo $label["employer_home_main_posts"]; ?></p>
      <p class="home_menu_items"><?php echo $label["employer_home_main_help"];?></p>
	<?php
	JB_render_box_bottom();
	?>



<?php JB_template_employers_footer();  ?>