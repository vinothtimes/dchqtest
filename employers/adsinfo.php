<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ('../config.php');
echo $JBMarkup->get_doctype();
$JBMarkup->markup_open(); // <html>
$JBMarkup->head_open();



$JBMarkup->title_meta_tag( 'Ads Info');

$JBMarkup->stylesheet_link(JB_get_maincss_url());

$JBMarkup->head_close();

$JBMarkup->body_open('style="margin:0px; background-color:white"');

?>
<table style="margin: 0 auto; width:90%; border:0px" cellpadding="0" cellspacing="0" >
    <tr>
      <td>
      <?php echo $label['ads_info'];?>
      <p></td>
    </tr>
	<tr>
	<td><IMG alt="example" src="<?php echo JB_THEME_URL; ?>images/example.gif" width="524" height="415" border="0" alt="">
	<br>
	<p style="text-align: center">
	<a href="credits.php" target="_parent" onclick="window.close(); window.opener.parent.location='credits.php';">
	<IMG alt="buy now" src="<?php echo JB_THEME_URL; ?>images/<?php echo $label['buy_p_posts_button_img']; ?>" width="187" height="41" border="0" alt="">
	</a>
	<br>
	<input type="button" onclick="window.close()" value="Close">
	</td>
	</tr>
  </table>

<?php

$JBMarkup->body_close();
$JBMarkup->markup_close();