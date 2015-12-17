<h3 style="text-align: center"><?php echo $label["employer_section_heading"];?></h3> 
	<table style="margin-left: auto;  margin-right: auto;">
		<tr>
			<td style="text-align:center;"><b><?php echo $label["employer_flogin_emp"];?></b>
				<form id="form1" method="post" target="_parent" action="login.php">
				<input type="hidden" name="page" value="<?php if ($_REQUEST['page']=='') $_REQUEST['page']=$_SERVER['PHP_SELF']; echo jb_escape_html($_REQUEST['page']); ?>">
				<table width="100%"  border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td style="float: right;" valign="MIDDLE"><?php echo $label["employer_signup_member_id"]; ?>&nbsp;</td>
						<td valign="MIDDLE"><input name="username" type="text" id="username" size="12"></td>
					</tr>
					<tr>
						<td style="float: right;" valign="MIDDLE"  ><?php echo $label["employer_signup_password"]; ?>&nbsp;</td>
						<td valign="MIDDLE"><input name="password" type="password" id="password" size="12"></td>
					</tr>
					<tr>
						<td></td>
						<td align="left">
							<input type="submit" class="form_submit_button" name="Submit" value="<?php echo $label["employer_login"];?>"> 
						</td>
					</tr>
			  
				</table>
				</form>
			</td>
		</tr>
		<tr><td colspan="2"><a href="forgot.php"><?php echo $label["employer_pass_forgotten"]; ?></a><br>
			  <a target="_parent" href="<?php echo JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER;?>"><?php echo $label["employer_link_to_jobseeker"];?></a>
		</td></tr>
		<tr>
			<td ><center><h3><a href="signup.php"><?php echo $label["employer_join_now"]; ?></a></h3> </center></td>
		</tr>
		
 </table>