Name: Simply Hired XML Back-fill
Author: Jamit Software
Price: Market
Requires: Jamit Job Board 3.5.0 or higher
Version: 1.5
Description: Fill your un-used job 
posting slots with with search results from 
simplyhired.com (The jobs are are not imported, but instead, they are fetched from 
Indeed's XML API, and displayed on-the-fly). Note: The plugin will not work if 
your server cannot make outgoing connections.

####################################################

INSTALL

This plugin relies in SimplyHired's XML API
http://www.simplyhired.com/a/publishers/overview
Please review the terms and conditions on SimplyHired.com
Note: Only select partners are eligible to participate in SimplyHired's
affiliate program 


To install, upload the SimplyHiredXML folder to the include/plugins/ directory

Make sure that the plugin files are all in the SimplyHiredXML sub-directory
inside include/plugins/ directory for the plugin to work.

Then go to The Admin section to enable your plugin.


###############################################
TROUBLE SHOOTING

> Keywords do not return any results?
Try your keyword on simplyhired.com first, before putting them in the job board
Notice: There are three result styles which can be switched on the form above.

> Page times out / does not fetch any results?
Your server must be able to make external connections to api.simplyhired.com
through port 80 (HTTP). This means that fsockopen must be enabled on
your host, and must be allowed to make external connections.

- I see warning/errors messages saying that 'argument 2' is missing.
This has been reported and can be fixed if you open the include/lists.inc.php
file and locate the following code:

JBPLUG_do_callback('job_list_data_val', $val, $template_tag);

and change to:

JBPLUG_do_callback('job_list_data_val', $val, $template_tag, $a);

> Can I make the links open in a new window?

Indeed rules are that in order to record the click, it must use their 
onmousedown event to call their javascript, and the javascripts 
prevents the link from opening in a new window.

> It still does not work

Please check the requirements - requires Jamit Job Board 3.5.0 or higher



###############################################
CHANGES
1.5
- Category keywords config: multi-lingual category names
1.4
- Added a new option: 'Show the Day, and how many days elapsed?'
- Fixed a bug with 'How to back-fill -> Stop after filling the first page'
1.3
- Multi-lingual support
1.2
- Added more countries 
- added 'result style' option
1.1
- Changed parser to read by chunks
1.0 
- First released