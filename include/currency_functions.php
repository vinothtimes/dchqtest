<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
function JB_currency_option_list ($selected) {

	$sql = "SELECT * FROM currencies ORDER by name ";
	$result = JB_mysql_query ($sql) or $error= 102;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if ($row['code']==$selected) {
			$sel = " selected ";
		} else {
			$sel = "";
		}
		echo "<option $sel value=".$row['code'].">".$row['code']." ".$row['sign']."</option>";

	}

}
#############################
 
function JB_get_default_currency() {
	static $jb_default_currency;
	if (!isset($jb_default_currency)) {
		if (!$jb_default_currency=jb_cache_get('jb_default_currency')) {
			$sql = "SELECT code from currencies WHERE is_default='Y' ";
			$result = JB_mysql_query ($sql) or die (mysql_error());
			$jb_default_currency = mysql_fetch_array($result, MYSQL_ASSOC);
			
			jb_cache_set('jb_default_currency', $jb_default_currency);
		}
		return $jb_default_currency['code'];
	} 
	return $jb_default_currency['code'];
	


}
#############################


function JB_get_currency_rate($code) {
	
	$sql = "SELECT rate from currencies WHERE code='".jb_escape_sql($code)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row['rate'];


}

##############################



function JB_convert_to_currency($amount, $from_currency, $to_currency, $from_rate=false) {

	if ($from_currency===$to_currency) {
		return $amount;
	}

	static $jb_c2c_cache;
	

	if ($from_rate==0) { 
		if ($jb_c2c_cache[$from_currency]['rate']=='') {
			$sql = "SELECT rate, decimal_places FROM currencies WHERE code='".jb_escape_sql($from_currency)."' ";
			$result = JB_mysql_query ($sql) or die (mysql_error());
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			$from_rate = $row['rate'];
			// cache it
			$jb_c2c_cache[$from_currency]['rate'] = $from_rate;
			$jb_c2c_cache[$to_currency]['decimal_places'] = $row['decimal_places'];
		} else {
			// read from cache
			$from_rate = $jb_c2c_cache[$from_currency]['rate'];
			$to_decimal_places = $jb_c2c_cache[$to_currency]['decimal_places'];
		}
	}

	if ($jb_c2c_cache[$to_currency]['rate']=='') {

		$sql = "SELECT rate, decimal_places from currencies WHERE code='".jb_escape_sql($to_currency)."' ";
		$result = JB_mysql_query ($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$to_rate = $row['rate'];
		$to_decimal_places = $row['decimal_places'];

		// cache it
		$jb_c2c_cache[$to_currency]['rate'] = $row['rate'];
		// cache it
		$jb_c2c_cache[$to_currency]['decimal_places'] = $row['decimal_places'];
	} else {
		$to_rate = $jb_c2c_cache[$to_currency]['rate'];
		$to_decimal_places = $jb_c2c_cache[$to_currency]['decimal_places'];
	}


	$new_amount = ($amount * $to_rate) / $from_rate;
	$new_amount = round ($new_amount, $to_decimal_places);
	

	return $new_amount;

}

###############################
// return as a float
function JB_convert_to_default_currency($cur_code, $amount) {
	if (func_num_args()>2) {
		$from_rate = func_get_arg(2);
	}
	if ($from_rate == '') {
		$sql = "SELECT * from currencies WHERE code='".jb_escape_sql($cur_code)."' ";
		$result = JB_mysql_query ($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$from_rate = $row['rate'];
	}
	

	$sql = "SELECT * from currencies WHERE is_default='Y' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$to_rate = $row['rate'];
	$to_decimal_places = $row['decimal_places'];

	$new_amount = ($amount * $to_rate) / $from_rate;
	$new_amount = round ($new_amount, $to_decimal_places);

	return $new_amount;


}

##############################################

function JB_convert_to_default_currency_formatted($cur_code, $amount) {

	if (func_num_args()>2) {
		$show_code = func_get_arg(2);
	}

	if (func_num_args()>3) {
		$from_rate = func_get_arg(3);
	}
	if ($from_rate == '') {

		$sql = "SELECT * from currencies WHERE code='".jb_escape_sql($cur_code)."' ";
		$result = JB_mysql_query ($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$from_rate = $row['rate'];
	}

	$sql = "SELECT * from currencies WHERE is_default='Y' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$to_rate = $row['rate'];
	$to_code = $row['code'];
	$to_decimal_places = $row['decimal_places'];

	if ($from_rate==0)  $from_rate=1; // on older vesions the currency may be blank...

	$new_amount = ($amount * $to_rate) / $from_rate;
	$new_amount = round ($new_amount, $to_decimal_places);

	return JB_format_currency($new_amount, $to_code, $show_code) ;


}

##############################################

function JB_format_currency($amount, $cur_code) {
	global $cached_code;
	global $cached_res;
	if (func_num_args()>2) {
		$show_code = func_get_arg(2);
		

	}
	$sql = "SELECT * FROM currencies WHERE code='".jb_escape_sql($cur_code)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if ($show_code) {
		$show_code = " ".$row['code'];

	}
	$amount = number_format ( $amount , $row['decimal_places'], $row['decimal_point'], $row['thousands_sep'] );
	$amount = $row['sign']."".$amount.$show_code;

	return $amount;


}
######################################
?>