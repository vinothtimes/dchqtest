Name: Career Jet Back-fill
Author: Jamit Software
Price: Market
Requires: Jamit Job Board 3.5.0 or higher
Version: 1.2
Description: Fill your un-used job 
posting slots with listings from around the web. 
This plugin back-fills unused job posting slots with search results from 
CareerJet (The jobs are are not imported, but instead, they are fetched from 
careerJet's API, and displayed on-the-fly). Note: The plugin will not work if 
your server cannot make outgoing connections.

####################################################

INSTALL



To install, upload the CareerJet folder to the include/plugins/ directory

Make sure that the plugin files are all in the CareerJet sub-directory
inside include/plugins/ directory for the plugin to work.

Then go to The Admin section to enable your plugin.


###############################################
TROUBLE SHOOTING

> Keywords do not return any results?
Try your keyword on careerjet.com first, before putting them in the job board

> Page times out / does not fetch any results?
Your server must be able to make external connections to careerjet.com
through port 80 (HTTP). This means that fsockopen must be enabled on
your host, and must be allowed to make external connections.

- I see warning/errors messages saying that 'argument 2' is missing.
This has been reported and can be fixed if you open the include/lists.inc.php
file and locate the following code:

JBPLUG_do_callback('job_list_data_val', $val, $template_tag);

and change to:

JBPLUG_do_callback('job_list_data_val', $val, $template_tag, $a);


> It still does not work

Please check the requirements - requires Jamit Job Board 3.5.0 or higher



###############################################
CHANGES
1.3
- Attribution setting did not save.
1.2
- Category keywords config: multi-lingual category names
1.1
- Change 'juju' labels to careerjet, update links
1.0
- First released