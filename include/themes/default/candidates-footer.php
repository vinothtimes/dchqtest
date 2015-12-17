</div>
</td>
     </tr>
      </table>
   </td>
 </tr>
  <tr>
<td width="100%" colspan="4" align="left" bgcolor="#D5D6E1" height="10">
		</td>
  </tr>
  <tr>
    <td width="100%" colspan="4" align="left" bgcolor="#E9E8FD" height="10"> 
			</td>
  </tr>
</table>


<?php


if (($_REQUEST['post_id'] != '') && (JB_MAP_DISABLED=="NO") ) {
	$pin_y = (int) $prams['pin_y'];
	$pin_x = (int) $prams['pin_x'];
	// echo the javascript to position the pin on the map
	JB_echo_map_pin_position_js ($pin_x, $pin_y);
}

JBPLUG_do_callback('can_before_body_end', $A = false);

$JBMarkup->body_close();
$JBMarkup->markup_close();


?>