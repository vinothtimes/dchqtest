Name: Jobs Filler
Author: Jamit Software
Price: Free
Requires: Jamit Job Board 3.5.0 or higher
Version: 1.3 - Nov 10th, 2011
Description: Fill your un-used job 
posting slots with real job postings from other job boards.
Uses the Jamit API (Beta). This service may change at any time.
####################################################

INSTALL

This plugin is distributed with the job board by default

To install, upload the JobFiller folder to the include/plugins/ directory

Make sure that the plugin files are all in the Jobs Filler sub-directory
inside include/plugins/ directory for the plugin to work.

Then go to The Admin section to enable your plugin.


###############################################
TROUBLE SHOOTING


> Page times out / does not fetch any results?
Your server must be able to make external connections to api.indeed.com
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
- Fix to the markup (layout)
1.2
- Ignore 'city' if 'city' and 'state' are identical for the location
1.1
- Category keywords config: multi-lingual category names
1.0 
- First released