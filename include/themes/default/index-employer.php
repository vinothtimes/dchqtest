
<br><p style="text-align:center"> <a href="<?php echo JB_get_go_back_link();?>"><b><?php echo $label['index_employer_jobs'];?></b></a> -> <b><?php echo $COMP_NAME; ?></b></p>

<?php


if ($profile_id) {
	$DynamicForm->display_form('view');
}

$label['listing_jobs_by_emp'] = str_replace ("%EMPLOYER_NAME%", $COMP_NAME, $label['listing_jobs_by_emp']);

echo "<div align='center'><h3>".$label['listing_jobs_by_emp']."</h3></div>";

JB_list_jobs ("ALL"); // list all the jobs by this employer

?>