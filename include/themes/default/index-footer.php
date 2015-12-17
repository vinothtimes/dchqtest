</td>
     </tr>
      </table>
   </td>
 </tr>
  <tr>
<td width="100%" colspan="4" align="left" bgcolor="#D5D6E1" height="10">
<?php JBPLUG_do_callback('index_footer_adcode', $A = false);?></td>
  </tr>
  <tr>
    <td width="100%" colspan="4" align="left" bgcolor="#E9E8FD" height="10">
	<div style="text-align:center;font-size:x-small; ">Powered by <a href="http://www.jamit.com/">Jamit Job Board</a></div>
			</td>
  </tr>
</table>
<?php
// the following code is for the map
// Since 3.6.3, Google maps can be used and the old map feature is deprecated
if (($_REQUEST['post_id'] != '') && (JB_MAP_DISABLED=="NO")) {
	$pin_y = (int) $prams['pin_y'];
	$pin_x = (int) $prams['pin_x'];
	// echo the javascript to position the pin on the map
	JB_echo_map_pin_position_js ($pin_x, $pin_y);
} 

 

$JBMarkup->body_close();
$JBMarkup->markup_close();

?>