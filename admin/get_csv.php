<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
set_time_limit ( 600 ); // 10 min

require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");
//header ("Content-Type: text/download");
//header('Content-Disposition:attachment; filename='.$_REQUEST['table'].'.csv');

$_REQUEST['form_id'] = jb_get_form_id_by_table_name($_REQUEST['table']);

if ($_REQUEST['form_id']!='') {
	$sql = "SELECT * FROM form_fields WHERE `form_id`='".jb_escape_sql($_REQUEST['form_id'])."' ";
	$f_result = JB_mysql_query ($sql) or die ($sql.mysql_error());
	while ($f_row = mysql_fetch_array($f_result, MYSQL_ASSOC)) {
		$labels[$f_row['field_id']] = $f_row['field_label'];
	}
}


$sql = "SHOW COLUMNS FROM `".jb_escape_sql($_REQUEST['table'])."`";
$cols = JB_mysql_query ($sql)or die ($sql.mysql_error());

$comma = '';
while ($c_row = mysql_fetch_row($cols)) {

	if ($labels[$c_row[0]]!='') {
		$fields[] = $labels[$c_row[0]];
	} else {
		$fields[] = $c_row[0];
	}

	$fields_str = $fields_str.$comma.'`'.$c_row[0].'`';
	$comma = ', ';

}


if (is_numeric($_REQUEST['schema_id'])) {
	$extra_sql = "WHERE `schema_id`='".jb_escape_sql($_REQUEST['schema_id'])."' ";
}


echo JB_to_csv_string($fields);


$sql = "SELECT * FROM `".$_REQUEST['table']."` $extra_sql ";
$result = JB_mysql_query ($sql);

while ($row = mysql_fetch_row($result)) {
	echo JB_to_csv_string($row);


}

?>