<?php

define ("SHOW_SUBCATS", 10000);
if ($this==null) die('cannot access directly');
global $IndeedXML_default_l;
global $IndeedXML_default_k;
$IndeedXML_default_l = $this->config['l']; // default location
$IndeedXML_default_k = $this->config['k']; // default keyword

function showAllCat($child, $cols)
{
   global $connection;
   global $catName;
   global $form_id;
   # initialise the global subcat flag  

   # query to get all the nodes that are the 
   # children of child id

    $query = "SELECT categories.*, categories.category_id as CID, cat_name_translations.category_name AS NAME FROM categories LEFT JOIN cat_name_translations ON categories.category_id=cat_name_translations.category_id WHERE parent_category_id='".jb_escape_sql($child)."' AND form_id=1 AND (lang='".jb_escape_sql($_SESSION['LANG'])."') ORDER BY list_order, category_name ";


   $x=0;
   # do the query
   $result = jb_mysql_query ($query) or die($query. mysql_error());
   while ($row = JB_mysql_fetch_array($result)) {
      $cats[] = $row;
      $x++;
      if ($x==$cols) { 
         showRow($cats);
         unset($cats); # clear array
         $x=0;
      } 
   }
   # show the remaining cats
   showRow($cats);

}

############################################################
function showRow ($cats) {
      echo "<tr>";

   for ($x=0; $x < count($cats); $x++) {
      showCat($cats[$x]);
   }
      echo "</tr>";


}

############################################################

function showCat ($cat) {
 
   global $MODE;


   echo "<td valign=top width='33%'>";
   //echo '<IMG alt="&gt;" src="images/arrow.gif" width="6" height="9" border="0" alt="">';
   echo "<A HREF=\"".htmlentities($_SERVER['PHP_SELF'])."?cat=".$cat['CID']."\"><span class='cat_heading'>".jb_escape_html($cat['NAME'])."'</span></A>"; //echo " (ID: ". ($cat[0]).") ";
	//if  ($cat['allow_records']=='N') echo "<b>&#8224;</b>";
	
	
  if ($cat['CID'] > 0) {
	 
      showSubcat($cat['CID']);
   } else {

	 


   }
  
   echo "</td>";


}

#################################################################################

function showSubcat ($c) {
	//global $connection;

	global $IndeedXML_default_l;
	global $IndeedXML_default_k;

  
	$query = " SELECT *, categories.category_id as CID, seo_keys FROM categories LEFT JOIN IndeedXML_keywords ON categories.category_id = IndeedXML_keywords.category_id  WHERE parent_category_id = '".jb_escape_sql($c)."' and form_id=1 ORDER BY list_order, category_name ASC ";

 
   $result = jb_mysql_query ($query) ;

  //echo "<b>cateid:".$row2[1]."  $query </b>";

  if (JB_mysql_num_rows($result)==0) {

	  $query = " SELECT kw, loc, categories.category_id as CID, seo_keys FROM categories LEFT JOIN IndeedXML_keywords ON categories.category_id = IndeedXML_keywords.category_id  WHERE categories.category_id = '".jb_escape_sql($c)."' and form_id=1 ";

 
	 $result = jb_mysql_query ($query) ;
	 $row = JB_mysql_fetch_array($result);

	 if (trim($row['loc'])=='') {
		$row['loc'] = $IndeedXML_default_l;
	}

	 if (trim($row['kw'])=='') {
		$row['kw'] = $row['seo_keys'];
	}

	if (trim($row['kw'])=='') { // still empty?
		$row['kw'] = JB_getCatName($row['CID']); //$IndeedXML_default_k;
	}

	

	  ?>
	What: <input size='36' type="text" value="<?php echo jb_escape_html($row['kw']);?>" name='kw_<?php echo $row['CID'];?>'>
	Where: <input size='36' type="text" value="<?php echo jb_escape_html($row['loc']);?>" name='loc_<?php echo $row['CID'];?>'><br>
	<?php

  } else {


	$x=0;
	echo "<br><div style='margin-left: 20px;'>";
	while ($row = JB_mysql_fetch_array($result)) {
		$x++;
		if ($x > SHOW_SUBCATS) break;
		
		//if  ($row['allow_records']=='N') echo "<b>&#8224;</b>";
		echo "<A HREF=".$_SERVER['PHP_SELF']."?cat=".$row['CID']."><font color=#0000FF>".jb_escape_html($row['category_name'])."</font></A> ";//;echo "<small>(ID: ". ($row[0]).")</small>";

		if (trim($row['loc'])=='') {
			$row['loc'] = $IndeedXML_default_l;
		}

		if (trim($row['kw'])=='') {
			$row['kw'] = $row['seo_keys'];
		}

		if (trim($row['kw'])=='') { // still empty?
			//$row['kw'] = $IndeedXML_default_k;
			$row['kw'] = JB_getCatName($row['CID']);
		}

		?>
		What: <input size='35' type="text" value="<?php echo jb_escape_html($row['kw']);?>" name='kw_<?php echo $row['CID'];?>'>
		Where: <input size='35' type="text" value="<?php echo jb_escape_html($row['loc']);?>" name='loc_<?php echo $row['CID'];?>'>
		<br>

		<?php

		// does this category have sub-categories?
		
		$query = " SELECT * FROM categories LEFT JOIN IndeedXML_keywords ON categories.category_id = IndeedXML_keywords.category_id  WHERE parent_category_id = '".$row['CID']."' ORDER BY list_order, category_name ASC ";

		$result2 = jb_mysql_query ($query);

		if (JB_mysql_num_rows($result2)>0) {
	
		  echo "<br>";
		  $row2 = JB_mysql_fetch_row($result2);
		 
		?>
		  <table style="margin-left: 15px;" cellspacing="1" border="0" width="100%">

		<?php
		showAllCat($row['CID'], 1);
		?>

		</table>
		<?php
		} 

	}

  }
 
   echo "</div>";


}




?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
<link rel="stylesheet" type="text/css" href="<?php echo JB_get_admin_maincss_url(); ?>" >
</head>

<body>
<b>[Indeed XML]</b> 
	<span style="background-color:#FFFFCC; border-style:outset; padding:5px; "><a href="p.php?p=IndeedXML&action=kw">Setup Keywords</a></span> 
	<span style="background-color:#F2F2F2; border-style:outset; padding:5px; "><a href="plugins.php?plugin=IndeedXML">Configure plugin</a></span>
	
	<hr>
<?php

if ($_REQUEST['save']!='') {

	$sql = "delete from IndeedXML_keywords  ";
	jb_mysql_query($sql);

	foreach ($_REQUEST as $key=>$val) {

		if (preg_match('/kw_(\d+)/', $key, $m)) {
			
			$sql = "REPLACE INTO IndeedXML_keywords (category_id, kw, loc) VALUES (".jb_escape_sql($m[1]).", '".jb_escape_sql(trim($_REQUEST['kw_'.$m[1]]))."' , '".jb_escape_sql(trim($_REQUEST['loc_'.$m[1]]))."')";


			jb_mysql_query($sql);

		}

	}
	echo '<p class="ok_msg_label">Keywords Saved</p>';

}

?>
<h3>Review all the keywords</h3>
<p>Please click 'Save' at least once. The keywords shown below will not be confirmed unless 'Save' is clicked. (The 'Where' part is optional)</p>
	<form method='POST' action='<?php echo $_SERVER['PHP_SELF'];?>?p=IndeedXML&amp;action=kw' >
<input type='hidden' name='board_id' value="<?php echo jb_escape_html($_REQUEST['board_id']);?>'>

<table cellspacing="1" border="0" width="100%">


	<?php

	showAllCat($_REQUEST['cat'], 1);


	?>

	</table>
<p>
<input type='submit' value='Save' name='save'>
</form>
<hr>
 <h3>Indeed Keyword Syntax</h3>
 <p>Indeed supports a few andvanced features.<br>
 To search within the title, prefix with 'title:', eg<br>
 title:Pharmaceutical Sales Alabama<br>
 Put quotes around keywords to get the exact phrase, eg<br>
 "Pharmaceutical Sales Alabama"<br>
 To exclude, prefix with '-', eg<br>
 -Sales<br>
 See more options here: http://www.indeed.com/advanced_search<br>
 You can test your keywords there, and if they work, paste them here