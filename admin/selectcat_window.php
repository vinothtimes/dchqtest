<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");
require_once ("../include/posts.inc.php");
require_once ("../include/category.inc.php");

JB_admin_header('Admin -> Select Category');

?>
Please select the category and click OK<br>
The category will be the starting category for the field.
<form name="cat_selector" >
<select height="10" name="select" onchange="change_it(); ">
<option value="0">[Select Starting Category]</option>
<?php
	
		$form_id = (int) $_REQUEST['form_id'];
		if ($form_id=='') {
			$form_id = 1;

		}
		JB_category_option_list2(0, $selected, $form_id);
?>
	</select>
	
	<input type="button" value="OK" onclick="window.close()" >

	</form>

	<script type="text/javascript">
	function change_it() {
	var selectBox = document.forms[0].select;
		user_input = selectBox.options[selectBox.selectedIndex].value
		user_text = selectBox.options[selectBox.selectedIndex].text
		window.opener.document.form2.category_init_id.value = user_input;
		window.opener.document.form2.category_init_name.value = user_text;

	}
	</script>

<?php

JB_admin_footer();

?>