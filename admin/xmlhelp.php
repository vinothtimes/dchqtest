<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> XML Help');

?>
<b>[XML Export]</b> 
	<span style="background-color: <?php if ($_REQUEST['export']=='1') { echo '#FFFFCC'; } else { echo "#F2F2F2"; } ?>; border-style:outset; padding:5px; "><a href="xmlfeed.php?export=1">XML Feeds</a></span> <span style="background-color:#F2F2F2; border-style:outset; padding: 5px;"><a href="xmlschema.php">XML Schemas</a></span> <span style="background-color:#FFFFCC; border-style:outset; padding: 5px;"><a href="xmlhelp.php">XML Help</a></span>
	<hr>

<p><b><font face="Arial">What are XML feeds?</font></b></p>
<p><font face="Arial" size="2">XML Feeds are used to export your job board data 
to other websites or databases, such as Google Base, SimplyHired.com or 
Indeed.com - effectively allowing you to do such things as cross-posting jobs to 
other job boards.</font></p>
<p><font face="Arial" size="2">You can create your own private XML feeds that 
can be used integrate your job board with other systems. Currently the XML 
feature is only able to export Jobs, but it will be extended in the near future 
to include other record types.</font></p>
<p><b><font face="Arial">What are XML Schemas</font></b></p>
<p><font face="Arial" size="2">The XML Export feature in the job board supports 
exporting in different XML formats. These formats are called 'Schemas' and each 
schema can be specifically configured to suit your requirements from the 'XML 
Schemas' menu. </font></p>
<p><font face="Arial" size="2">The job board comes pre-loaded with the following 
schemas</font></p>
<p><font face="Arial" size="2">&nbsp;Google Base, Indeed.com, RSS, 
SimplyHired.com</font></p>
<p><font face="Arial" size="2">You can also build your own schemas form the 'XML 
Schemas'&nbsp; menu. This ensures that the XML export feature is able to deal 
with many new and exciting feed formats in the future.</font></p>
<p><b><font face="Arial">XML Feed questions.</font></b></p>
<p><font face="Arial" size="2">1. How do I create a new feed?</font></p>
<p><font face="Arial" size="2">Click the 'Create a New Feed' button and then 
select your schema to base your feed on. Fill in the remainder of the fields.</font></p>
<p><font face="Arial" size="2">The most important step is to map your XML 
elements to your database fields. </font></p>
<p><font face="Arial" size="2">2. What if I want to restrict the feed to allow 
only some people to access it?</font></p>
<p><font face="Arial" size="2">You can modify IP Address Allow list for each XML 
feed. Input your IP addresses there. You can also make your feed private and 
require a key to be submitted with it.</font></p>
<p><b><font face="Arial">XML Schema questions.</font></b></p>
<p><font face="Arial" size="2"><b>1. What is an element?</b></font></p>
<p><font face="Arial" size="2">An element in an XML document is just like any 
tag in a HTML document. For example &lt;b&gt;&lt;/b&gt; is an element in a HTML document, so 
too in an XML document. The only difference is you can invent your own element 
names in XML documents, and have something like &lt;funky&gt;&lt;/funky&gt;. </font></p>
<p><font face="Arial" size="2"><b>2. What is the parent element setting?</b></font></p>
<p><font face="Arial" size="2">You can use the 'parent element' setting to place 
elements in to a particular level on the tree.</font></p>
<p><font face="Arial" size="2">In all XML documents, Elements are semi-ordered 
in to a tree. Each element has a parent, except the top most element who's 
parent is the root. So for example, you can have something like:</font></p>
<p><font face="Arial" size="2">&lt;feed&gt;<br>
&nbsp;&nbsp;&nbsp; &lt;job&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &lt;location&gt;&lt;/location&gt;<br>
&nbsp;&nbsp;&nbsp; &lt;/job&gt;<br>
&lt;/feed&gt;</font></p>
<p><font face="Arial" size="2">Where Job is the parent of location, feed is the 
parent of job.&nbsp; </font></p>
<p>&nbsp;</p>
<p><font face="Arial" size="2"><b>3. What is the 'Attributes' setting?</b></font></p>
<p><font face="Arial" size="2">Some elements may have some extra attributes, for 
example a color=red attribute, placed inside the tag. In general, there is no 
need to fill in this field. The preferred method is to place attributes between 
the opening &lt;&gt; and closing &lt;/&gt; tags.</font></p>
<p><font face="Arial" size="2"><b>4. What is a 'Pivot'?</b></font></p>
<p><font face="Arial" size="2">The jamit job board defines a pivot element the 
one that's going to be repeated when generating the feed. While reading the data 
from the database, the job board will export the data in to the pivot element 
(and all it's sub-elements) and then if there is more data it will iterate the 
pivot until all data is exported.</font></p>
<p><font face="Arial" size="2">There should only be 1 pivot element per schema.</font></p>
<p><font face="Arial" size="2"><b>5. What is CDATA?</b></font></p>
<p><font face="Arial" size="2">Some feed specs such as Indeed.com insist that 
some data values are exported as CDATA (raw character data). If this is the 
case, you should turn on the setting for this element to Yes.</font></p>
<p><font face="Arial" size="2"><b>6. What is 'Is Mandatory'?</b></font></p>
<p><font face="Arial" size="2">Most XML feeds require you to have at least most 
of the data filled before they can be accepted. This setting does not do 
anything except remind the Administrator that the fields marked with a * are 
monitory and must be present on the feed.</font></p>
<p><font face="Arial" size="2"><b>7. What are Multi-fields?</b></font></p>
<p><font face="Arial" size="2">Sometimes you may have 2 or 3 or more fields in 
your job board, but the XML feed requires you to export them as one field. For 
example, you may have Address, City and State all separate, and you want to 
export this as one field called 'Location'.</font></p>
<p><font face="Arial" size="2">You can use the multi-fields feature to specify 
how many fields you want to merge in to this attribute. (Very useful with 
Googles location attribute)</font></p>
<p><font face="Arial" size="2"><b>8. What is Static Data?</b></font></p>
<p><font face="Arial" size="2">Static data is a special form of export data 
which does not come form the database, or is partially generated form the 
database.</font></p>
<p><font face="Arial" size="2">You can put any kind of text in this setting to 
be included in the export, or you can enter the special variables.</font></p>
<p><font face="Arial" size="2"><b>9. What are the Static Data options?</b></font></p>
<p><font face="Arial" size="2">The static data options control how the static 
data is to be inserted in to the feed. You can also mix static data with data 
exported from the database, for example you can append static data to the 
exported data.</font></p>
<p><font face="Arial" size="2"><b>10. What is the 'Is Boolean' setting?</b></font></p>
<p><font face="Arial" size="2">Some more complicated XML specs (SimplyHired.com) 
insist that some data be exported as 'true' or 'false'. You can check to Yes if 
this is the case for this element. When filling out the XML feed form, you will 
be asked to specify which value should return true.</font></p>
<p><font face="Arial" size="2"><b>11. What's with the Radio-buttons, Checkboxes 
and Selects?</b></font></p>
<p><font face="Arial" size="2">Normally, these fields are stored as codes in the 
database. You can choose to have the export engine to convert the codes to their 
full names if you choose yes.</font></p>
<p><font face="Arial" size="2"><b>12. What's with the 'Category Fields?'</b></font></p>
<p><font face="Arial" size="2">Same as above, categories are stored as codes in 
the database. Selecting to 'Yes' will export the categories as full names.</font></p>
<p><font face="Arial" size="2"><b>13. What does truncate do?</b></font></p>
<p><font face="Arial" size="2">This will cut the exported data after a number of 
characters. The truncation is HTML safe, which means it will count HTML 
entireties such as &amp;nbsp; as one character. </font></p>
<p><font face="Arial" size="2"><b>14. What does 'Strip Tags' do?</b></font></p>
<p><font face="Arial" size="2">This will remove any HTML data if you allow HTML 
input. Highly recommended if you are exporting data form a HTML Editor type 
field.</font></p>
<hr>
<p><font face="Arial" size="2">The following section details some notes that 
were observed during testing of the feeds. Hope you may find these useful.</font></p>
<p><b><font face="Arial">Some notes for importing in to Google Base</font></b></p>
<p><font face="Arial">1. You will need to download your file to your disk before 
uploading it to Google Base. Otherwise, you can try to set up an FTP upload - 
the corresponding xml file is always cached in your cache/ directory.</font></p>
<p><font face="Arial">2. You will need to make sure that the exported data is 
100% correct and your fields are matched correctly. Google is mostly fussy about 
unrecognized locations and other data that it thinks may be wrong. You may need 
to add additional data to the location field when exporting. For example, for 
our Korean based website, we appended an extra 'Korea' string to the end of the 
location field, so that all location data is exported with the Korea string You 
may do this using the 'Static Data' feature in the XML Schema setting, and set 
it so that the extra value.</font></p>
<p><font face="Arial">3. Google's description attribute has a limit of 10,000 
characters or so. Keep in mind that other fields may have some sort of 
restriction too. See here for more detail:
<a href="http://www.google.com/base/jobs.html">
http://www.google.com/base/jobs.html</a> </font></p>
<hr>
<p style="background-color:#FFFFCC">TIP: Do you have too many records in your feed? Did you know that you can break down your feeds in to smaller feeds by adding an offest parameter? An offest parameter can be used to skip past records and fetch further records, eg http://example.com/jb-get-xml.php?feed_id=11&offset=2000 will skip the first 2000 records</p>
<p>&nbsp;</p>


<?php

JB_admin_footer();

?>
