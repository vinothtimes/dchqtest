<center><h3><?php echo $label["app_please_log_in"];?></h3></center> <?php
   ?><table style="margin: 0 auto; border:0px;">
   <tr>
				<td>
				<center><b><?php echo $label["c_flogin_jobseek"]?></b></center>
					<form name="form1" method="post" action="<?php if ($action=='') { echo JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER; ?>login.php<?php } else { echo $action; } ?>" >
					<input type="hidden" name="page" value="<?php if ($_REQUEST['page']=='') $_REQUEST['page']=$_SERVER['PHP_SELF']; echo jb_escape_html($_REQUEST['page']); ?>">
					<table style="margin: 0 auto; border:0px;  " cellspacing="0" cellpadding="0">
						
						<tr>
							<td style="float: right;"><?php echo $label["candidate_login_seeker_id"];?>&nbsp;</td>
							<td><input name="username" type="text" id="username" size="12"></td>
						</tr>
						<tr>
							<td style="float: right;"><?php echo $label["candidate_login_password"];?>&nbsp;</td>
							<td><input name="password" type="password" id="password" size="12"></td>
						</tr>
						<tr>
							<td></td>
							<td align="left">
								<input class="form_submit_button" type="submit" name="Submit" value="<?php echo $label["c_flogin_login"];?>">
							</td>
						</tr>
                     
					</table>
					</form>
				</td>
			</tr>
			<tr>
				<td>
				<a target="_parent" href="<?php echo JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER?>forgot.php"><?php echo $label["c_flogin_forgotten"];?></a><br>
					 <a target="_parent" href="<?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER;?>"><?php echo $label["c_flogin_advertiser"];?></a>
				</td>
			</tr>
			<tr>
				<td><div style="text-align:center" ><h3><a target="_parent" href="<?php echo JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER?>signup.php"><?php echo $label["c_flogin_join_now"];?></a></h3> </div></td>
			</tr>
			
			
     </table>