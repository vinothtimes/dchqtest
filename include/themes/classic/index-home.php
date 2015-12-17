
		<table width="100%" border="0">
		<tr>
		<td valign="top"><!-- The Left Column, displaying the content including the search form, job list, etc -->
			<table border='0'><!-- This table shows the job seeker's login form and 'find jobs' box-->
			<tr>
				<td valign="top" width='50%'><!-- left cell, 'Job Seekers' -->
				
				<p align="center" style="margin-top: 0; margin-bottom: 0">
				<img border="0" alt="Job Seekers" src="<?php echo JB_THEME_URL; ?>images/seekers.gif" width="189" height="36"></p>
				<p align="center" style="margin-top: 0; margin-bottom: 0"><font face="Arial" size="2"><?php echo $label["candidate_intro"]; ?></font></p>
				<p align="center" style="margin-top: 0; margin-bottom: 0;" ><b><font face="Arial" color="#000000">
				<?php echo $label["post_resume_link"];?></font></b></p>
				<?php if (JBPLUG_do_callback('index_home_login_replace', $A = false)== false) { ?>
					<form name="form1" action="<?php echo JB_CANDIDATE_FOLDER; ?>login.php" method="post">
					<table border="0" align="center">
					<tr>
					  <td>
					  
						<table cellSpacing="0" cellPadding="0" align="center" border="0">
						  <tr>
							<td nowrap align="right">&nbsp;<?php echo $label['candidate_login_seeker_id']; ?>&nbsp;</td>
							<td><input id="username" size="8" name="username"></td>
						  </tr>
						  <tr>
							<td align="right">&nbsp;<?php echo $label['candidate_login_password']; ?>&nbsp;</td>
							<td>
							<input id="password" type="password" size="8"  name="password"></td>
						  </tr>
						  <tr>
							<td>&nbsp;</td>
							<td>
							  <input class="form_submit_button" type="submit" value="<?php echo $label["candidate_login_button"];?>" name="Submit">
							</td>
						  </tr>
						
						</table>
					  
					  </td>
					</tr>
					  <tr>
							<td width="45%" colspan="2"><p align="center" style="margin: 0">
							<?php echo $label['candidate_join_now_link'];?> | <font size='1'><a href='<?php echo JB_CANDIDATE_FOLDER; ?>forgot.php'><?php echo $label["candidate_forgot_your_pass"];?></a></font></td>
						  </tr>
					</table>
					</form>
				<?php } ?>
				</td><td valign="top"><!-- right cell, 'Find Jobs' -->
				<?php
					JB_render_box_top(100, $label['index_search_box_heading'], '#EDF8FC');
					// the following call will display a search form:  JB_display_dynamic_search_form(form_id, columns)
					JB_display_dynamic_search_form (1,1);
					JB_render_box_bottom();
				?></td>
			</tr>
			</table>
			<?php JBPLUG_do_callback('index_home_middle', $A = false); ?>
			<table border="0" width="100%" ><!-- This table shows the employer's services bar -->
			<tr>
				<td bgcolor="#FFFFCC">
				<center><b><font face="arial" size="2"><?php echo $label["index_employers_services"]; ?></font></b>
				<img src="<?php echo JB_THEME_URL; ?>images/postit-small.gif" alt='' align="middle" border="0"> 
				<a href="<?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER; ?>post.php?page=post.php"><?php echo $label["post_job_link"]; ?></a>&nbsp;/ 
				<a href="<?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER; ?>"><?php echo $label["manage_posts_link"]; ?></a>&nbsp;/ 
				<a href="<?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER; ?>"><?php echo $label["view_resumes_link"]; ?></a> </center>
				</td>
			</tr>
			</table><!-- End employer's services table-->
			<center><!-- Links to the rss feed / del.icio.us link -->
			<a href="<?php echo ( JB_BASE_HTTP_PATH.'rss.xml'); ?>"><IMG border='0' style='margin: 5px;' SRC="<?php echo JB_THEME_URL; ?>images/rss20.gif" WIDTH="80" HEIGHT="15"  ALT="RSS / XML Feed"></a>
			<a href="http://add.my.yahoo.com/content?url=<?php echo htmlentities(( JB_BASE_HTTP_PATH.'rss.xml')); ?>"><IMG border='0' style='margin: 5px;' SRC="<?php echo JB_THEME_URL; ?>images/add_yahoo.gif" WIDTH="91" HEIGHT="17"  ALT="Add to My Yahoo!"></a>
			<a href="http://fusion.google.com/add?feedurl=<?php echo htmlentities(( JB_BASE_HTTP_PATH.'rss.xml')); ?>"><IMG border='0' style='margin: 5px;' SRC="<?php echo JB_THEME_URL; ?>images/add_google.gif" WIDTH="104" HEIGHT="17"  ALT="Add to Google!"></a>
			<a href="http://my.msn.com/addtomymsn.armx?<?php echo htmlentities(("id=rss&ut=". JB_BASE_HTTP_PATH.'rss.xml')); ?>"><IMG border='0' style='margin: 5px;' SRC="<?php echo JB_THEME_URL; ?>images/add_msn.gif" WIDTH="71" HEIGHT="14"  ALT="Add to My MSN!"></a>
			<a href="http://del.icio.us/post?<?php echo htmlentities("v=2&url=".JB_BASE_HTTP_PATH); ?><?php echo htmlentities( "&title=".urlencode(JB_SITE_NAME)); ?>"><IMG border='0' style='margin: 5px;' SRC="<?php echo JB_THEME_URL; ?>images/delicious.gif" WIDTH="16" HEIGHT="16"  ALT="Bookmark this page to del.icio.us"></a>
			<?php JBPLUG_do_callback('index_home_rss_links', $A = false); ?>
			</center>
			<?php 
				// list premium jobs. Comment if you do not want to have it displayed
				JBPLUG_do_callback('index_home_list_premium_jobs', $A = false);
				JB_list_jobs ("PREMIUM");
				JBPLUG_do_callback('index_home_list_jobs', $A = false);
				// List all jobs
				JB_list_jobs ("ALL");
				JBPLUG_do_callback('index_home_end_list_jobs', $A = false);
			?>
			</td>
		<td   valign="top"><!-- The Right Column, displaying the category list, language buttons-->
		<?php
			JB_template_index_sidebar();
		?>
		</td></tr></table>