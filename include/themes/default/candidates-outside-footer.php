</div>

<?php 

JBPLUG_do_callback('can_outside_before_body_end', $A = false);

$JBMarkup->body_close();
$JBMarkup->markup_close();

?>