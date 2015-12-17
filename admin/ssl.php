<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('NO_HOUSE_KEEPING', true);
require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> SSL');

?>

<h3>SSL protection</h3>
<p>If your server has an SSL certificate installed then it is possible to access the admin/ , employers/ and myjobs/ directories using SSL without any modification to the job board files. To force users to the SSL version of your site, simply modify the links in your theme's template files. </p>
<p>Unfortunatelly, It is out of scope for Jamit Software to support your SSL settings, as each server is different and installation of a cerificate varies depending on your server.</p>
<p>However, if you are using the Apache server, then you can put the following rules in to your .htaccess file to automatically force users to SSL: (For Apache experts only, not supported by Jamit Software)</p>
<?php
$base = JB_BASE_HTTP_PATH;

$base = preg_replace ('#https?://#', '', JB_BASE_HTTP_PATH);
$a = array();
$a = explode('/',$base);

$host = array_shift($a); // get rid of the host part

$base = implode("/", $a);

?>
<hr>
<pre>
&lt;IfModule mod_rewrite.c>

RewriteEngine on
# Force HTTPS when going to these directories
RewriteCond %{HTTPS} off

RewriteCond %{REQUEST_URI} <?php echo $base.JB_EMPLOYER_FOLDER; ?> [OR]
RewriteCond %{REQUEST_URI} <?php echo $base.JB_CANDIDATE_FOLDER; ?>

RewriteCond %{REQUEST_URI} <?php echo $base; ?>admin/

# RewriteRule for the above conditions:
RewriteRule (.*) %{HTTP_HOST}%{REQUEST_URI} [R,L]

# Turn off HTTPS when going to the home-page
RewriteCond %{HTTPS} on
RewriteRule ^<?php echo $base; ?>$ %{HTTP_HOST}%{REQUEST_URI} [R,L]
RewriteCond %{HTTPS} on
RewriteRule ^<?php echo $base; ?>index\.php$ %{HTTP_HOST}%{REQUEST_URI} [R,L]


&lt;/IfModule>
</pre>


<?php

JB_admin_footer();

?>