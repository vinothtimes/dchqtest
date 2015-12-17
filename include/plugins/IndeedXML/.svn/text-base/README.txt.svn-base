Name: Indeed XML Back-fill
Author: Jamit Software
Price: Market
Requires: Jamit Job Board 3.5.0 or higher
Version: 2.1 - Dec 7th, 2010
Description: Fill your un-used job 
posting slots with sponsored listings & earn additional revenue. 
This plugin back-fills unused job posting slots with search results from 
indeed.com (The jobs are are not imported, but instead, they are fetched from 
Indeed's XML API, and displayed on-the-fly). Note: The plugin will not work if 
your server cannot make outgoing connections.

####################################################

INSTALL

Prerequisite: You will need to sign up to Indeed's publisher program here:
https://ads.indeed.com/jobroll/ (There is no need to copy and paste the
attribution code provided by Indeed as the plugin already includes it)


To install, upload the IndeedXML folder to the include/plugins/ directory

Make sure that the plugin files are all in the IndeedXML sub-directory
inside include/plugins/ directory for the plugin to work.

Then go to The Admin section to enable your plugin.


###############################################
TROUBLE SHOOTING

> Keywords do not return any results?
Try your keyword on indeed.com first, before putting them in the job board

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

> Can I make the links open in a new window?

Indeed rules are that in order to record the click, it must use their 
onmousedown event to call their javascript, and the javascripts 
prevents the link from opening in a new window.

> It still does not work

Please check the requirements - requires Jamit Job Board 3.5.0 or higher



###############################################
CHANGES
2.2
- Expanded countries.
2.1
- Category keywords config: multi-lingual category names
2.0
- Small change to support Indeed's XML API v2
1.9
- Multi-lingual support
- added countries: Ireland, Hong Kong, China, Japan, Korea, Colombia, 
Singapore, South Africa, Sweden, New Zealand, Poland, Argentina, Switzerland,
Mexico, Italy, Brazil, Belgium, Austria, Australia
1.8
- Fixed a bug with double quoted queries, changed parser to read by chunks.
1.7
- Fixed a bug with the cache dir
1.6
- Added many new options to the configuration
1.5
- Support for channels
1.4
- Corrected bug with the starting parameter
- Added new country parameter and added new countries
1.3
- Bug Fix: Remote address was not added to request
- Bug Fix: Saved proxy URL did not display
- Improved search keyword integration
- New: 'Back-fill Results' option, results can continue to further pages
- New: Multiple search parameters can be combined
1.2
- Added support for cURL and HTTP Proxy
- Added support for Indeed's international sites
1.1 
- Fixed the broken links by adding a workaround for PHP XML parser bug
- trouble-shooting text
- URL encode of location keyword
1.0 
- First released