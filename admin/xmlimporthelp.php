<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> XML Import Help');

?>

<b>[XML Import]</b> 
	<span style="background-color:#F2F2F2; border-style:outset; padding:5px;"><a href="xmlimport.php">Import Setup</a></span> 
	<span style="background-color:#F2F2F2; border-style:outset; padding:5px;"><a href="xmlimport_log.php">Import Log</a></span>
	<span style="background-color:#FFFFCC; border-style:outset; padding: 5px;"><a href="xmlimporthelp.php">Import Help</a></span>
	<hr>

<h3>What is XML Import?</h3>
<p>XML import is a tool which can parse various XML files and import the jobs data in to your job board. It is flexible enough to be 'trained' to work with any XML feed, as long as it contains the required data.</p>
<h3>What does it import?</h3>
<p>- Jobs</p>
<p>- Employer accounts (if present with the jobs data)</p>
<p>- Additionally, it can process commands to update or delete a job posting</p>
<h3>How does it fetch XML files?</h3>
<p>- Direct push to a URL on your server, using the POST method of the HTTP/HTTPS protocol. </p>
<p>- From a local file</p>
<p>- From a remote FTP server</p>
<p>- From any URL</p>
<h3>How to Setup</h3>
<p>To begin, you will need to prepare a sample file of the XML feed that you would like to import. This must should contain at least one record of a job post, but no more than one is needed.</p>
<p>1. Go to Admin-&gt;XML Import, click the 'Add a new Feed to Import' button</p>
<p>2. Fill in the details. For the XML Sample file, select the XML sample file that you prepared earlier.</p>
<p>3. Click on 'Please setup feed structure'. There you will be asked to identify the sequence element. In other words, click on the radio button next to the tag where the job record begins. The system will automatically draw a vertical green line to where it finds the end of the job record. Click 'Submit' to continue</p>
<p>4. Click on 'Please map your fields'. There you will be able to map all the elements with the fields in your database. You can also set some options for how the data is to be imported. Ensure that all the required fields are mapped. Click the 'Save Button' to save. (A mapping is an association between an XML element in the feed and a field in your database)</p>
<p>5. The feed will be ready to import.</p>
<h3>How to associate the jobs with employer accounts?</h3>
<p>The XML Import tool has four methods for associating jobs with employer accounts</p>
<p>- Insert using the employer's account details provided with the feed. Reject if a user/pass does not authenticate.  (This option assumes that the jobs feed also includes accounts data with the username/password included. It will check the username/password in the feed, and only import the job if the account validates. Perfect for importing from semi-trusted systems)</p>
<p>- Insert using the employer's account, but insert using the <strong>default username</strong> if user/pass do not authenticate (This option assumes that the jobs feed also includes accounts data, but some jobs may not have the correct accounts data. It will authenticate the username/password in the feed, but of it does not validate it will import the job under the default username)</p>
<p>- Insert using the employer's username, create a new account from the account data present in the feed. (This option assumes that the jobs feed also includes accounts data. It will authenticate the account data, and create a new account if it does not exist. Perfect for synchronizing two job boards)</p>
<p>-Always import the jobs under the <strong>default username</strong> (This option assumes that the feed does not contain account data. Perfect for importing from public feeds, such as RSS Feeds)</p>
<h3>What is a Command Field?</h3>
<p>This is an element in your feed which tells the importing tool how to process the job data. It supports three user-configurable commands: Update, Insert or Delete</p>
<p>The command field is optional, and if not specified in the field, it is assumed to be Insert</p>
<h3>What is a GUID?</h3>
<p>GUID stands for Global Unique ID. It is a required piece of data by the importing tool. A GUID must be unique value, not just for your job board, but for every job posting, for every job board across the entire internet. Usually this is the URL to the original job posting.</p>
<h3>What are the validation options for the fields?</h3>
<p>- At present, there are 7 validation options to choose. If a field is not valid for the option selected, then the import tool will skip importing that record and log the error in the error log file. Here are the validation options:</p>
<p>1. No validation needed</p>
<p>2. Not blank - The field must not be blank</p>
<p>3. Alphanumeric - Only characters 0-9, A-Z, À-ÿ are allowed. Periods, dash and @ characters are allowed as well. Unicode not supported.</p>
<p>4. Numeric - Positive or negative number with the sign optional, thousands separated with commas, optional decimal point (period).</p>
<p>5. Email - Validates to make sure it's a well formatted email address</p>
<p>6. Currency - It does not validate the symbol or currency code, just ensures that a valid number can be extracted from it. </p>
<p>7. URL - Validates to make sure it's a well formatted URL</p>
<p><em>Note: Any field that does not fit in the database field will be automatically truncated. This means that text fields are limited to 255 characters max. Textarea and HTML editor fields are not limited.</em></p>
<p><em>Note 2: The import tool will automatically convert any XML entities to their normal characters (eg. &lt; will be converted to a less than sign &lt;). The import tool will also convert all UTF-8 characters to the job board's internal character handling system. It will also sanitize the input by removing any malicious HTML from the data such as javascript. You can check the <strong>'Allow limited HTML'</strong> option so that not all of the HTML is not stripped from the field.</em></p>
<h3>Can I ignore some fields and replace with my own data?</h3>
<p>Yes, check the 'Ignore field &amp; Replace with' checkbox, then enter the value to replace with.</p>
<h3>How are Categories Imported?</h3>
<p>First, the system will take the imported value and search the categories to see if it exists on the system. If the imported value does not exist on the system, you have one of the four options:</p>
<p>1. 'Add the value as a new category, under the category of' - The imported value will be added as a new category, under the selected category</p>
<p>2. 'Attempt to match the category name with text from...' - This is the smart way of matching a job post to a category, if the job post does not contain category information. How it works is like this: It grabs the text from the selected field. It splits the text in to words, using only words longer than 3 letters The word frequency is then counted, and sorted. Then the most frequent words are searched in the category table to try to get the category_id - it does the search for 5 most frequent words before giving up.</p>
<p>3. Throw an error and skip the whole record. This will be logged in the import log file.</p>
<p>4. Don't do anything, import anyway - Perfect option if the category is not required</p>
<h3>How are fields with options imported?</h3>
<p>Options for Checkboxes, Radio Buttons, Selects and Multiple Selects are imported using their whole values. In the system, these fields come in pairs. Eg. AU is the <strong>code</strong> and Australia is the <strong>code description</strong>.</p>
<p>The system will first check if the imported option exists in the database. If the imported option does not exist, you have one of the four options</p>
<p>1. Add the value as a new option, using the first three letters of the imported option as the code.</p>
<p>2. Add the value as an option, using a selected field as the Code. This means that the imported field will be used for the <strong>code description</strong> as normal, but instead of using the first three letters for the code, the system will read the code from an additional field which is in the feed.</p>
<p>3. Throw an error and skip the whole record. The error will be logged to the import log error file</p>
<p>4. Don't do anything, import anyway</p>
<h3>Do you have a sample XML file for the import tool?</h3>
<p>Certainly. Here is a <a href="jamit.xml" target="_blank">sample XML file</a>. (The file will open in a new window. To view the structure you will need to view the source)</p>
<p>Additionally, you can setup the XML Export tool to export jobs using the structure of the above XML File</p>
<p>Here is how to setup the export tool:<br>
1. Go to Admin->XML Export, click the 'Create a New Schema' button at the top<br>
2. Enter a name and description, click Save<br>
3. Click on 'Configure XML Structure'<br>
4. Click on the 'Import structure from CSV' link<br>
5. Paste in the following data, and click Save<br>

<pre>
element_id,element_name,is_cdata,parent_element_id,form_id,field_id,schema_id,attributes,static_data,is_pivot,description,fieldcondition,is_boolean,qualify_codes,qualify_cats,truncate,strip_tags,is_mandatory,static_mod,multi_fields,has_child,comment
2399,country,N,2368,1,,5,,,N,,,N,Y,Y,0,N,N,F,1,N,
2398,region,N,2368,1,,5,,,N,,,N,Y,Y,0,N,N,F,1,N,"Region can be a state, province, continent or other geo-political area"
2397,place,N,2368,1,,5,,,N,,,N,Y,Y,0,N,N,F,1,N,This field may be best for when the location is a type-in field
2396,image_link,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2395,description,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,"Required, this is the main description of the job post"
2394,experience,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2393,immigration_status,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2392,benefits,N,2388,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2391,to,N,2388,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2390,from,N,2388,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2389,currency,N,2388,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2388,salary,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,4,Y,
2387,posted_by,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,"Required, the name of the poster"
2386,postalcode,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2385,skills,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2384,duration,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2383,start,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2382,www_url,N,2372,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2381,tel,N,2372,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2379,country,N,2372,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2378,region,N,2372,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2377,place,N,2372,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2376,address,N,2372,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2375,company,N,2372,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,Map to Employer_Company Name
2374,lastName,N,2372,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,Map to Employer Last Name
2373,firstName,N,2372,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,Map to Employer_ First Name
2372,contact,N,2352,1,,5,,,N,,,N,N,N,0,N,N,F,1,Y,
2371,industry,N,2369,1,,5,,,N,,,N,Y,Y,0,N,N,F,1,N,
2370,type,N,2369,1,,5,,,N,,,N,Y,Y,0,N,N,F,1,N,
2369,categories,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,Y,
2368,location,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,Y,Required
2367,email,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,"Required, the email address of the poster"
2366,title,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,"Required, the main title of the job posting"
2365,www_url,N,193,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2364,tel,N,193,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2363,country,N,193,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2362,province,N,193,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2361,town,N,193,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2360,address,N,193,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2359,company,N,193,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2358,lastName,N,193,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2357,firstName,N,193,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2355,accountEmail,N,2352,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,Map to Employer_Email
2402,latitude,N,2368,1,,5,,,N,,,N,Y,Y,0,N,N,F,1,,
2354,password,N,2352,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,Map to Employer_Password (md5 hash). Optional
2353,username,N,2352,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,Map to Employer_Username
2352,account,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,Y,Employers account data.
2351,link,N,2344,1,,5,,%LINK%,N,,,N,N,N,0,N,N,F,1,N,
2350,pubDate,N,2344,1,,5,,%DATE_RFC%,N,,,N,N,N,0,N,N,F,1,N,
2349,application_url,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2348,guid,N,2344,1,,5,,%LINK%,N,,,N,N,N,0,N,N,F,1,N,
2347,refID,N,2344,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,Reference id
2346,days_to_advertise,N,2344,1,,5,,30,N,,,N,N,N,0,N,N,F,1,N,
2345,command,N,2344,1,,5,,INSERT,N,,,N,N,N,0,N,N,F,1,N,
2344,job,N,2322,1,,5,,,Y,,,N,N,N,0,N,N,F,1,Y,
2343,height,N,2338,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2342,width,N,2338,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2341,url,N,2338,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2340,title,N,2338,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2339,link,N,2338,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2337,region,N,2331,1,,5,, ,N,,,N,N,N,0,N,N,F,1,N,"The region can be a state, privince or continent. Change to static when exporting and type-in the value"
2336,country,N,2331,1,,5,, ,N,,,N,N,N,0,N,N,F,1,N,Edit and type-in the value when setting up the feed
2335,skill,N,2331,1,,5,, ,N,,,N,N,N,0,N,N,F,1,N,Edit and type-in the value when setting up the feed
2334,jobs_type,N,2331,1,,5,, ,N,,,N,N,N,0,N,N,F,1,N,Edit and type-in the value when setting up the feed
2333,function,N,2369,1,,5,,,N,,,N,Y,Y,0,N,N,F,1,N,
2332,industry,N,2331,1,,5,, ,N,,,N,N,N,0,N,N,F,1,N,Edit and type-in the value when setting up the feed
2331,targetNiche,N,2322,1,,5,,,N,,,N,N,N,0,N,N,F,1,Y,
2330,webMaster,N,2322,1,,5,,%SITE_CONTACT_EMAIL%,N,,,N,N,N,0,N,N,F,1,N,
2329,managingEditor,N,2322,1,,5,,%SITE_CONTACT_EMAIL%,N,,,N,N,N,0,N,N,F,1,N,
2328,generator,N,2322,1,,5,,%SITE_NAME% - Jamit Job Board,N,,,N,N,N,0,N,N,F,1,N,
2327,lastBuildDate,N,2322,1,,5,,%FEED_DATE%,N,,,N,N,N,0,N,N,F,1,N,
2326,pubDate,N,2322,1,,5,,%FEED_DATE%,N,,,N,N,N,0,N,N,F,1,N,
2325,description,N,2322,1,,5,,%SITE_DESCRIPTION%,N,,,N,N,N,0,N,N,F,1,N,
2323,title,N,2322,1,,5,,%SITE_NAME%,N,,,N,N,N,0,N,N,F,1,N,
2324,link,N,2322,1,,5,,%BASE_HTTP_PATH%,N,,,N,N,N,0,N,N,F,1,N,
2322,jobsFeed,N,2320,1,,5,,,N,,,N,N,N,0,N,N,F,1,Y,
2321,jamitKey,N,2320,1,,5,,,N,,,N,N,N,0,N,N,F,1,N,
2320,jamit,N,0,1,,5,"version=""1.0"" encoding=""utf-8""",,N,,,N,N,N,0,N,N,F,1,Y,
2400,post_mode,N,2344,1,,5,,,N,,,N,Y,Y,0,N,N,F,1,N,"map to the Post Mode, eg. data free, premium or standard"
2401,lang,N,2322,1,,5,,EN,N,,,N,N,N,0,N,N,F,1,N,
2403,longitude,N,2368,1,,5,,,N,,,N,Y,Y,0,N,N,F,1,,
</pre>
<p>&nbsp;</p>
<h3>Other tutorials, docs & articles</h3>
<a href="http://www.jamit.com/jamit-xml.htm">Jamit XML Spec</a> - Explains the Jamit XML spec in detail and provides an example<br>
<a href="http://www.jamit.com/tutorials/xml-tutorial.htm">XML Import Tutorial</a> - How to import from another job board (Advanced)<br>
<a href="https://www.jamit.com.au/support/index.php?_m=knowledgebase&_a=viewarticle&kbarticleid=191">Extending Functionality</a> - How to customize the functionality XML Importer (Advanced)<br>
<a href="https://forum.jamit.com">Forums</a> - There is a board dedicated to XML Imports / XML Exports<br>
<p>&nbsp;</p>

<?php
JB_admin_footer();


?>
