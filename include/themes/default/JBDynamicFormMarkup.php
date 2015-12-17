<?php

/*




The way the fields are rendered when viewing
the dynamic form.



*/
class JBDynamicFormMarkup  extends JBMarkup {

	var $field_row; // a field_row is the row return form the database when running a select * from the form_fields table. Contains the field_id, label and other information for the fields

	var $data_row; // The data stored in the form, stored in an instance of JBDynamicForm 

	var $mode; // mode, eg 'view' or 'edit', or 'EDIT' when editing the fields

	var $form_id;

	var $context;

	function JBDynamicFormMarkup($m, $form_id=1) {
		$this->mode=$m;
		if (is_numeric($form_id)) {
			$this->form_id = $form_id;
		}
	
	}

	function set_form_id($form_id) {
		if (is_numeric($form_id)) {
			$this->form_id = $form_id;
		}
	}

	function set_context($str) {
		$this->context = $str;
	}
	
	
	// Sets the $field_row array
	// The $field_row array contains a row form the form_fields table, which has all the meta-information about the field.

	function set_field_row(&$f) { 
		$this->field_row = &$f;

	}
	
	// Sets the data values for the fields.
	// $this->data_row is a refrence to the $data array in the DynamicFormObject
	function set_values(&$d) {
		
		if (is_numeric($this->form_id)) {
			// php5: JB_get_DynamicFormObject($this->form_id)->set_values($d);
			$obj = $this->get_DynamicFormObject($this->form_id);
			return $obj->set_values($d);
		} else {
			$this->data_row = &$d;
			return true;
		}
		
	}

	function get_data_value($field_id) {
		
		if (is_numeric($this->form_id)) {
			// php5: return JB_get_DynamicFormObject($this->form_id)->get_value($field_id);
			$obj = $this->get_DynamicFormObject($this->form_id);
			return $obj->get_value($field_id);
		} else { 
			return $this->data_row[$field_id];
		}
	}

	function set_data_value($field_id, $value) {
		if (is_numeric($this->form_id)) {
			// php5: JB_get_DynamicFormObject($this->form_id)->set_value($field_id, $value);
			$obj = $this->get_DynamicFormObject($this->form_id);
			return $obj->set_value($field_id, $value);
		} else {
			$this->data_row[$field_id] = $value;
			return true;
		}
	}

	function &get_values() {
		if (is_numeric($this->form_id)) {
			// php5: return JB_get_DynamicFormObject($this->form_id)->get_values();
			$obj = $this->get_DynamicFormObject($this->form_id);
			return $obj->get_values();
		} else {
			return $this->data_row;
		}
	}

	function &get_DynamicFormObject() {

		$c = null;
		if ($this->context) {
			$c = $this->context;
		}

		// php5: return JB_get_DynamicFormObject($this->form_id, $c)->get_values();
		
		return JB_get_DynamicFormObject($this->form_id, $c);
		

	}


	function set_mode($m) {
		$this->mode = $m;
	}
    
    function get_mode() {
        
        return $this->mode;
    }

	function open_container() {


		?>
	
		<table id="dynamic_form" class="dynamic_form" cellSpacing="1" cellPadding="3"  >

		<?php

	}

	function close_container() {

		?>
		</table>
		<?php

	}

	# Tell the user this field is anonymous (Only on the Resume form)

	function get_anonymous_note($user_id) {

		global $label;

		return '<i>'.$this->field_row['FLABEL'].' '.$label['resume_value_hidden'].' (#'.$user_id.')</i>';
	}

	function get_image_anonymous_note($user_id) {
		global $label;

		return '<i>'.$label['employer_resume_list_image_hidden'].' (#'.$user_id.')</i>';
	}

	# Tell the user this field is blocked (Only on the Resume form)

	function get_blocked_note($user_id) {

		global $label;

		return '<i>'.$label['resume_details_blocked'].' (#'.$user_id.')</i>';
	}

	function get_membership_note() {
		
		global $label;

		return '<i>'.$label['member_only_please_log_in'].' </i>';

	}

	function get_error_line($field_label, $error_msg) {
		global $label;
		return $field_label.' - '.$error_msg.'<br>';
	}

	###############################
    # Common HTML for the structure 
	# between fields
	################################


	function field_start($bg_selected='') {

		// Need more information about the field? 
		// eg. wnat to know the field_id?
		// get it from $this->field_row['field_id']
		// everything from the form_fields table is available
		// in $this->field_row for the current field

		?>
		<tr <?php echo $bg_selected; ?>>
		<?php

	}

	function field_end() {

		?>
		</tr>
		<?php

	}

	function field_left_open($bg_selected) {
		?>
		<td class="dynamic_form_field" <?php echo $bg_selected;?>  valign="top" >
		<?php
	}

	function field_left_close() {
		?></td>
		<?php

	}

	function field_label() {
		echo $this->field_row['FLABEL'];
	}

	
	
	function field_right_open($bg_selected) {
		?>
		<td class="dynamic_form_value" <?php echo $bg_selected;?> >
		<?php
	}

	function field_right_close() {
		?></td>
		<?php

	}

	#################################
	
	// print the data value
	// assuming that $val is safe to print to the browser - with HTML escaped accordingly
	// Hint:
	// Want to get the field_id, template_tag, label, etc. of the field that
	// is being printed?
	// Try print_r($this->field_row)
	function _print($val) {
		print $val;
	}

	##################################
	# HTML for get_template_value()

	function get_url_templated($val) {
		if (!$val || ($val=='http://') || ($val=='https://')) {
			return;
		}
	
		// assuming $val is escaped as HTML entities
		$trunc_str_len = 0;
		if ($host = parse_url($val, PHP_URL_HOST)) {
			$host = jb_truncate_html_str($val, 75, $trunc_str_len, true);
		}
		return "<a href='".$val."'>".$host."</a>";
	}


	##################################



	/////////////////////////

	function get_required_mark() {
		return '<span class="is_required_mark" >*</span>'; 
	}

	function get_blank_field_label() {
		return '&nbsp;&nbsp';
	}

	function at_sign_replace() { 
		// the @ sign in emails is replaced with this to trick
		// email harvesting bots 
		return '<img src="'.JB_THEME_URL.'images/at.gif" width="13" height="9" border="0" ALT="(at)">';
	}




	/////////////////////////////////////////
	//
	// Form widgets
	//
	/////////////////////////////////////////

	

	#############################
	# Skill Matrix
	#############################


	

	function skill_matrix_field_open($bg_selected) {
		?>
		<td <?php echo $bg_selected;?> class="dynamic_form_field">
		<?php
	}

	function skill_matrix_field_label() {
		?>
		<span class="dynamic_form_image_label"><?php echo $this->field_row['FLABEL']; ?></span>
		<?php

	}

	function skill_matrix_field_close() {
		?>
		</td>
		<?php

	}

	function skill_matrix_value_open($bg_selected) {
		?>
		<td <?php echo $bg_selected;?> class="dynamic_form_value">
		<?php

	}

	function skill_matrix_form() {

		if ($this->field_row['FCOMMENT']!='') { // print comment for the skill matrix
			echo " <br>".$this->field_row['FCOMMENT'];
		}
		// If you ever need to customize the skill matrix fields,
		// then you can rip out the code form JB_display_matrix() and place
		// it here.
		
		JB_display_matrix ($this->field_row['field_id'], $this->get_values(), $this->mode);


	}

	function skill_matrix_value_close() {
		?>
		</td>
		<?php

	}

	

	
	#############################
	# Youtube field
	#############################


	function youtube_field_open($bg_selected) {
		?>
		<td class="dynamic_form_2_col_field" nowrap valign="top"  colspan="2" <?php echo $bg_selected;?>  >
		<?php
	}

	function youtube_field_close() {
		?></td>
		<?php
	}

	function youtube_display() {

		
		if ($this->field_row['field_width']==0) {
			$this->field_row['field_width'] = 320;
		}
		if ($this->field_row['field_height']==0) {
			$this->field_row['field_height'] = 300;
		}
		?>
		<object width="<?php echo $this->field_row['field_width']?>" height="<?php echo $this->field_row['field_height']; ?>"><param name="movie" value="http://www.youtube.com/v/<?php echo $this->get_data_value($this->field_row['field_id']); ?>"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/<?php echo $this->get_data_value($this->field_row['field_id']);?>" type="application/x-shockwave-flash" wmode="transparent" width="<?php echo $this->field_row['field_width']?>" height="<?php echo $this->field_row['field_height']; ?>"></embed></object>
		<?php

	}

	function youtube_label() {
		?><span class="dynamic_form_image_label"><?php echo $this->field_row['FLABEL'];?></span><br><?php
	}

	function youtube_field() {
		global $label;

		if ($this->field_row['field_width']==0) {
			$this->field_row['field_width']=50;
		}
		?>
		<i><?php echo $label['enter_youtube_url']; ?> </i><br>
		<input class="dynamic_form_text_style" type="text"  name="<?php echo $this->field_row['field_id']; ?>" value="<?php echo JB_escape_html($this->get_data_value($this->field_row['field_id']));?>" size="<?php echo $this->field_row['field_width'];?>" >
		<?php
		if ($this->field_row['FCOMMENT']!='') {
			echo " ".$this->field_row['FCOMMENT'];
		}

	}

	function youtube_delete_button() {
		
		global $label;

		?>
		<input type="hidden" name="<?php echo $this->field_row['field_id']; ?>" value="<?php echo $this->get_data_value($this->field_row['field_id']); ?>">
		<br>
		<input type="hidden" name="del_video<?php echo $this->field_row['field_id'];?>" value="">
		<input type="button" value="<?php echo $label['delete_video_button'];?>" onclick="document.form1.del_video<?php echo $this->field_row['field_id'];?>.value=<?php echo $this->field_row['field_id'];?>; document.form1.submit();"><br>
		<?php
	}

	
	##########################
	# Image Field
	##########################

	

	
	function image_field_open($bg_selected) {
		?>
		<td class="dynamic_form_2_col_field" nowrap valign="top"  colspan="2" <?php echo $bg_selected;?>  >
		<?php

	}


	function image_label() {
		
		?><span class="dynamic_form_image_label"><?php echo $this->field_row['FLABEL']?></span><br>
		<?php

	}

	function image_thumb_display() {
		?><img border="0" alt="<?php echo $this->get_data_value($this->field_row['field_id']); ?>" src="<?php echo JB_get_image_thumb_src($this->get_data_value($this->field_row['field_id'])); ?>" >
		<?php

	}

	function image_linked_display() {
		?>
		<a target="_blank" href="<?php echo JB_get_image_src($this->get_data_value($this->field_row['field_id'])); ?>">
		<img border="0"  alt="<?php echo $this->get_data_value($this->field_row['field_id']) ?>" src="<?php echo JB_get_image_thumb_src($this->get_data_value($this->field_row['field_id'])); ?>" >
		</a>
		<?php

	}

	function image_display_null() {
		?><img SRC="<?php echo JB_THEME_URL;?>images/no-image.gif" width="150" height="150" border="0" ALT=""><?php

	}

	function image_field() {

		global $label;

		?><br><?php echo $label['upload_image'];?><br> 
		
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo JB_MAX_UPLOAD_BYTES;?>"><input class="dynamic_form_text_style" type="file" name="<?php echo $this->field_row['field_id'];?>"  size="<?php echo $this->field_row['field_width']; ?>" >
		<?php
		if ($this->field_row['FCOMMENT']!='') {
			echo " <br>".$this->field_row['FCOMMENT'];
		}


	}

	function image_field_close() {
		?>
		</td>
		<?php

	}

	function image_delete_button() {

		global $label;

		?><br><input type="hidden" name="<?php echo $this->field_row['field_id'];?>" value="<?php echo $this->get_data_value($this->field_row['field_id']);?>" ><input type="hidden" name="del_image<?php echo $this->field_row['field_id'];?>" value=""><input type="button" value="<?php echo $label['delete_image_button'];?>" onclick="document.form1.del_image<?php echo $this->field_row['field_id']?>.value=<?php echo $this->field_row['field_id'];?>; document.form1.submit();"><br>
		<?php


	}

	##########################
	# File Field
	##########################

	

	function file_field_open($bg_selected) {
		?>
		<td valign="top" class="dynamic_form_2_col_field" colspan="2" <?php echo $bg_selected; ?> >
		<?php

	}

	function file_field_close() {
		?>
		</td>
		<?php

	}

	function file_display_link() {

		global $label;

		$file_url = JB_get_upload_file_url($this->get_data_value($this->field_row['field_id']));
		$file_name = $this->get_data_value($this->field_row['field_id']);

		?>

		<a href="<?php echo $file_url; ?>" >
		<img alt="file" src="<?php echo JB_THEME_URL; ?>images/file.gif" width="20" height="20" border="0" > 
		<?php 
		// trim the filename if too long, default 16 chars max	
		if (strlen($file_name) > 16) { 
			$extension = end(explode(".",$file_name));
			echo substr($file_name, 0, 20).'[..].'.$extension; 
		} else { 
			echo $file_name; 
		} ?> </a> - <?php $bytes = filesize(JB_get_upload_file_path($file_name)); echo round($bytes / 1024, 2); ?> <?php echo $label['kilobytes']."<br>";

	}

	function file_not_uploaded() {
		global $label;
		echo '<i>'.$label['no_file_uploaded'].'</i>';

	}

	function file_delete_button() {

		global $label;

		
		?>
		<br>
		<input type="hidden" name="<?php echo $this->field_row['field_id'];?>" value="<?php echo $this->get_data_value($this->field_row['field_id']);?>" >
		<input type="hidden" name="del_file<?php echo $this->field_row['field_id'];?>" value=""><input type="button" value="<?php echo $label['delete_file_button']?>" onclick="document.form1.del_file<?php echo $this->field_row['field_id'];?>.value='<?php echo $this->field_row['field_id'];?>'; document.form1.submit()"><br>
		<?php

	}

	function file_label() {
		?><span class="dynamic_form_file_label"><?php echo $this->field_row['FLABEL'];?></span><br><?php
	}

	function file_field() {

		global $label;

		echo $label['upload_file']." ".'<input type="hidden" name="MAX_FILE_SIZE" value="'.JB_MAX_UPLOAD_BYTES.'"><input class="dynamic_form_text_style" type="file" name="'.$this->field_row['field_id'].'"   >';

		if ($this->field_row['FCOMMENT']!='') {
			echo " <br>".$this->field_row['FCOMMENT']."";
		}
					
	}

	##########################
	# Note Field
	##########################

	function note_open($bg_selected) {
		?>
		<td colspan="2" valign="top" class="dynamic_form_2_col_field" <?php echo $bg_selected; ?> >
		<?php

	}

	function note_field() {
		?> <span class="dynamic_form_note_label"><?php echo $this->field_row['FLABEL'];?></span>
		<?php
	}

	function note_close() {
		?></td>
		<?php

	}

	##########################
	# Google Map Field
	##########################

	function gmap_field_label() {

		?><span class="dynamic_form_image_label"><?php echo $this->field_row['FLABEL'];?></span><br><?php

	}

	function gmap_open($bg_selected) {
		?>
		<td colspan="2" valign="top" class="dynamic_form_2_col_field" <?php echo $bg_selected; ?> >
		<?php

	}

	function gmap_mark() {
		global $label;
		$src = htmlentities(JB_get_relative_path('include/lib/GoogleMap/gmap_iframe.php')); 
		$lat = htmlentities($this->get_data_value($this->field_row['field_id'].'_lat'));
		$lng = htmlentities($this->get_data_value($this->field_row['field_id'].'_lng'));
		$z = htmlentities($this->get_data_value($this->field_row['field_id'])); // zoom

		if (!$z) {
			$z = JB_GMAP_ZOOM;
		}
		echo $label['gmap_move_marker']; echo $this->field_row['FCOMMENT'];
		?>
		<br>
		<input type="hidden" id="input_gmap_<?php echo $this->field_row['field_id'];?>_lat" name="<?php echo $this->field_row['field_id'];?>_lat" value="<?php echo $lat; ?>"> 
		<input type="hidden" id="input_gmap_<?php echo $this->field_row['field_id'];?>_lng" name="<?php echo $this->field_row['field_id'];?>_lng" value="<?php echo $lng; ?>">
		<input type="hidden" id="input_gmap_<?php echo $this->field_row['field_id'];?>_zoom" name="<?php echo $this->field_row['field_id'].'_zoom';?>" value="<?php echo $z; ?>">

		<iframe width="<?php echo $this->field_row['field_width']; ?>"  height="<?php echo $this->field_row['field_height']; ?>" frameborder="0" src="<?php echo $src.'?map_id='.$this->field_row['field_id']; ?>&amp;form_id=<?php echo $this->form_id; ?>&amp;lat=<?php echo $this->get_data_value($this->field_row['field_id'].'_lat'); ?>&amp;lng=<?php echo $this->get_data_value($this->field_row['field_id'].'_lng'); ?>&amp;z=<?php echo $z; ?>"></iframe>

		<?php

	}

	

	function gmap_show() {


		?>

		<div id="gmap_<?php echo $this->field_row['field_id']; ?>" style="width:<?php echo $this->field_row['field_width']; ?>px;height:<?php echo $this->field_row['field_height']; ?>px;">this is a test of the map</div>

		<?php

	}

	function gmap_close() {
		?></td>
		<?php

	}

	##########################
	# Text Field
	##########################

	function text_field() {
		
		?><input class="dynamic_form_text_style" type="text"  name="<?php echo $this->field_row['field_id']; ?>" value="<?php echo JB_escape_html($this->get_data_value($this->field_row['field_id']));?>" size="<?php echo $this->field_row['field_width'];?>" >
		<?php
		if ($this->field_row['FCOMMENT']!='') {
			echo " ".$this->field_row['FCOMMENT']."";
		}
	}

	
	##########################
	# HTML Editor Field
	##########################

	function get_editor_field_header() {

		return '
<script type="text/javascript" src="'.jb_get_CK_js_base(true).'ckeditor.js"></script>
';

	}

	function editor_field() {

		global $FCK_LANG_FILES, $JBMarkup; 

		$lang = str_replace('.js', '', $FCK_LANG_FILES[$_SESSION['LANG']]);

		if ($this->field_row['FCOMMENT']!='') {
			echo $this->field_row['FCOMMENT']."<br>";
		}
		// See include/lib/ckeditor/config.js for configuration options
		require_once(JB_basedirpath()."include/lib/ckeditor/ckeditor.php") ;
		$CKEditor = new CKEditor();
		$CKEditor->initialized = true;
		$CKEditor->basePath	= jb_get_CK_js_base(true);
		$config = array (
			'toolbar'=>'Basic',
			'docType'=>$JBMarkup->doc_type,
			'width'=>'99%',
			'height'=>$this->field_row['field_height']*15,
			'language'=>$lang
		);

		$events['instanceReady'] = 'function (ev) { ev.editor.dataProcessor.writer.selfClosingEnd = \'>\'; }'; // turn off XHTML generation

		$val = $this->get_data_value($this->field_row['field_id']);
		$CKEditor->editor($this->field_row['field_id'], $val, $config, $events);

	}

	##########################
	# Textarea Field
	##########################

	function textarea_field() {

		if ($this->field_row['FCOMMENT']!='') {
			echo $this->field_row['FCOMMENT']."<br>";
		}
		
		?>
		<textarea  name="<?php echo $this->field_row['field_id']; ?>" cols="<?php echo $this->field_row['field_width']; ?>" rows="<?php echo $this->field_row['field_height']; ?>"><?php echo JB_escape_html($this->get_data_value($this->field_row['field_id']));?></textarea>
		<?php
		
	}

	##########################
	# seperator field
	##########################

	

	function seperator_open($bg_selected) {

		?><td colspan="2" class="dynamic_form_seperator" ><?php
	}

	function seperator_display() {
		echo $this->field_row['FLABEL']; 
	}

	function seperator_close() { // called after seperator()
		?></td>
		<?php
	}

	##########################
	# Category field
	##########################

	function category($str) {
		echo $str;
	}

	function category_breadcrumbs() { // breadbrumb navigation for categories
		
		echo JB_getPath_templated($this->get_data_value($this->field_row['field_id']));
	}

	function get_category_breadcrumb_link($link, $anchor) {
		return '<A href="'.$link.'">'.jb_escape_html($anchor).'</a>';
	}

	function get_category_breadcrumb_seperator() {
		// The -> seperator between categories
		// eg. Job Classification -> I.T. & T.
		return ' -&gt; ';
	}

	function category_link($link, $anchor) {

		echo '<A href="'.$link.'">'.$anchor.'</a>';

	}

	function category_field() {

		JB_category_select_field ($this->field_row['field_id'], $this->field_row['category_init_id'], $this->get_data_value($this->field_row['field_id'])); 
		if ($this->field_row['FCOMMENT']!='') { 
			echo $this->field_row['FCOMMENT']."<br>";	
		}

	}

	function category_select_open($field_name) {

		?>

		<select name="<?php echo $field_name; ?>">

		<?php

	}

	function category_first_option() {

		global $label;
		?><option value=""><?php echo $label['sel_category_select']; ?></option>
		<?php

	}


	// Render the category option for the category option list.
	// $depth is the level of the category. It is used to render 
	
	function category_select_option($val, $option, $selected, $allow='Y', $depth=null) {

		$disabled = '';
		if ($allow=='N') {
			$disabled = 'disabled'; // for xhtml this would be: disabled="disabled"
		}
		?><option <?php echo $disabled; ?> value="<?php echo jb_escape_html(trim($val));?>" <?php echo $selected;?> ><?php echo jb_escape_html($option);?></option> 
		<?php

	}

	function category_select_close() {
		?>
		</select>
		<?php
	}

	function get_category_option_space() {
		return '&nbsp;&nbsp;';
	}

	function get_category_option_branch() {
		return '|--&nbsp;';
	}

	function get_category_option_arrow() {
		return ' -&gt; ';
	}

	##########################
	# Date field
	##########################

	function date_field_open() {
		?>
		<table class="date_field"><tr><td nowrap>
		<?php

	}

	function date_field_close() {
		?>
		</td></tr></table>
		<?php

	}

	function date_field($day, $month, $year) {

		$class = "dynamic_form_date_style";

		echo JB_form_date_field ($this->field_row['field_id'], $day, $month, $year, $class);

		if ($this->field_row['FCOMMENT']!='') {
			echo " ".$this->field_row['FCOMMENT'];
		}


	}

	function date_year($year, $field_name, $class) {
		?><input type="text" class="<?php echo $class; ?>" name="<?php echo $field_name."y" ; ?>" size="4"  value="<?php echo $year;  ?>" >
		<?php

	}

	function date_month($month, $field_name, $class) {

		global $label;

		
		?><select name="<?php echo $field_name."m"; ?>" class="<?php echo $class; ?>" >
			<option value=""></option>
			<?php 
			for ($i=1; $i<13; $i++) {
			?>
				<option <?php if ($month==sprintf("%02s", $i)) { echo ' selected ';} ?> value="<?php echo sprintf("%02s", $i); ?>"><?php echo $label['sel_month_'.$i]; ?></option>
				<?php
			}
		?>
		</select><?php

	}

	function date_day($day, $field_name, $class) {

		?>

		<select  name="<?php echo $field_name."d"; ?>" class="<?php echo $class; ?>" >
			<option value=""></option>
			<?php 
			for ($i=1; $i<32; $i++) {
			?>
				<option <?php if ($day==sprintf("%02s", $i)) { echo ' selected ';} ?> value="<?php echo sprintf("%02s", $i); ?>" ><?php echo sprintf("%02s", $i); ?></option>
				<?php
			}
		?>
		</select>
		<?php

	}

	# SCW Date input field
	function date_field_scw() {

		
		?>
		<input autocomplete="off" name="<?php echo $this->field_row['field_id']; ?>" onclick= "scwShow(this,this);" size="10" onfocus= "scwShow(this,this);" type="text" value="<?php echo (JB_ISODate_to_SCWDate($this->get_data_value($this->field_row['field_id']))); ?>">
		<?php
		if ($this->field_row['FCOMMENT']!='') {
			echo " ".$this->field_row['FCOMMENT'];
		}
	}

	

	##########################
	# Single Select field
	##########################

	function select_field() {

		// generate the select field
		// to customize, see single_select_close(), single_select_first_option()
		// single_select_option() and single_select_option(..) functions
		JB_form_select_field ($this->field_row['field_id'], $this->get_data_value($this->field_row['field_id'])); 
		// print the comment						
		if ($this->field_row['FCOMMENT']!='') {
			echo " ".$this->field_row['FCOMMENT']."";
		}

	}

	function single_select_open($field_id) {
		?><select  name="<?php echo $field_id;?>">
		<?php
	}

	function single_select_close() {
		?></select><?php

	}

	function single_select_first_option() {
		global $label;
		?><option value=""><?php echo jb_escape_html($label['sel_box_select']);?></option>
		<?php
	}

	function single_select_option(&$code_row, $checked) {
		?><option <?php echo $checked;?> value="<?php echo $code_row['code'];?>"><?php echo jb_escape_html($code_row['description']);?></option>
		<?php
	}

	##########################
	# Radio button field
	##########################

	function radio_field() { // 1 or more radio buttons
		JB_form_radio_field ($this->field_row['field_id'], $this->get_data_value($this->field_row['field_id']));
		if ($this->field_row['FCOMMENT']!='') {
			echo " ".$this->field_row['FCOMMENT']."";
		}

	}

	function radio_button(&$code_row, $checked) { // a single radio button belonging to the radio field

		?><input class="dynamic_form_radio_style" <?php echo $checked;?> id="id<?php echo $this->field_row['field_id'].$code_row['code']; ?>" type="radio" name="<?php echo $this->field_row['field_id'];?>" value="<?php echo $code_row['code'];?>">
		<label for="id<?php echo $this->field_row['field_id'].$this->field_row['code'];?>" class="dynamic_form_input_label" ><?php echo jb_escape_html($code_row['description']);?></label> <br>
		<?php

	}

	##########################
	# Checkbox field
	##########################

	function checkbox_field() { // 1 or more checkboxes
		JB_form_checkbox_field ($this->field_row['field_id'], $this->get_data_value($this->field_row['field_id']), $this->mode);
		if ($this->mode != 'view') {
			if ($this->field_row['FCOMMENT']!='') {
				echo " ".$this->field_row['FCOMMENT']."";	
			}
		}

	}

	function checkbox(&$code_row, $checked, $disabled) {
		?> <input class="dynamic_form_checkbox_style" id="id<?php echo $this->field_row['field_id'].$code_row['code'];?>" type="checkbox" <?php echo $checked.$disabled;?> name="<?php echo $this->field_row['field_id'];?>[]" value="<?php echo $code_row['code'];?>">
		<label for="id<?php echo $this->field_row['field_id'].$code_row['code'];?>" class="dynamic_form_input_label"><?php echo
		jb_escape_html($code_row['description']);?></label> <br>
		<?php
	}

	##########################
	# Multiple Select field
	##########################

	function multiple_select_field() {

		JB_form_mselect_field ($this->field_row['field_id'], $this->get_data_value($this->field_row['field_id']), $this->field_row['field_height'], $this->mode);
		if ($this->mode != 'view') {
			if ($this->field_row['FCOMMENT']!='') {echo " ".$this->field_row['FCOMMENT']."";}
		}
	}


	function multiple_select_open($field_id, $size) {
		echo '<select name="'.$field_id.'[]" multiple size="'.$size.'" >';
	}

	function multiple_select_close() {
		echo '</select>';
	}

	function multiple_select_option(&$code_row, $checked) {
		echo '<option '.$checked.' value="'.JB_escape_html($code_row['code']).'">'.jb_escape_html($code_row['description']).'</option>';
	}

	##########################
	# Blank field
	##########################

	function blank_field() {

		echo  "&nbsp;";

	}


	

}

?>