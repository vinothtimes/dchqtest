<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Stats');
?>
<b>[Statistics]</b> <span style="background-color: <?php if ($_REQUEST['report']=='') { echo  '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding: 5px;"><a href="stats.php">Monthly Tally</a></span> <!-- <span style="background-color: <?php if ($_REQUEST['report']=='C') { echo  '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding:5px; "><a href="stats.php?report=C">Candidates</a></span> <span style="background-color: <?php if ($_REQUEST['report']=='R') { echo   '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding:5px; "><a href="stats.php?report=R">Resumes</a> </span> 
<span style="background-color: <?php if ($_REQUEST['report']=='A') { echo   '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding:5px; "><a href="stats.php?report=A">Applications</a> </span><span style="background-color: <?php if ($_REQUEST['report']=='E') { echo   '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding:5px; "><a href="stats.php?report=E">Employers</a></span> <span style="background-color: <?php if ($_REQUEST['report']=='P') { echo   '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding:5px; "><a href="stats.php?report=P">Posts</a></span>--><span style="background-color: <?php if ($_REQUEST['report']=='REV') { echo   '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding:5px; "><a href="stats.php?report=REV">Revenue</a></span>
	

<hr>

<?php

define ('STAT_MONTH_LIMIT', 24);

function get_monthly_stat_query($report) {

	switch ($report) {

		case 'A':

			$sql = "SELECT DATE_FORMAT( app_date, '%Y-%m' ) MYDATE , count( * ) as COUNT 
					FROM `applications`
					GROUP BY DATE_FORMAT( app_date, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;

		case 'C':

			$sql = "SELECT DATE_FORMAT( SignupDate, '%Y-%m' ) MYDATE , count( * ) as COUNT 
					FROM `users`
					GROUP BY DATE_FORMAT( SignupDate, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		case 'R':
			
			$sql = "SELECT DATE_FORMAT( resume_date, '%Y-%m' ) MYDATE , count( * ) as COUNT 
					FROM `resumes_table`
					GROUP BY DATE_FORMAT( resume_date, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		case 'RH':
			
			$sql = "SELECT DATE_FORMAT( resume_date, '%Y-%m' ) MYDATE , sum( hits) as COUNT 
					FROM `resumes_table`
					GROUP BY DATE_FORMAT( resume_date, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		case 'E':
			
			$sql = "SELECT DATE_FORMAT( SignupDate, '%Y-%m' ) MYDATE , count( * ) as COUNT 
					FROM `employers`
					GROUP BY DATE_FORMAT( SignupDate, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		case 'EP':
			
			$sql = "SELECT DATE_FORMAT( profile_date, '%Y-%m' ) MYDATE , count( * ) as COUNT 
					FROM `profiles_table`
					GROUP BY DATE_FORMAT( profile_date, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		case 'P':
			
			$sql = "SELECT DATE_FORMAT( post_date, '%Y-%m' ) MYDATE , count( * ) as COUNT 
					FROM `posts_table`
					WHERE post_mode != 'premium'
					GROUP BY DATE_FORMAT( post_date, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		case 'PH':
			
			$sql = "SELECT DATE_FORMAT( post_date, '%Y-%m' ) MYDATE , sum( hits ) as COUNT 
					FROM `posts_table`
					GROUP BY DATE_FORMAT( post_date, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		case 'PR':
			
			$sql = "SELECT DATE_FORMAT( post_date, '%Y-%m' ) MYDATE , count( * ) as COUNT 
					FROM `posts_table`
					WHERE post_mode = 'premium'
					GROUP BY DATE_FORMAT( post_date, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		case 'SJ':
			
			$sql = "SELECT DATE_FORMAT( save_date, '%Y-%m' ) MYDATE , count( * ) as COUNT 
					FROM `saved_jobs`
					GROUP BY DATE_FORMAT( save_date, '%Y-%m' ) ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		case 'REV';

			$sql = "SELECT DATE_FORMAT( date, '%Y-%m' ) MYDATE , sum( amount ) as COUNT, currency as CUR 
					FROM `jb_txn`
					WHERE `type`='DEBIT'
					GROUP BY DATE_FORMAT( date, '%Y-%m' ), currency ORDER BY MYDATE DESC LIMIT ".jb_escape_sql(STAT_MONTH_LIMIT);
			break;
		default:
			 $sql='select ID from employers where 1!=1';
			break;
	}
	return $sql;

}

function init_stat($report, &$stats) {

	#A, C, R, E, P

	$result = jb_mysql_query(get_monthly_stat_query($report));
	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
		$stats[$row['MYDATE']][$report]=$row['COUNT'];
	}

	

}

function big_stat_report() {

	$stats = array();
	init_stat('C', $stats);
	init_stat('R', $stats);
	init_stat('A', $stats);
	init_stat('E', $stats);
	init_stat('P', $stats);
	init_stat('PR', $stats);
	init_stat('SJ', $stats);
	init_stat('EP', $stats);
	init_stat('PH', $stats);
	init_stat('RH', $stats);

	// sort the stats
	ksort($stats, SORT_REGULAR);
	$stats = array_reverse($stats, true);

	?>
<h3>Monthly Tally</h3>
	<table border="0"  cellSpacing="1" cellPadding="5" bgColor="#d9d9d9">
		<tr>
			<td bgcolor="#E9E9E9"><b>Month</b></td>
			<td bgcolor="#E9E9E9"><b>Candidates<b></td>
			<td bgcolor="#E9E9E9"><b>Resumes<b></td>
			<td bgcolor="#E9E9E9"><b>Resume Hits<b></td>
			<td bgcolor="#E9E9E9"><b>Apps<b></td>
			<td bgcolor="#E9E9E9"><b>Employers<b></td>
			<td bgcolor="#E9E9E9"><b>Emp. Profiles<b></td>
			<td bgcolor="#E9E9E9"><b>Posts<b></td>
			<td bgcolor="#E9E9E9"><b>Pr. Posts<b></td>
			<td bgcolor="#E9E9E9"><b>Post Hits<b></td>
			<td bgcolor="#E9E9E9"><b>Saved Posts<b></td>
		</tr>

	<?php

	$tally = array();


	foreach ($stats as $date => $count) {

		?>
		<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgcolor="#FFFFFF">
			<td><?php echo $date; ?></td>
			<td><?php echo number_format($count['C']); $tally['C']+=$count['C']; if ($count['C']>0) $tc['C']++; ?></td>
			<td><?php echo number_format($count['R']); $tally['R']+=$count['R']; if ($count['R']>0) $tc['R']++; ?></td>
			<td><?php echo number_format($count['RH']); $tally['RH']+=$count['RH']; if ($count['RH']>0) $tc['RH']++; ?></td>
			<td><?php echo number_format($count['A']); $tally['A']+=$count['A']; if ($count['A']>0) $tc['A']++; ?></td>
			<td><?php echo number_format($count['E']); $tally['E']+=$count['E']; if ($count['E']>0) $tc['E']++; ?></td>
			<td><?php echo number_format($count['EP']); $tally['EP']+=$count['EP']; if ($count['EP']>0) $tc['EP']++; ?></td>
			<td><?php echo number_format($count['P']); $tally['P']+=$count['P']; if ($count['P']>0) $tc['P']++; ?></td>
			<td><?php echo number_format($count['PR']); $tally['PR']+=$count['PR']; if ($count['PR']>0) $tc['PR']++; ?></td>
			<td><?php echo number_format($count['PH']); $tally['PH']+=$count['PH']; if ($count['PH']>0) $tc['PH']++; ?></td>
			<td><?php echo number_format($count['SJ']); $tally['SJ']+=$count['SJ']; if ($count['SJ']>0) $tc['SJ']++; ?></td>
		</tr>
		<?php

	}

	?>
	<tr>
	<td>Totals</td>
		<td><?php echo number_format($tally['C']);  ?></td>
		<td><?php echo number_format($tally['R']); ?></td>
		<td><?php echo number_format($tally['RH']); ?></td>
		<td><?php echo number_format($tally['A']); ?></td>
		<td><?php echo number_format($tally['E']); ?></td>
		<td><?php echo number_format($tally['EP']); ?></td>
		<td><?php echo number_format($tally['P']); ?></td>
		<td><?php echo number_format($tally['PR']); ?></td>
		<td><?php echo number_format($tally['PH']); ?></td>
		<td><?php echo number_format($tally['SJ']); ?></td>
	</tr>
	<tr>
	<td>Avg</td>
		<td><?php if ($tc['C']>0) echo round($tally['C']/$tc['C'], 2);  ?></td>
		<td><?php if ($tc['R']>0) echo round($tally['R']/$tc['R'], 2); ?></td>
		<td><?php if (($tally['RH']>0) && ($tc['RH']>0)) echo number_format(round($tally['RH']/$tc['RH'], 2)); ?></td>
		<td><?php if ($tc['A']>0) echo round($tally['A']/$tc['A'], 2); ?></td>
		<td><?php if ($tc['E']>0) echo round($tally['E']/$tc['E'], 2); ?></td>
		<td><?php if ($tc['EP']>0) echo round($tally['EP']/$tc['EP'], 2); ?></td>
		<td><?php if ($tc['P']>0) echo round($tally['P']/$tc['P'], 2); ?></td>
		<td><?php if ($tc['PR']>0) echo round($tally['PR']/$tc['PR'], 2); ?></td>
		<td><?php if (($tally['RH']>0) && ($tc['RH']>0)) echo number_format(round($tally['PH']/$tc['PH'], 2)); ?></td>
		<td><?php if ($tc['SJ']>0) echo round($tally['SJ']/$tc['SJ'], 2); ?></td>
	</tr>

	</table>

	<?php


}

$sql = get_monthly_stat_query($_REQUEST['report']);

$result = jb_mysql_query($sql);

if (mysql_num_rows($result)>0) {

	$total = 0;

		switch ($_REQUEST['report']) {

		case 'A':
			echo "<h3>Monthly Applications</h3>";
		
			break;

		case 'C':
			echo "<h3>Monthly Candidate Signups</h3>";
			
			break;
		case 'R':
			echo "<h3>Monthly Resume updates</h3>";
			
			break;
		case 'E':
			echo "<h3>Monthly Employer Signups</h3>";
			
			break;
		case 'P':
			echo "<h3>Monthly Job Posts</h3>";
			
			break;
		case 'REV';
		echo "<h3>Monthly Revenue</h3>";
			
			break;
	
	}

	?>
	<table border="0"  cellSpacing="1" cellPadding="5" bgColor="#d9d9d9">
		<tr>
			<td bgcolor="#E9E9E9"><b>Date</b></td>
			<td bgcolor="#E9E9E9"><b>Count<b></td>
			<?php if ($_REQUEST['report']=='REV') { ?>
			<td bgcolor="#E9E9E9"><b>Currency<b></td>
			<?php } ?>

		</tr>

	<?php

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		
		$total+=$row['COUNT'];
		?>

		<tr>
			<td bgcolor="#FFFFFF"><?php echo $row['MYDATE']; ?></td>
			<td bgcolor="#FFFFFF"><?php echo $row['COUNT']; ?></td>
			<?php if ($_REQUEST['report']=='REV') { ?>
			<td bgcolor="#FFFFFF"><?php echo $row['CUR']; ?></td>
			<?php } ?>
		</tr>

		<?php


	}
	?>

	</table>

	<?php

	echo "Total: ".$total;


} else {

	big_stat_report();
	//echo "No data found.";

}

JB_admin_footer();
?>