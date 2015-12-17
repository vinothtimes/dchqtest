<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

#########################################
// File Uploads
#########################################

/*

The system works by hashing the file-name, so it does not have to check the 
database to find where the file is, it just hashes the file name. File-names 
are always assumed to be unique - the unique file name is generated in 
JB_saveFile() and JB_saveImage().

The system will spread the files evenly across a tree of directories. The 
maximum number of directories is calculated to be 4096 (16 possible characters 
to choose from, 3 choices can be made). If there are 1000 files per directory, 
then that would be 4,096,000 files. Modern file systems can support much more 
than 1000 files per directory. Also, if you need to increase the number 
add one more level then modify only in one place (JB_get_archive_sub_dir() 
function)

#########################################


Save the file after it has been uploaded.
The function will process the file name, move the file
in to a storage directory while renaming it to the new name.

- Returns: The new name of the file (string)

*/
function JB_saveFile($field_id, $user_id=false) {

	if ($user_id===false) {
		$user_id=$_SESSION['JB_ID'];
	}
			
	$a = explode(".", JB_clean_str($_FILES[$field_id]['name']));

	if (sizeof($a)<2) {
		return false;
	}

	$ext = strtolower(array_pop($a));
	$name = strtolower(array_shift($a));

	if (!$name) {
		return false;
	}
	
	$name = $user_id."_".$name;
	
	$name = preg_replace('#[^a-z^0-9]+#i', "_", $name); // strip out unwanted characters
	$ext = preg_replace('#[^a-z^0-9]+#i', "_", $ext); // strip out unwanted characters
	
	$new_name = $name.time().".".$ext;
	//$new_name = $name.".".$ext;

	$uploadfile = jb_provision_archive_path($new_name, 'FILE');
	



	if (strpos(strtoupper(PHP_OS), 'WIN')!==false) { 
		// sometimes the dir can have double slashes on Win, remove 'em
		$_FILES[$field_id]['tmp_name'] = str_replace ('\\\\', '\\', $_FILES[$field_id]['tmp_name']);
	}
	
	if (move_uploaded_file($_FILES[$field_id]['tmp_name'], $uploadfile)) {
		
		@chmod ($uploadfile, JB_NEW_FILE_CHMOD);
		// plugins can hook here to do extra processing on the file
		JBPLUG_do_callback('save_file', $uploadfile, $field_id, $user_id);
	} else {
		
		switch ($_FILES[$field_id]["error"]) {
			case UPLOAD_ERR_OK:
			   break;
			case UPLOAD_ERR_INI_SIZE:
			   jb_custom_error_handler('upload', "The uploaded file exceeds the upload_max_filesize directive (".ini_get("upload_max_filesize").") in php.ini.", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_FORM_SIZE:
			   jb_custom_error_handler('upload', "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.", __FILE__, 0, $vars);
			   break;
			case UPLOAD_ERR_PARTIAL:
			   jb_custom_error_handler('upload', "The uploaded file was only partially uploaded.", __FILE__, 0, $vars);
			   break;
			case UPLOAD_ERR_NO_FILE:
			   jb_custom_error_handler('upload', "No file was uploaded.", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_NO_TMP_DIR:
			   jb_custom_error_handler('upload', "Missing a temporary folder.", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_CANT_WRITE:
			   jb_custom_error_handler('upload', "Failed to write file to disk", __FILE__, __LINE__, $vars);
			   break;
			default:
			   jb_custom_error_handler('upload', "Unknown File Error", __FILE__, __LINE__, $vars);
		}
	}
	
	return $new_name;

}

###########################################################
/*

Check if the uploaded file exists
Returns true if it does, false otherwise

*/

function JB_upload_file_exists($file_name) {
	if ($file_name=='') {
		return false;
	}
	$path = JB_get_file_path($file_name, 'FILE');
	return (is_file($path) && file_exists($path));
	

}

#####################################################################

/*
Get the absolute URL to download the file, the URL
will be pointing to the upload_files/dl.php which allows
a user to download the file.

*/

function JB_get_upload_file_url($file_name) {
	//return JB_BASE_HTTP_PATH.'upload_files/dl.php?f='.$file_name;
	return JB_get_upload_file_URI($file_name, 'FILE');

}

#####################################################################

/*

Get the full path to the archived file,
including the sub-directory for the file.
Returns a path to the file, directory + sub-directory + filename. The sub-directory
is derived from the file name
$media_type - 'FILE', 'IMAGE', 'THUMB'
*/
function JB_get_file_path($file_name, $media_type='FILE') {

	$dir = JB_get_upload_file_dir($media_type);
	$sub_dir = JB_get_archive_sub_dir($file_name);
	$archive_file_name = JB_get_archive_file_name($file_name);

	// check if the archived file exists. Older versions (pre 3.6) did not
	// put the files in sub-directory archives
	if (file_exists($dir . $sub_dir . $archive_file_name)) {
		return $dir . $sub_dir . $archive_file_name;
	}
	else {
		// return only the base directory with the file name appended
		return $dir . $file_name;
	}
	
	
}

##################################

function JB_get_upload_file_dir($media_type='FILE') {
	
	static $dir;
	
	if (isset($dir[$media_type])) return $dir[$media_type];

	switch ($media_type) {
		case 'FILE':
			$dir[$media_type] = JB_FILE_PATH;
			break;
		case 'IMAGE':
			$dir[$media_type] = JB_IMG_PATH;
			break;
		case 'THUMB';
			$dir[$media_type] = JB_IMG_PATH.'thumbs/';
			break;
		default:
			$set_dir = null;
			JBPLUG_do_callback('get_upload_file_dir', $set_dir, $media_type);
			if (!is_null($set_dir)) {
				$dir[$media_type] = $set_dir;
			}
			break;
	}
	
	return $dir[$media_type];
}

##################################


function JB_get_archive_sub_dir($file_name) {

	$md5_name = JB_get_archive_file_name($file_name);

	// 16 different chars, 3 choices, so there
	// would be 4096 possible paths
	preg_match ('#(.)(.)(.)#i', $md5_name, $m); // choose the first three chars
	if (!isset($m[1]) || !isset($m[2]) || !isset($m[3])) {
		return false;
	}
	return $m[1].'/'.$m[2].'/'.$m[3].'/';

}
##################################

function JB_get_upload_file_URI($file_name, $media_type='FILE') {

	static $uri;

	if (isset($uri[$file_name.$media_type])) {
		return $uri[$file_name.$media_type];
	}

	$path = JB_get_file_path($file_name, $media_type);
	$archive_file_name = JB_get_archive_file_name($file_name);

	$sub_dir = '';
	// does the file exist in the archive?
	if (strpos($path, $archive_file_name)!==false) { 
		// if it does exist, it would mean that $archive_file_name is in the $path
		// therefore, get the sub-directory part that we can append later
		$sub_dir = JB_get_archive_sub_dir($file_name);
	}

	switch ($media_type) {
		case 'FILE':
			//$uri = JB_FILE_HTTP_PATH;
			$uri[$file_name.$media_type] = JB_BASE_HTTP_PATH.'upload_files/dl.php?f='.$file_name;
			return $uri[$file_name.$media_type]; // special case
			break;
		case 'IMAGE':
			$uri[$file_name.$media_type] = JB_IMG_HTTP_PATH;
			break;
		case 'THUMB':
			$uri[$file_name.$media_type] = JB_IMG_HTTP_PATH.'thumbs/';
			break;
		default:
			JBPLUG_do_callback('get_archive_sub_dir', $uri, $media_type);
			break;
	}

	if ($sub_dir) {
		$uri[$file_name.$media_type] .= $sub_dir . $archive_file_name; // the new archive urls
	} else {
		$uri[$file_name.$media_type] .=  $file_name; // the old url
	}
	
	

	return $uri[$file_name.$media_type];
}

##################################

function JB_get_archive_file_name($file_name) {

	$a = explode(".", $file_name);
	if (sizeof($a) < 2) { 
		// it needs to be at least made up from two parts:
		// file name and extension
		return false;
	}
	
	// make a new name for the file using md5 hash

	$ext = strtolower(array_pop($a));
	if (!strtolower(array_pop($a))) {
		return false;
	}
	$md5_name = md5($file_name).'.'.$ext;

	return $md5_name;


}

##################################
# create the archive directories

function jb_provision_archive_path($file_name, $media_type='FILE') {

	$dir = JB_get_upload_file_dir($media_type);
	$sub_dir = JB_get_archive_sub_dir($file_name);
	$sep = '';
	$sub_dir_parts = explode('/', $sub_dir);

	while (($part = array_shift($sub_dir_parts))!==null) {

		$dir .= $sep;


		if (!is_dir($dir.$part)) {
		
			mkdir ($dir.$part, (JB_NEW_DIR_CHMOD));
			//@chmod ($dir.$part, JB_NEW_DIR_CHMOD);
			// put a blank index.html
			touch ($dir.$part.'/index.html');

			// setup a htaccess file
			if ($media_type=='FILE') {
				$fh = fopen($dir.$part.'/.htaccess', 'w');
				$str =
					"deny from all";
				fwrite ($fh, $str, strlen($str));
				fclose($fh);
			}

		}
		$dir = $dir.$part;
		$sep = '/';

	}

	return $dir.JB_get_archive_file_name($file_name);

}



#####################################################################
/*

Get the full path to the uploaded file

*/
function JB_get_upload_file_path($file_name) {
	
	return JB_get_file_path($file_name, 'FILE');
	
	
	
}

#####################################################################
/*

Deletes the file stored in the FILE field of any record in the database
$table_name -> name of the table, eg. posts_table
$primary_key_name -> name of the primary key, eg. post_id
$primary_key_id -> ID value of the primary_key, eg. 56
$field_id -> field_id of the form where the file name is stored, eg. 12
*/
function JB_delete_file_from_field_id($table_name, $primary_key_name, $primary_key_id, $field_id) {
	// get the name of the file stored in the field
   $sql = "SELECT `$field_id` FROM `$table_name` WHERE `$primary_key_name`='".JB_escape_sql($primary_key_id)."'";
   $result = JB_mysql_query($sql) or die (mysql_error());
   $row = mysql_fetch_array ($result, MYSQL_ASSOC);
   if ($row[$field_id] != '') {
	  // delete the original
	  JB_delete_file($row[$field_id]);
   }

}
###########################################################
/*

Deletes the file, given the original file_name

*/
function JB_delete_file($file_name) {

	if (JB_upload_file_exists($file_name)) {
		unlink (JB_get_upload_file_path($file_name));
	}

}


#########################################
// Image Uploads
#########################################



function JB_saveImage($field_id, $user_id=false) {

	if ($user_id===false) {
		$user_id=$_SESSION['JB_ID'];
	}
	
	$a = explode(".", JB_clean_str($_FILES[$field_id]['name']));

	if (sizeof($a)<2) { // must have name and extension
		return false;
	}

	$ext = strtolower(array_pop($a));
	$name = strtolower(array_shift($a));

	if (!$name) {
		return false;
	}
	
	
	$name = $user_id."_".$name; // prefix the file with the user id
	
 
	$name = preg_replace('#[^a-z^0-9]+#i', "_", $name); // strip out unwanted characters
	$ext = preg_replace('#[^a-z^0-9]+#i', "_", $ext); // strip out unwanted characters
	
	$new_name = $name.time().".".$ext;
	//$new_name = $name.".".$ext;

	$uploadfile = jb_provision_archive_path($new_name, 'IMAGE');
	$thumbfile = jb_provision_archive_path($new_name, 'THUMB');

	if (strpos(strtoupper(PHP_OS), 'WIN')!==false) { 
		// sometimes the dir can have double slashes on Win, remove 'em
		$_FILES[$field_id]['tmp_name'] = str_replace ('\\\\', '\\', $_FILES[$field_id]['tmp_name']);
	}

	if (move_uploaded_file($_FILES[$field_id]['tmp_name'], $uploadfile)) {
		//if unix, update permissions
		chmod ($uploadfile, JB_NEW_FILE_CHMOD);
		// plugins can hook here to do extra processing on the file
		JBPLUG_do_callback('save_image', $uploadfile, $field_id, $user_id); 
	} else {
		//echo htmlentities('Could not move the image form the temp directory.  (FROM: '.$_FILES[$field_id]['tmp_name'].' ->> TO: '.$uploadfile.') ').PHP_OS."<br>\n";

		switch ($_FILES[$field_id]["error"]) {
			case UPLOAD_ERR_OK:
			   jb_custom_error_handler('upload', "Uploaded the file OK, but the move failed", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_INI_SIZE:
			   jb_custom_error_handler('upload', "The uploaded file exceeds the upload_max_filesize directive (".ini_get("upload_max_filesize").") in php.ini.", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_FORM_SIZE:
			   jb_custom_error_handler('upload', "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.", __FILE__, 0, $vars);
			   break;
			case UPLOAD_ERR_PARTIAL:
			   jb_custom_error_handler('upload', "The uploaded file was only partially uploaded.", __FILE__, 0, $vars);
			   break;
			case UPLOAD_ERR_NO_FILE:
			   jb_custom_error_handler('upload', "No file was uploaded.", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_NO_TMP_DIR:
			   jb_custom_error_handler('upload', "Missing a temporary folder.", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_CANT_WRITE:
			   jb_custom_error_handler('upload', "Failed to write file to disk", __FILE__, __LINE__, $vars);
			   break;
			default:
			   jb_custom_error_handler('upload', "Unknown File Error", __FILE__, __LINE__, $vars);
		}
	}

	// resize

	JB_gd_resize_image($field_id, $uploadfile, $thumbfile); // use GD Library

	@chmod ($thumbfile, JB_NEW_FILE_CHMOD);

	if (JB_KEEP_ORIGINAL_IMAGES=='YES') {

		// resize the original image.

		if (!defined('JB_BIG_IMG_MAX_WIDTH')) {
			define('JB_BIG_IMG_MAX_WIDTH', 1000);
		}
	
		JB_gd_resize_image($field_id, $uploadfile, $thumbfile.'.tmp', JB_BIG_IMG_MAX_WIDTH); // use GD Library
		
		unlink($uploadfile);
		// move the original image to the upload_files/images/ directory
		copy ($thumbfile.'.tmp', $uploadfile);
		unlink($thumbfile.'.tmp');

	} else {
  
		@unlink($uploadfile); // delete the original file.

	}

   return $new_name;
} 




###############################################################

function JB_gd_resize_image($field_id, $uploadfile, $thumbfile, $max_width=false) {

	if ($max_width==false) {
		if (JB_IMG_MAX_WIDTH=='JB_IMG_MAX_WIDTH' ) {
			$max_width = '150';
		} else {
			$max_width = JB_IMG_MAX_WIDTH;
		}
	}


	$current_size = getimagesize($uploadfile);
    $width_orig = $current_size[0];
    $height_orig = $current_size[1];
 
	if ($width_orig > $max_width) {

		// The file
		$filename = $uploadfile;

		// Set a maximum height and width
		$width = $max_width;
		$height = 200;

		$ratio_orig = $width_orig/$height_orig;

		if ($width/$height > $ratio_orig) {
		   $width = $height*$ratio_orig;
		} else {
		   $height = $width/$ratio_orig;
		}

		// Resample
		$image_p = imagecreatetruecolor($width, $height);
		
		switch ($_FILES[$field_id]['type']) {
			case "image/gif":
				touch ($filename);
				$uploaded_img = imagecreatefromgif($filename);
				imagecopyresampled($image_p, $uploaded_img, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				#unlink ($filename); // delete original file 
				// Output
				imagegif($image_p, $thumbfile, 100);

				break;
			case "image/jpg":
			case "image/jpeg":
			case "image/pjpeg":
				touch ($filename);
				$uploaded_img = imagecreatefromjpeg($filename);
				imagecopyresampled($image_p, $uploaded_img, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				#unlink ($filename); // delete original file 
				// Output
				imagejpeg($image_p, $thumbfile, 100);
				break;
			case "image/png":
			case "image/x-png":
				touch ($filename);
				$uploaded_img = imagecreatefrompng($filename);
				imagecopyresampled($image_p, $uploaded_img, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				#unlink ($filename); // delete original file 
				// Output
				imagepng($image_p, $thumbfile, 9);
				break;
			

			default:
				trigger_error('Image type not supported:'.$_FILES[$field_id]['type'], E_USER_WARNING);
				return false;
				break;
		}

		imagedestroy ($uploaded_img);
		imagedestroy ($image_p);

	} else {
     // echo 'No need to resize.';
      copy ($uploadfile, $thumbfile);
   } 



}

#####################################################################
/*

Get the base path where the file uploads are stored.

*/
function JB_get_image_file_path($file_name='') {
	return JB_get_file_path($file_name, 'IMAGE');
}

###########################################################


function JB_get_thumb_file_path($file_name='') {
	return JB_get_file_path($file_name, 'THUMB');	
}
###########################################################
// 
function JB_delete_image_from_field_id($table_name, $primary_key_name, $primary_key_id, $field_id) {
   $sql = "SELECT `$field_id` FROM `$table_name` WHERE `$primary_key_name`='".JB_escape_sql($primary_key_id)."'";
   $result = JB_mysql_query($sql) or die (mysql_error().$sql);
   $row = mysql_fetch_array ($result, MYSQL_ASSOC);
   if ($row[$field_id] != '') {
	  JB_delete_image($row[$field_id]);
   }

}

###########################################################


function JB_is_empty_dir($dir) {
    if (($files = @scandir($dir)) && count($files) <= 2) {
        return true;
    }
    return false;
}

###########################################################

function JB_delete_image($file_name) {

	// delete thumbs
	if (JB_image_thumb_file_exists($file_name)) {
		unlink (JB_get_thumb_file_path($file_name));
		
	}

	// delete originals
	
	if (JB_image_original_file_exists($file_name)) {
	   unlink (JB_get_image_file_path($file_name));
	}
    
    // delete thumbs made by plugins
    // the following routine will search the file's dir
    // and delete any files with similar names.
    
    // eg. the base file is 68b3b0e6b3e44f687e1ec0cff4e79505.png
    // files created by plugins may be:
    // TE_68b3b0e6b3e44f687e1ec0cff4e79505.png
    // HS_68b3b0e6b3e44f687e1ec0cff4e79505.png
    // (ie. files created by plugins are prefixed with their initials)
    
    $dir = dirname(JB_get_thumb_file_path($file_name));
    $basefile = basename(JB_get_thumb_file_path($file_name));
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
            
                if (strpos($file, $basefile)!==false) {
                    
                    unlink($dir .'/'. $file);
                }
            }
            closedir($dh);
        }
    }
	

}

################################################################

function JB_image_thumb_file_exists($file_name) {
	if ($file_name=='') {
		return false;
	}
	$path = JB_get_file_path($file_name, 'THUMB');
	return (is_file($path) && file_exists($path));

}

##########################################

function JB_image_original_file_exists($IMAGE) {
	if ($IMAGE=='') {
		return false;
	}
	$path = JB_get_file_path($IMAGE, 'IMAGE');
	return (is_file($path) && file_exists($path));

}

###########################################################

function JB_get_image_thumb_src($file_name) {
	return JB_get_upload_file_URI($file_name, 'THUMB') ;
}

###########################################################


function JB_get_image_src($file_name) {
	return JB_get_upload_file_URI($file_name, 'IMAGE') ;
}

#################################################################



#########################################
// Directory Information Functions
#########################################


// get the base instalation directory of the job board
// with slash / at the end.
function JB_basedirpath() {

	static $dir;
	if (isset($dir)) return $dir;

	$dir = dirname(__FILE__);
	$dir = explode (DIRECTORY_SEPARATOR, $dir);
	$blank = array_pop($dir);
	$dir = implode('/', $dir);

	$dir = $dir.'/';

	return $dir;

}

##########################################


function JB_get_cache_dir() {

	static $dir;
	if (isset($dir)) return $dir;

	$dir = JB_basedirpath();
	$dir = $dir.'cache/';
	JBPLUG_do_callback('get_cache_dir', $dir);


	return $dir;

}

#################################################

function jB_get_lang_dir() {
	static $dir;
	if (isset($dir)) return $dir;
	$dir = JB_basedirpath().'lang/';
	JBPLUG_do_callback('get_lang_dir', $dir);
	return $dir;

}

#################################################



function JB_get_theme_dir() {

	static $dir;

	if (isset($dir)) return $dir;

	$dir = JB_basedirpath();
	$dir = $dir.'include/themes/';
	if (function_exists('JBPLUG_do_callback')) {
		JBPLUG_do_callback('get_theme_dir', $dir);
	}


	return $dir;

}

##########################################

function jb_get_english_default_dir() {
	static $dir;
	if (isset($dir)) return $dir;
	$dir = JB_basedirpath().'lang/';
	JBPLUG_do_callback('get_english_default_dir', $dir);
	return $dir;
}

###################################################

function jb_get_config_dir() {
	static $dir;
	if (isset($dir)) return $dir;
	$dir = JB_basedirpath();
	JBPLUG_do_callback('get_config_dir', $dir);
	return $dir;
}

###################################################

function JB_get_rss_dir() {
	static $dir;
	if (isset($dir)) return $dir;
	$dir = JB_basedirpath();
	JBPLUG_do_callback('get_rss_dir', $dir);
	return $dir;
}

#####################################################
/*

function:

JB_get_relative_path

description:

Get the relative path, given the absolute $path
This function uses $_SERVER['PHP_SELF'] to determine the relative path
Warning: String returned from this function must be escaped before
outputting to the browser.

Relies on $_SERVER['PHP_SELF']

arguments:

$path - The path where the resource is located, eg 'include/lib/GoogleMap/'

returns:

relative path as a string. 
eg. if browser is in the path/to/somehere/admin/ directory, 'include/lib/GoogleMap/' will return
../include/lib/GoogleMap/


*/
function JB_get_relative_path($path) { // added in 3.6.2

	// eg. $path = 'include/lib/GoogleMap/', we are browsing from admin/
	// if JB_BASE_HTTP_PATH is 'http://example.com/path/to/somehere/'

	$self = dirname($_SERVER['REQUEST_URI']); // $self would be '/path/to/somehere/admin/'
    
    $url_parts = parse_url(JB_BASE_HTTP_PATH); // so that we can use only the 'directory 'path' part of the URL
	$src = $url_parts['path']; // $src would be '/path/to/somehere/'
    
	$src = str_replace($src, '',  $self); // eg. '/path/to/somehere/admin' becomes just 'admin/'
	
    $src = jb_compute_rel_path($src, $path); // adds '..' to the path, eg $path of 'include/lib/GoogleMap/' becomes ../include/lib/GoogleMap/ 
	
	return $src;


}

#############################################

function jb_compute_rel_path($rel, $base) {

	$rel = explode('/', $rel);

	foreach ($rel as $val) {
		if ($val) $src .= '../';
	}
	$src .= $base;
 
	return $src;
	

}


###############################################################

/*

Resolve the document's absolute path on disk, given the document's URL 


*/
function JB_resolve_document_path($URL=JB_BASE_HTTP_PATH) {

	/*

	 The way this function works is by taking the directory part of the URL
	 and then breaking up the basedirpath in to parts
	 then a loop searches for the file by testing different path names.

	 The path names are formed by appending a base part and concatenating with
	 the URL path. A match is made once the file exists!

	 Eg. The test URL starts form:
	 1. / - concatenate with /jobs/images/something.png : does not exist
	 2. /home/ - concatenate with /jobs/images/something.png  : does not exist
	 3. /home/user/ - concatenate with /jobs/images/something.png  : does not exist
	 4. /home/user/www/ - concatenate with /jobs/images/something.png  : exists! return it :)

	*/
	
	$URL_parts = parse_url($URL);

	$base_parts = explode('/', jb_basedirpath());  
	
	$test_path = array_shift($base_parts);
	foreach ($base_parts as $p) {
		if (!$p) {
			continue;
		}
		$test_path = $test_path.'/'.$p;

		// basedir restriction compatible - do not check outside the basedir
		if ((strpos($test_path.$URL_parts['path'], jb_basedirpath())===0) 
			&& file_exists($test_path.$URL_parts['path'])) {
			return  ($test_path.$URL_parts['path']);
		}
	}

	return false;
}



?>