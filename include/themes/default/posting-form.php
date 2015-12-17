<form method="POST"  action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" name="form1" onsubmit="<?php if (JB_MAP_DISABLED=='NO') { echo 'save_pin();'; } ?> form1.savebutton.disabled=true;" enctype="multipart/form-data">
	<input type="hidden" name="type" value="<?php echo jb_escape_html($_REQUEST['type']); ?>">
	<input type="hidden" name="mode" value="<?php echo jb_escape_html($mode); ?>">
	<input type="hidden" name="pin_x" value="<?php echo jb_escape_html($DynamicForm->get_value('pin_x')); ?>">
	<input type="hidden" name="pin_y" value="<?php echo jb_escape_html($DynamicForm->get_value('pin_y')); ?>">
	<input type="hidden" name="post_id" value="<?php echo jb_escape_html($DynamicForm->get_value('post_id')); ?>">
	<input type="hidden" name="user_id" value="<?php echo jb_escape_html($DynamicForm->get_value('user_id')); ?>">
	
	<table border="0" cellpadding="0" cellspacing="0" class="job_post_data" >
	
	<?php  if (($error != '' ) && ($mode!='EDIT')) { ?>
		<tr>
			<td colspan="2"><?php  echo "<span class='error_msg_label'>".$label['post_save_error']."</span><br> <b>".$error."</b>";  ?></td>
		</tr>
	
	<?php 
	
	}

	if (JB_APP_CHOICE_SWITCH=='YES') {

		if ($mode != 'view') {

		?>
			<tr>
				<td colspan="2">
				<?php

				if ($DynamicForm->get_value('app_type')=='') {
					$DynamicForm->set_value('app_type', 'O'); // Online via the site
				}

				$label['post_form_all_online'] = str_replace ('%SITE_NAME%', jb_escape_html(JB_SITE_NAME), $label['post_form_all_online']);

				?><p></p>
					<table id="dynamic_form" class="dynamic_form" cellSpacing="1" cellPadding="3"  >
					<tr>
					<td class="dynamic_form_field"><?php echo $label['post_form_app_pref'];?></td>
					<td class="dynamic_form_value"><input type="radio" name="app_type" value="O" <?php if($DynamicForm->get_value('app_type')=='O') echo ' checked '; ?> > <?php echo $label['post_form_all_online']; ?><br>
					<input type="radio" name="app_type" value="R" <?php if($DynamicForm->get_value('app_type')=='R') echo ' checked '; ?>> <?php echo $label['post_form_app_url']; ?> <input size="50" type="text" value="<?php echo $DynamicForm->get_value('app_url'); ?>" name="app_url"><br>
					<input type="radio" name="app_type" value="N" <?php if($DynamicForm->get_value('app_type')=='N') echo ' checked '; ?>> <?php echo $label['post_form_app_none']; ?>
					</td>
					</tr>
					</table>
					<p>&nbsp;</p>
				</td>
			</tr>
		<?php

		}

	}

	?>
	
		<tr>
			<td colspan="2" >
			<?php
				if ($mode == "EDIT") {
					echo "[Custom Fields]";
				}
				
				$DynamicForm->display_form_section($mode, 1, $admin);
			?>
			</td>
		</tr>
		<tr >
			
			<td valign="top"  <?php if (JB_MAP_DISABLED == 'YES' ) { echo ' colspan="2" '; } ?> > 
			<?php 
				if ($mode == "EDIT") {
					echo "[Section 2]";
				} 

				$DynamicForm->display_form_section($mode, 2, $admin);
				?></td>
				<?php
				
				if (JB_MAP_DISABLED =='GMAP') {
					echo '<td valign="top">';
					$DynamicForm->display_form_section($mode, 4, $admin);
					echo '</td>';
				}
				elseif (JB_MAP_DISABLED != 'YES' ) {
			
				
			?>
			<td valign="top"><img  border="0" id="map" name="map" alt="map" src="<?php echo jb_get_map_img_url() ?>"  <?php $size=getimagesize(jb_get_map_img_path()) ?> width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>"></td>
				<?php 
				} 
			
		?>
		
		</tr>
		<tr>
			
			<td colspan="2" valign="top"  ><?php 
				if ($mode == "EDIT") {
					echo "[Section 3]";
				}
				
				$DynamicForm->display_form_section($mode, 3, $admin);
				?></td>
		</tr>

		<tr><td colspan="2" align="left">
		<input type="hidden" name="save" id="save101" value="">
		<?php if ($mode=='edit') { ?>
		<input class="form_submit_button" TYPE="SUBMIT"  name="savebutton" value="<?php echo $label['post_save_button']; ?>"  onClick="save101.value='1'">
		<?php } ?>
		</td></tr>
		
	</table>
	</form>