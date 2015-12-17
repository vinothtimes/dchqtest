<?php

$fn = "../../../config.php";
$time = filemtime($fn);
// ob_start('ob_gzhandler');
header('Cache-Control: public, must-revalidate');
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $time).' GMT');
header("Content-type: text/javascript");


// Getting headers sent by the client.
$headers = apache_request_headers(); 

// Checking if the client is validating his cache and if it is current.
if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($fn))) {
   // Client's cache IS current, so we just respond '304 Not Modified'.
   header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 304);
   die();
} else {
   // Script not cached or cache outdated, we respond '200 OK' and output the image.
   header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 200);
   
  // print file_get_contents($fn);
}


require ("../../../config.php");

?>

// *****************************************************************************
//      Simple Calendar Widget - Cross-Browser Javascript pop-up calendar.
//
//   Copyright (C) 2005-2006  Anthony Garrett
//
//   This library is free software; you can redistribute it and/or
//   modify it under the terms of the GNU Lesser General Public
//   License as published by the Free Software Foundation; either
//   version 2.1 of the License, or (at your option) any later version.
//
//   This library is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//   Lesser General Public License for more details.
//
//   You should have received a copy of the GNU Lesser General Public
//   License along with this library; if not, it is available at
//   the GNU web site (http://www.gnu.org/) or by writing to the
//   Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
//   Boston, MA  02110-1301  USA
//
// *****************************************************************************
//
// Contact:   Sorry, I can't offer support for this but if you find a problem
//            (or just want to tell me how useful you find it), please send
//            me an email at scwfeedback@tarrget.info (Note the two Rs in
//            tarrget).  I will try to fix problems quickly but this is a
//            spare time thing for me.
//
// Credits:   I wrote this from scratch myself but I couldn't have done it
//            without the superb "JavaScript The Definitive Guide" by David
//            Flanagan (Pub. O'Reilly ISBN 0-596-00048-0).  I also recognise
//            a contribution from my experience with PopCalendar 4.1 by
//            Liming(Victor) Weng.
//
// Link back: Please give me credit and link back to my page.  To ensure that
//            search engines give my page a higher ranking you can add the
//            following HTML to any indexed page on your web site:
//
//            <A HREF="http://www.tarrget.info/calendar/scw.htm">
//              Simple Calendar Widget by Anthony Garrett
//            </A>
//
// Features:  Easily customised
//                  (output date format, colours, language, year range and
//                   week start day)
//            Accepts a date as input
//                  (see comments below for formats).
//            Cross-browser code tested against;
//                  Internet Explorer 6.0.28     Mozilla  1.7.1
//                  Opera             7.52+      Firefox  0.9.1+
//                  Konqueror         3.4.0      Flock    0.4.9
//
// How to add the Calendar to your page:
//            This script needs to be defined for your page so, immediately
//            after the BODY tag add the following line;
//
//                  <script type='Text/JavaScript' src='scw.js'></script>
//
// How to use the Calendar once it is defined for your page:
//            Simply choose an event to trigger the calendar (like an onClick
//            or an onMouseOver) and an element to work on (for the calendar
//            to take its initial date from and write its output date to) then
//            write it like this;

//                  <<event>>="scwShow(<<element>>,this);"
//
//            e.g. onClick="scwShow(document.getElementById('myElement'),this);"
//            or   onMouseOver="scwShow(this,this);"
//
//            You can also disable days of the week by adding arguments to the
//            call to scwShow.  The values should be Sunday = 0 through to
//            Saturday = 6.  A call to scwShow with Friday and Monday disabled
//            would look something like this;
//
//                  scwShow(<<element>>,this,5,1);
//
//            Finally you can use the following technique to run a function
//            when the calendar closes:
//
//                  scwNextAction=<<function>>.runsAfterSCW(this,<<arguments>>);
//                  scwShow(<<element>>,this <<,optional arguments above>>);
//
//            Where <<function>> is a function defined on the calling page
//            and <<arguments>> is the list of arguments being passed to that
//            function.
//
// *****************************************************************************
//
// Version   Date        By               Description
// =======   ====        ===============  ===========
//   1.0     2004-08-02  Anthony Garrett  Initial release
//   1.1     2005-10-17  Anthony Garrett  Added requested feature to allow
//                                        a click anywhere on the calling page
//                                        to cancel the calendar.
//                                        Added "How to" paragraphs to
//                                        documentation (above).
//                                        Corrected bug that misread numeric
//                                        seed months as one less than entered.
//   1.2     2005-10-26  Anthony Garrett  Allow start of week to be any day.
//   2.0     2005-11-03  Anthony Garrett  Add an IFRAME behind the calendar to
//                                        deal with IE SELECT boxes.
//                                        Renamed all exposed variables and
//                                        functions but kept showCal as entry
//                                        point for backward compatibility.
//                                        Added classes to all HTML elements
//                                        and moved all style attributes to
//                                        inline stlye sheet in customisation
//                                        section.
//   2.1     2005-11-10  Anthony Garrett  Fixed a bug which causes the calendar
//                                        not to display in Firefox when the
//                                        event trigger element's parent was
//                                        not the data element's parent.
//                                        NOTE: This has forced me to add a
//                                              second interface parameter!
//   2.2     2005-11-17  Anthony Garrett  Added input date validation.
//                                        Added input date highlighting (thanks
//                                        to Brad Allan for that suggestion).
//                                        Added optional strict date processing
//                                        (e.g. making 31-Sep an error instead
//                                        of returning 1-Oct). Improved the
//                                        calendar positioning so that CSS
//                                        positioning using DIVs is handled
//                                        correctly.
//   2.3     2005-11-23  Anthony Garrett  Corrected input validation for US
//                                        and other date formats.  Added
//                                        examples for US date processing.
//   2.4     2005-12-10  Anthony Garrett  Added feature to allow disabling
//                                        of specific days of the week, dates
//                                        and date ranges.  Made it optional
//                                        that clicking on the calendar itself
//                                        causes the pop-up to be hidden.
//                                        Thanks to Felix Joussein for the
//                                        feedback.
//   2.41    2005-12-12  Anthony Garrett  Small fix for "Today" display when
//                                        there are no disabled dates.
//   2.42    2005-12-15  Anthony Garrett  Fixed bug where calendar exceeded end
//                                        of range by one month.
//   2.5     2005-12-23  Anthony Garrett  Implemented optional disabling of
//                                        displayed out of range days which
//                                        can occur at the start of the very
//                                        first month and end of the very last.
//                                        Tidied up cursor on calendar
//                                        (thanks to Lou LaRocca for that
//                                        suggestion and others under
//                                        consideration).
//                                        Replaced all browser sniffing with
//                                        more reliable techniques.
//                                        Tightened up and improved access
//                                        to month display with week start
//                                        days other than Zero (Sunday).
//   2.6     2006-01-04  Anthony Garrett  Fixed bug where "disabled" today's
//                                        date could still be used.
//                                        Modified to work with Konqueror.
//                                        Simplified calendar positioning (and
//                                        made it more robust) - Thanks to
//                                        Brad Rubenstein for that suggestion.
//   2.61    2006-01-11 Anthony Garrett   Fixed two digit year entry where year
//                                        parses to a single digit integer.
//   2.62    2006-01-14 Anthony Garrett   Work around Internet Explorer's bug
//                                        where an IFRAME's default src
//                                        attribute is not secure causing the
//                                        'Mixed content' alert when using SSL.
//                                        Thanks to Mark Chapa for reporting
//                                        this problem.
//   2.7     2006-01-18 Anthony Garrett   Simplified date formatting - With
//                                        thanks to Wa aX for sending me
//                                        enhancements that form the basis of
//                                        this change.
//                                        Added optional day disabling
//                                        parameters to scwShow. Thanks (again)
//                                        to Mark Chapa for sharing his
//                                        requirement.
//   2.8     2006-01-31 Anthony Garrett   Added calendar drag. Thanks to Sunil
//                                        Paliwal for the feature request.
//   2.9     2006-02-01 Anthony Garrett   Enhanced disabled day display to
//                                        differentiate day types (In/Out of
//                                        month, weekend days etc) as for
//                                        enabled days.
//                                        Re-organised functions and variables
//                                        to expose only necessary code.
//   3.0     2006-02-10 Anthony Garrett   Allowed more than one delimiter to
//                                        separate date elements.  Thanks to
//                                        Rick Haggart for reporting the problem
//                                        with the format "MMM DD, YYYY".
//   3.1     2006-02-15 Anthony Garrett   Added ability to run a function on
//                                        closing the calendar.  The function can
//                                        take parameters and give a return.
//                                        Thanks to Lou LaRocca for the feedback.
//   3.11    2006-02-20 Anthony Garrett   Prevent "after" function set for one
//                                        element from trigerring for another.
//   3.2     2006-02-25 Anthony Garrett   Added optional week numbering.
//   3.21    2006-02-27 Anthony Garrett   Fixed day disabling that was broken by
//                                        week numbering.
//   3.22    2006-03-06 Anthony Garrett   Fixed bug that prevented a form reset
//                                        in Firefox. Thanks to Stephan Jager
//                                        for the feedback.
//   3.23    2006-03-08 Anthony Garrett   Fixed bug that cause JS Error in IE
//                                        with Strict DTD and advanced CSS.
//                                        Thanks to Thierry Blind for the
//                                        feedback.
//   3.3     2006-03-10 Anthony Garrett   Modified to work with IE7. Added
//                                        dynamic language setting for
//                                        multilingual pages. Thanks to Rafael
//                                        Pirolla for the idea.
//   3.31    2006-03-20 Anthony Garrett   Enhanced the dynamic language setting
//                                        for multilingual pages.
//   3.4     2006-03-27 Anthony Garrett   Added optional alert disabling. Thanks
//                                        to Peter Foti for his suggestion,
//                                        reinforced by Gopi Reddy.
//   3.5     2006-04-04 Anthony Garrett   Allowed easy extraction of CSS (making
//                                        it straight forward to implement
//                                        themes) while simplifying the script.
//                                        Thanks to Jason Novotny for this
//                                        suggestion.
//                                        Note: This version replaces the
//                                              colour-control variables with
//                                              CSS classes.  Please be aware
//                                              of this change if you upgrade
//                                              the script for a page that sets
//                                              these variables.
//   3.51    2006-09-09 Anthony Garrett   Work around Safari's problem with event
//                                        bubbling.  Thanks again to Jason
//                                        Novotny for reporting this problem.
//                                        Deep gratitude to Sam Bayne and the
//                                        guys at North Seattle Community College
//                                        for the help with access to Safari.
//                                        Also fixed small IFRAME size error,
//                                        Thanks to Thierry Blind for reporting
//                                        that bug.
// *****************************************************************************

// ************************************
// Start of Simple Calendar Widget Code
// ************************************

// This date is used throughout to determine today's date.

    var scwDateNow = new Date(Date.parse(new Date().toDateString()));

//******************************************************************************
//------------------------------------------------------------------------------
// Customisation section
//------------------------------------------------------------------------------
//******************************************************************************

    // Set the bounds for the calendar here...
    // If you want the year to roll forward you can use something like this...
    //      var scwBaseYear = scwDateNow.getFullYear()-5;
    // alternatively, hard code a date like this...
    //      var scwBaseYear = 1990;

    var scwBaseYear        = scwDateNow.getFullYear()-10;

    // How many years do want to be valid and to show in the drop-down list?

    var scwDropDownYears   = 20;

    // All language-dependent changes can be made here...

    // If you wish to work in a single language (other than English) then
    // just replace the English (in the function scwSetLanguage below) with
    // your own text.

    // Using multiple languages:
    // In order to keep this script to a resonable size I have not included
    // languages here.  You can set language fields in a function that you
    // should call  scwSetLanguage  the script will use your languages.
    // I have included all the translations that have been sent to me in
    // such a function on the demonstration page.

    var scwLanguage;

    function scwSetDefaultLanguage()
        {try
            {scwSetLanguage();}
         catch (exception)
            {// All possible languages
             scwToday               = '<?php echo JB_htmlent_to_hex($label['scw_today']); ?>';
             scwDrag                = '<?php echo JB_htmlent_to_hex($label['scw_drag']); ?>';
             scwArrMonthNames       = ['<?php echo JB_htmlent_to_hex($label['scw_jan']); ?>','<?php echo JB_htmlent_to_hex($label['scw_feb']); ?>','<?php echo JB_htmlent_to_hex($label['scw_mar']); ?>','<?php echo JB_htmlent_to_hex($label['scw_apr']); ?>','<?php echo JB_htmlent_to_hex($label['scw_may']); ?>','<?php echo JB_htmlent_to_hex($label['scw_jun']); ?>',
                                       '<?php echo JB_htmlent_to_hex($label['scw_jul']); ?>','<?php echo JB_htmlent_to_hex($label['scw_aug']); ?>','<?php echo JB_htmlent_to_hex($label['scw_sep']); ?>','<?php echo JB_htmlent_to_hex($label['scw_oct']); ?>','<?php echo JB_htmlent_to_hex($label['scw_now']); ?>','<?php echo JB_htmlent_to_hex($label['scw_dec']); ?>'];
             scwArrWeekInits        = ['<?php echo JB_htmlent_to_hex($label['scw_sun0']); ?>','<?php echo JB_htmlent_to_hex($label['scw_mon1']); ?>','<?php echo JB_htmlent_to_hex($label['scw_tue2']); ?>','<?php echo JB_htmlent_to_hex($label['scw_wed3']); ?>','<?php echo JB_htmlent_to_hex($label['scw_thu4']); ?>','<?php echo JB_htmlent_to_hex($label['scw_fri5']); ?>','<?php echo JB_htmlent_to_hex($label['scw_sat6']); ?>'];
             scwInvalidDateMsg      = '<?php echo JB_htmlent_to_hex($label['scw_inv']); ?>';
             scwOutOfRangeMsg       = '<?php echo JB_htmlent_to_hex($label['scw_range']); ?>';
             scwDoesNotExistMsg     = '<?php echo JB_htmlent_to_hex($label['scw_exist']); ?>';
             scwInvalidAlert        = ['<?php echo JB_htmlent_to_hex($label['scw_ign']); ?>','<?php echo JB_htmlent_to_hex($label['scw_ign2']); ?>'];
             scwDateDisablingError  = ['<?php echo JB_htmlent_to_hex($label['scw_err1']); ?>',' <?php echo JB_htmlent_to_hex($label['scw_err1obj']); ?>'];
             scwRangeDisablingError = ['<?php echo JB_htmlent_to_hex($label['scw_err2']); ?> ',
                                       ' <?php echo JB_htmlent_to_hex($label['scw_err2elem']); ?>'];
            }
        }
 
    // Note:  Always start the scwArrWeekInits array with your string for
    //        Sunday whatever scwWeekStart (below) is set to.

    // scwWeekStart determines the start of the week in the display
    // Set it to: 0 (Zero) for Sunday, 1 (One) for Monday etc..

    var scwWeekStart       =    1;

    // The week start day for the display is taken as the week start
    // for week numbering.  This ensures that only one week number
    // applies to one line of the calendar table.
    // [ISO 8601 begins the week with Day 1 = Monday.]

    // If you want to see week numbering on the calendar, set
    // this to true.  If not, false.

    var scwWeekNumberDisplay    = false;

    // Week numbering rules are generally based on a day in the week
    // that determines the first week of the year.  ISO 8601 uses
    // Thursday (day four when Sunday is day zero).  You can alter
    // the base day here.

    // See http://www.cl.cam.ac.uk/~mgk25/iso-time.html for more information

    var scwWeekNumberBaseDay    = 4;

    // Each of the calendar's alert message types can be disabled
    // independently here.

    var scwShowInvalidDateMsg       = false,
        scwShowOutOfRangeMsg        = true,
        scwShowDoesNotExistMsg      = true,
        scwShowInvalidAlert         = false,
        scwShowDateDisablingError   = true,
        scwShowRangeDisablingError  = true;

    // Set the allowed input date delimiters here...
    // E.g. To set the rising slash, hyphen, full-stop (aka stop or point),
    //      comma and space as delimiters use
    //              var scwArrDelimiters   = ['/','-','.',',',' '];

    var scwArrDelimiters   = ['/','-','.',',',' '];

    // Set the format for the displayed 'Today' date and for the output
    // date here.
    //
    // The format is described using delimiters of your choice (as set
    // in scwArrDelimiters above) and case insensitive letters D, M and Y.
    //
    // Definition               Returns
    // ----------               -------
    // D            date in the month without zero filling
    // DD           date in the month left zero filled
    // M            month number without zero filling
    // MM           month number left zero filled
    // MMM          month string from scwArrMonthNames
    // YY           year number in two digits
    // YYYY         year number in four digits

	<?php

	if (!defined('JB_SCW_DATE_FORMAT')) {
		define ('JB_SCW_DATE_FORMAT', 'YYYY-MM-DD');
	}

	if (!defined('JB_SCW_INPUT_SEQ')) {
		define ('JB_SCW_DATE_FORMAT', 'YMD');
	}
	
	
	?>

    // Displayed "Today" date format

    var scwDateDisplayFormat = '<?php echo JB_SCW_DATE_FORMAT; ?>';     // e.g. 'MMM-DD-YYYY' for the US

    // Output date format

    var scwDateOutputFormat  = 'YYYY-mm-dd'; // e.g. 'MMM-DD-YYYY' for the US
	// Note: please do not change above setting, it is used internally by the job board!

    // The input date is fully parsed so a format is not required,
    // but there is no way to differentiate the sequence reliably.
    //
    // e.g. Is 05/08/03     5th August 2003,
    //                      8th May    2003 or even
    //                      3rd August 2005?
    //
    // So, you have to state how the code should interpret input dates.
    //
    // The sequence should always contain one D, one M and one Y only,
    // in any order.

    var scwDateInputSequence = '<?php echo JB_SCW_INPUT_SEQ; ?>';           // e.g. 'MDY' for the US

    // Note: Because the user may select a date then trigger the
    //       calendar again to select another, it is necessary to
    //       have the input date sequence in the same order as the
    //       output display format.  To allow the flexibility of having
    //       a full input date and a partial (e.g. only Month and Year)
    //       output, the input sequence is set separately.
    //
    //       The same reason determines that the delimiters used should
    //       be in scwArrDelimiters.

    // scwZindex controls how the pop-up calendar interacts with the rest
    // of the page.  It is usually adequate to leave it as 1 (One) but I
    // have made it available here to help anyone who needs to alter the
    // level in order to ensure that the calendar displays correctly in
    // relation to all other elements on the page.

    var scwZindex          = 1;

    // Personally I like the fact that entering 31-Sep-2005 displays
    // 1-Oct-2005, however you may want that to be an error.  If so,
    // set scwBlnStrict = true.  That will cause an error message to
    // display and the selected month is displayed without a selected
    // day. Thanks to Brad Allan for his feedback prompting this feature.

    var scwBlnStrict       = false;

    // If you wish to disable any displayed day, e.g. Every Monday,
    // you can do it by setting the following array.  The array elements
    // match the displayed cells.
    //
    // You could put something like the following in your calling page
    // to disable all weekend days;
    //
    //  for (var i=0;i<scwEnabledDay.length;i++)
    //      {if (i%7%6==0) scwEnabledDay[i] = false;}
    //
    // The above approach will allow you to disable days of the week
    // for the whole of your page easily.  If you need to set different
    // disabled days for a number of date input fields on your page
    // there is an easier way: You can pass additional arguments to
    // scwShow. The syntax is described at the top of this script in
    // the section:
    //    "How to use the Calendar once it is defined for your page:"
    //
    // It is possible to use these two approaches in combination.

    var scwEnabledDay      = [true, true, true, true, true, true, true,
                              true, true, true, true, true, true, true,
                              true, true, true, true, true, true, true,
                              true, true, true, true, true, true, true,
                              true, true, true, true, true, true, true,
                              true, true, true, true, true, true, true];

    // You can disable any specific date (e.g. 24-Jan-2006 or Today) by
    // creating an element of the array scwDisabledDates as a date object
    // with the value you want to disable.  Date ranges can be disabled
    // by placing an array of two values (Start and End) into an element
    // of this array.

    var scwDisabledDates   = new Array();

    // e.g. To disable 10-Dec-2005:
    //          scwDisabledDates[0] = new Date(2005,11,10);
    //
    //      or a range from 2004-Dec-25 to 2005-Jan-01:
    //          scwDisabledDates[1] = [new Date(2004,11,25),new Date(2005,0,1)];
    //
    // Remember that Javascript months are Zero-based.

    // The disabling by date and date range does prevent the current day
    // from being selected.  Disabling days of the week does not so you can set
    // the scwActiveToday value to false to prevent selection.

    var scwActiveToday = true;

    // Dates that are out of the specified range can be displayed at the start
    // of the very first month and end of the very last.  Set
    // scwOutOfRangeDisable to  true  to disable these dates (or  false  to
    // allow their selection).

    var scwOutOfRangeDisable = true;

    // You can allow the calendar to be dragged around the screen by
    // using the setting scwAllowDrag to true.
    // I can't say I recommend it because of the danger of the user
    // forgetting which date field the calendar will update when there
    // are multiple date fields on a page.

    var scwAllowDrag = false;

    // Closing the calendar by clicking on it (rather than elsewhere on the
    // main page) can be inconvenient.  The scwClickToHide boolean value
    // controls this feature.

    var scwClickToHide = false;

    // I have made every effort to isolate the pop-up script from any
    // CSS defined on the main page but if you have anything set that
    // affects the pop-up (or you may want to change the way it looks)
    // then you can address it in the following style sheets.

    document.writeln(
        '<style type="text/css">'                                       +
            '.scw           {padding:1px;vertical-align:middle;}'       +
            'iframe.scw     {position:absolute;z-index:' + scwZindex    +
                            ';top:0px;left:0px;visibility:hidden;'      +
                            'width:1px;height:1px;}'                    +
            'table.scw      {padding:0px;visibility:hidden;'            +
                            'position:absolute;cursor:default;'         +
                            'width:200px;top:0px;left:0px;'             +
                            'z-index:' + (scwZindex+1)                  +
                            ';text-align:center;}'                      +
        '</style>'  );

    // This style sheet can be extracted from the script and edited into regular
    // CSS (by removing all occurrences of + and '). That can be used as the
    // basis for themes. Classes are described in comments within the style
    // sheet.

    document.writeln(
        '<style type="text/css">'                                       +
            '/* IMPORTANT:  The SCW calendar script requires all '      +
            '               the classes defined here.'                  +
            '*/'                                                        +
            'table.scw      {padding:       1px;'                       +
                            'vertical-align:middle;'                    +
                            'border:        ridge 2px;'                 +
                            'font-size:     10pt;'                      +
                            'font-family:   Arial,Helvetica,Sans-Serif;'+
                            'font-weight:   bold;}'                     +
            'td.scwDrag,'                                               +
            'td.scwHead                 {padding:       0px 0px;'       +
                                        'text-align:    center;}'       +
            'td.scwDrag                 {font-size:     8pt;}'          +
            'select.scwHead             {margin:        3px 1px;}'      +
            'input.scwHead              {height:        22px;'          +
                                        'width:         22px;'          +
                                        'vertical-align:middle;'        +
                                        'text-align:    center;'        +
                                        'margin:        2px 1px;'       +
                                        'font-weight:   bold;'          +
                                        'font-size:     10pt;'          +
                                        'font-family:   fixedSys;}'     +
            'td.scwWeekNumberHead,'                                     +
            'td.scwWeek                 {padding:       0px;'           +
                                        'text-align:    center;'        +
                                        'font-weight:   bold;}'         +
            'td.scwFoot,'                                               +
            'td.scwFootHover,'                                          +
            'td.scwFoot:hover,'                                         +
            'td.scwFootDisabled         {padding:       0px;'           +
                                        'text-align:    center;'        +
                                        'font-weight:   normal;}'       +
            'table.scwCells             {text-align:    right;'         +
                                        'font-size:     8pt;'           +
                                        'width:         96%;}'          +
            'td.scwCells,'                  +
            'td.scwCellsHover,'             +
            'td.scwCells:hover,'            +
            'td.scwCellsDisabled,'          +
            'td.scwCellsExMonth,'           +
            'td.scwCellsExMonthHover,'      +
            'td.scwCellsExMonth:hover,'     +
            'td.scwCellsExMonthDisabled,'   +
            'td.scwCellsWeekend,'           +
            'td.scwCellsWeekendHover,'      +
            'td.scwCellsWeekend:hover,'     +
            'td.scwCellsWeekendDisabled,'   +
            'td.scwInputDate,'              +
            'td.scwInputDateHover,'         +
            'td.scwInputDate:hover,'        +
            'td.scwInputDateDisabled,'      +
            'td.scwWeekNo,'                 +
            'td.scwWeeks                {padding:           3px;'       +
                                        'width:             16px;'      +
                                        'height:            16px;'      +
                                        'font-weight:       bold;'      +
                                        'vertical-align:    middle;}'   +
            '/* Blend the colours into your page here...    */'         +
            '/* Calendar background */'                                 +
            'table.scw                  {background-color:  #6666CC;}'  +
            '/* Drag Handle */'                                         +
            'td.scwDrag                 {background-color:  #9999CC;'   +
                                        'color:             #CCCCFF;}'  +
            '/* Week number heading */'                                 +
            'td.scwWeekNumberHead       {color:             #6666CC;}'  +
            '/* Week day headings */'                                   +
            'td.scwWeek                 {color:             #CCCCCC;}'  +
            '/* Week numbers */'                                        +
            'td.scwWeekNo               {background-color:  #776677;'   +
                                        'color:             #CCCCCC;}'  +
            '/* Enabled Days */'                                        +
            '/* Week Day */'                                            +
            'td.scwCells                {background-color:  #CCCCCC;'   +
                                        'color:             #000000;}'  +
            '/* Day matching the input date */'                         +
            'td.scwInputDate            {background-color:  #CC9999;'   +
                                        'color:             #FF0000;}'  +
            '/* Weekend Day */'                                         +
            'td.scwCellsWeekend         {background-color:  #CCCCCC;'   +
                                        'color:             #CC6666;}'  +
            '/* Day outside the current month */'                       +
            'td.scwCellsExMonth         {background-color:  #CCCCCC;'   +
                                        'color:             #666666;}'  +
            '/* Today selector */'                                      +
            'td.scwFoot                 {background-color:  #6666CC;'   +
                                        'color:             #FFFFFF;}'  +
            '/* MouseOver/Hover formatting '                            +
            '       If you want to "turn off" any of the formatting '   +
            '       then just set to the same as the standard format'   +
            '       above.'                                             +
            ' '                                                         +
            '       Note: The reason that the following are'            +
            '       implemented using both a class and a :hover'        +
            '       pseudoclass is because Opera handles the rendering' +
            '       involved in the class swap very poorly and IE6 '    +
            '       (and below) only implements pseudoclasses on the'   +
            '       anchor tag.'                                        +
            '*/'                                                        +
            '/* Active cells */'                                        +
            'td.scwCells:hover,'                                        +
            'td.scwCellsHover           {background-color:  #FFFF00;'   +
                                        'cursor:            pointer;'   +
                                        'cursor:            hand;'      +
                                        'color:             #000000;}'  +
            '/* Day matching the input date */'                         +
            'td.scwInputDate:hover,'                                    +
            'td.scwInputDateHover       {background-color:  #FFFF00;'   +
                                        'cursor:            pointer;'   +
                                        'cursor:            hand;'      +
                                        'color:             #000000;}'  +
            '/* Weekend cells */'                                       +
            'td.scwCellsWeekend:hover,'                                 +
            'td.scwCellsWeekendHover    {background-color:  #FFFF00;'   +
                                        'cursor:            pointer;'   +
                                        'cursor:            hand;'      +
                                        'color:             #000000;}'  +
            '/* Day outside the current month */'                       +
            'td.scwCellsExMonth:hover,'                                 +
            'td.scwCellsExMonthHover    {background-color:  #FFFF00;'   +
                                        'cursor:            pointer;'   +
                                        'cursor:            hand;'      +
                                        'color:             #000000;}'  +
            '/* Today selector */'                                      +
            'td.scwFoot:hover,'                                         +
            'td.scwFootHover            {color:             #FFFF00;'   +
                                        'cursor:            pointer;'   +
                                        'cursor:            hand;'      +
                                        'font-weight:       bold;}'     +
            '/* Disabled cells */'                                      +
            '/* Week Day */'                                            +
            '/* Day matching the input date */'                         +
            'td.scwInputDateDisabled    {background-color:  #999999;'   +
                                        'color:             #000000;}'  +
            'td.scwCellsDisabled        {background-color:  #999999;'   +
                                        'color:             #000000;}'  +
            '/* Weekend Day */'                                         +
            'td.scwCellsWeekendDisabled {background-color:  #999999;'   +
                                        'color:             #CC6666;}'  +
            '/* Day outside the current month */'                       +
            'td.scwCellsExMonthDisabled {background-color:  #999999;'   +
                                        'color:             #666666;}'  +
            'td.scwFootDisabled         {background-color:  #6666CC;'   +
                                        'color:             #FFFFFF;}'  +
        '</style>'
                    );

//******************************************************************************
//------------------------------------------------------------------------------
// End of customisation section
//------------------------------------------------------------------------------
//******************************************************************************

//  Variables required by both scwShow and scwShowMonth

    var scwTargetEle,
        scwTriggerEle,
        scwMonthSum            = 0,
        scwBlnFullInputDate    = false,
        scwPassEnabledDay      = new Array(),
        scwSeedDate            = new Date(),
        scwParmActiveToday     = true,
        scwWeekStart           = scwWeekStart%7,
        scwToday,
        scwDrag,
        scwArrMonthNames,
        scwArrWeekInits,
        scwInvalidDateMsg,
        scwOutOfRangeMsg,
        scwDoesNotExistMsg,
        scwInvalidAlert,
        scwDateDisablingError,
        scwRangeDisablingError;

    // Add a method to format a date into the required pattern

    Date.prototype.scwFormat =
        function(scwFormat)
            {var charCount = 0,
                 codeChar  = '',
                 result    = '';

             for (var i=0;i<=scwFormat.length;i++)
                {if (i<scwFormat.length && scwFormat.charAt(i)==codeChar)
                        {// If we haven't hit the end of the string and
                         // the format string character is the same as
                         // the previous one, just clock up one to the
                         // length of the current element definition
                         charCount++;
                        }
                 else   {switch (codeChar)
                            {case 'y': case 'Y':
                                result += (this.getFullYear()%Math.
                                            pow(10,charCount)).toString().
                                            scwPadLeft(charCount);
                                break;
                             case 'm': case 'M':
                                // If we find an M, check the number of them to
                                // determine whether to get the month number or
                                // the month name.
                                result += (charCount<3)
                                            ?(this.getMonth()+1).
                                                toString().scwPadLeft(charCount)
                                            :scwArrMonthNames[this.getMonth()];
                                break;
                             case 'd': case 'D':
                                // If we find a D, get the date and format it
                                result += this.getDate().toString().
                                            scwPadLeft(charCount);
                                break;
                             default:
                                // Copy any unrecognised characters across
                                while (charCount-- > 0) {result += codeChar;}
                            }

                         if (i<scwFormat.length)
                            {// Store the character we have just worked on
                             codeChar  = scwFormat.charAt(i);
                             charCount = 1;
                            }
                        }
                }
             return result;
            }

    // Add a method to left pad zeroes

    String.prototype.scwPadLeft =
        function(padToLength)
            {var result = '';
             for (var i=0;i<(padToLength - this.length);i++) {result += '0';}
             return (result + this);
            }

    // Set up a closure so that any next function can be triggered
    // after the calendar has been closed AND that function can take
    // arguments.

    Function.prototype.runsAfterSCW =
        function()  {var func = this,
                         args = new Array(arguments.length);

                     for (var i=0;i<args.length;++i)
                        {args[i] = arguments[i];}

                     return function()
                        {// concat/join the two argument arrays
                         for (var i=0;i<arguments.length;++i)
                            {args[args.length] = arguments[i];}

                         return (args.shift()==scwTriggerEle)
                                    ?func.apply(this, args):null;
                        }
                    };

    // Use a global variable for the return value from the next action
    // IE fails to pass the function through if the target element is in
    // a form and scwNextAction is not defined.

    var scwNextActionReturn, scwNextAction;

// ****************************************************************************
// Start of Function Library
//
//  Exposed functions:
//
//      scwShow             Entry point for display of calendar,
//                              called in main page.
//      showCal             Legacy name of scwShow:
//                              Passes only legacy arguments,
//                              not the optional day disabling arguments.
//
//      scwShowMonth        Displays a month on the calendar,
//                              Called when a month is set or changed.
//
//      scwBeginDrag        Controls calendar dragging.
//
//      scwCancel           Called when the calendar background is clicked:
//                              Calls scwStopPropagation and may call scwHide.
//      scwHide             Hides the calendar, called on various events.
//      scwStopPropagation  Stops the propagation of an event.
//
// ****************************************************************************

    function showCal(scwEle,scwSourceEle)    {scwShow(scwEle,scwSourceEle);}
    function scwShow(scwEle,scwSourceEle)
        {scwTriggerEle = scwSourceEle;

         // Take any parameters that there might be from the third onwards as
         // day numbers to be disabled 0 = Sunday through to 6 = Saturday.

         scwParmActiveToday = true;

         for (var i=0;i<7;i++)
            {scwPassEnabledDay[(i+7-scwWeekStart)%7] = true;
             for (var j=2;j<arguments.length;j++)
                {if (arguments[j]==i)
                    {scwPassEnabledDay[(i+7-scwWeekStart)%7] = false;
                     if (scwDateNow.getDay()==i) scwParmActiveToday = false;
                    }
                }
            }

         //   If no value is preset then the seed date is
         //      Today (when today is in range) OR
         //      The middle of the date range.

         scwSeedDate = scwDateNow;

         // Strip space characters from start and end of date input
         scwEle.value = scwEle.value.replace(/^\s+/,'').replace(/\s+$/,'');

         // Set the language-dependent elements

         scwSetDefaultLanguage();

         document.getElementById('scwDragText').innerHTML = scwDrag;

         document.getElementById('scwMonths').options.length = 0;
         for (i=0;i<scwArrMonthNames.length;i++)
            document.getElementById('scwMonths').options[i] =
                new Option(scwArrMonthNames[i],scwArrMonthNames[i]);

         document.getElementById('scwYears').options.length = 0;
         for (i=0;i<scwDropDownYears;i++)
            document.getElementById('scwYears').options[i] =
                new Option((scwBaseYear+i),(scwBaseYear+i));

         for (i=0;i<scwArrWeekInits.length;i++)
            document.getElementById('scwWeekInit' + i).innerHTML =
                          scwArrWeekInits[(i+scwWeekStart)%
                                            scwArrWeekInits.length];

         if (document.getElementById('scwFoot'))
            document.getElementById('scwFoot').innerHTML =
                    scwToday + ' ' +
                    scwDateNow.scwFormat(scwDateDisplayFormat);

         if (scwEle.value.length==0)
            {// If no value is entered and today is within the range,
             // use today's date, otherwise use the middle of the valid range.

             scwBlnFullInputDate=false;

             if ((new Date(scwBaseYear+scwDropDownYears-1,11,31))<scwSeedDate ||
                 (new Date(scwBaseYear,0,1))                     >scwSeedDate
                )
                {scwSeedDate = new Date(scwBaseYear +
                                        Math.floor(scwDropDownYears / 2), 5, 1);
                }
            }
         else
            {function scwInputFormat(scwEleValue)
                {var scwArrSeed = new Array(),
                     scwArrInput = scwEle.value.
                                    split(new RegExp('[\\'+scwArrDelimiters.
                                                        join('\\')+']+','g'));

                 // "Escape" all the user defined date delimiters above -
                 // several delimiters will need it and it does no harm for
                 // the others.

                 // Strip any empty array elements (caused by delimiters)
                 // from the beginning or end of the array. They will
                 // still appear in the output string if in the output
                 // format.

                 if (scwArrInput[0].length==0) scwArrInput.splice(0,1);

                 if (scwArrInput[scwArrInput.length-1].length==0)
                    scwArrInput.splice(scwArrInput.length-1,1);

                 scwBlnFullInputDate = false;

                 switch (scwArrInput.length)
                    {case 1:
                        {// Year only entry
                         scwArrSeed[0] = parseInt(scwArrInput[0],10);   // Year
                         scwArrSeed[1] = '6';                           // Month
                         scwArrSeed[2] = 1;                             // Day
                         break;
                        }
                     case 2:
                        {// Year and Month entry
                         scwArrSeed[0] =
                             parseInt(scwArrInput[scwDateInputSequence.
                                                    replace(/D/i,'').
                                                    search(/Y/i)],10);  // Year
                         scwArrSeed[1] = scwArrInput[scwDateInputSequence.
                                                    replace(/D/i,'').
                                                    search(/M/i)];      // Month
                         scwArrSeed[2] = 1;                             // Day
                         break;
                        }
                     case 3:
                        {// Day Month and Year entry

                         scwArrSeed[0] =
                             parseInt(scwArrInput[scwDateInputSequence.
                                                    search(/Y/i)],10);  // Year
                         scwArrSeed[1] = scwArrInput[scwDateInputSequence.
                                                    search(/M/i)];      // Month
                         scwArrSeed[2] =
                             parseInt(scwArrInput[scwDateInputSequence.
                                                    search(/D/i)],10);  // Day

                         scwBlnFullInputDate = true;
                         break;
                        }
                     default:
                        {// A stuff-up has led to more than three elements in
                         // the date.
                         scwArrSeed[0] = 0;     // Year
                         scwArrSeed[1] = 0;     // Month
                         scwArrSeed[2] = 0;     // Day
                        }
                    }

                 // These regular expressions validate the input date format
                 // to the following rules;
                 //         Day   1-31 (optional zero on single digits)
                 //         Month 1-12 (optional zero on single digits)
                 //                     or case insensitive name
                 //         Year  One, Two or four digits

                 // Months names are as set in the language-dependent
                 // definitions and delimiters are set just below there

                 var scwExpValDay    = /^(0?[1-9]|[1-2]\d|3[0-1])$/,
                     scwExpValMonth  = new RegExp('^(0?[1-9]|1[0-2]|'        +
                                                  scwArrMonthNames.join('|') +
                                                  ')$','i'),
                     scwExpValYear   = /^(\d{1,2}|\d{4})$/;

                 // Apply validation and report failures

                 if (scwExpValYear.exec(scwArrSeed[0])  == null ||
                     scwExpValMonth.exec(scwArrSeed[1]) == null ||
                     scwExpValDay.exec(scwArrSeed[2])   == null
                    )
                    {if (scwShowInvalidDateMsg)
                        alert(scwInvalidDateMsg  +
                               scwInvalidAlert[0] + scwEleValue +
                               scwInvalidAlert[1]);
                     scwBlnFullInputDate = false;
                     scwArrSeed[0] = scwBaseYear +
                                     Math.floor(scwDropDownYears/2); // Year
                     scwArrSeed[1] = '6';                            // Month
                     scwArrSeed[2] = 1;                              // Day
                    }

                 // Return the  Year    in scwArrSeed[0]
                 //             Month   in scwArrSeed[1]
                 //             Day     in scwArrSeed[2]

                 return scwArrSeed;
                }

             // Parse the string into an array using the allowed delimiters

             scwArrSeedDate = scwInputFormat(scwEle.value);

             // So now we have the Year, Month and Day in an array.

             //   If the year is one or two digits then the routine assumes a
             //   year belongs in the 21st Century unless it is less than 50
             //   in which case it assumes the 20th Century is intended.

             if (scwArrSeedDate[0]<100)
                scwArrSeedDate[0] += (scwArrSeedDate[0]>50)?1900:2000;

             // Check whether the month is in digits or an abbreviation

             if (scwArrSeedDate[1].search(/\d+/)!=0)
                {month = scwArrMonthNames.join('|').toUpperCase().
                            search(scwArrSeedDate[1].substr(0,3).
                                                    toUpperCase());
                 scwArrSeedDate[1] = Math.floor(month/4)+1;
                }

             scwSeedDate = new Date(scwArrSeedDate[0],
                                    scwArrSeedDate[1]-1,
                                    scwArrSeedDate[2]);
            }

         // Test that we have arrived at a valid date

         if (isNaN(scwSeedDate))
            {if (scwShowInvalidDateMsg)
                alert(  scwInvalidDateMsg +
                        scwInvalidAlert[0] + scwEle.value +
                        scwInvalidAlert[1]);
             scwSeedDate = new Date(scwBaseYear +
                    Math.floor(scwDropDownYears/2),5,1);
             scwBlnFullInputDate=false;
            }
         else
            {// Test that the date is within range,
             // if not then set date to a sensible date in range.

             if ((new Date(scwBaseYear,0,1)) > scwSeedDate)
                {if (scwBlnStrict && scwShowOutOfRangeMsg)
                    alert(scwOutOfRangeMsg);
                 scwSeedDate = new Date(scwBaseYear,0,1);
                 scwBlnFullInputDate=false;
                }
             else
                {if ((new Date(scwBaseYear+scwDropDownYears-1,11,31))<
                      scwSeedDate)
                    {if (scwBlnStrict && scwShowOutOfRangeMsg)
                        alert(scwOutOfRangeMsg);
                     scwSeedDate = new Date(scwBaseYear +
                                            Math.floor(scwDropDownYears)-1,
                                                       11,1);
                     scwBlnFullInputDate=false;
                    }
                 else
                    {if (scwBlnStrict && scwBlnFullInputDate &&
                          (scwSeedDate.getDate()      != scwArrSeedDate[2] ||
                           (scwSeedDate.getMonth()+1) != scwArrSeedDate[1] ||
                           scwSeedDate.getFullYear()  != scwArrSeedDate[0]
                          )
                        )
                        {if (scwShowDoesNotExistMsg) alert(scwDoesNotExistMsg);
                         scwSeedDate = new Date(scwSeedDate.getFullYear(),
                                                scwSeedDate.getMonth()-1,1);
                         scwBlnFullInputDate=false;
                        }
                    }
                }
            }

         // Test the disabled dates for validity
         // Give error message if not valid.

         for (var i=0;i<scwDisabledDates.length;i++)
            {if (!((typeof scwDisabledDates[i]      == 'object') &&
                   (scwDisabledDates[i].constructor == Date)))
                {if ((typeof scwDisabledDates[i]      == 'object') &&
                     (scwDisabledDates[i].constructor == Array))
                    {var scwPass = true;

                     if (scwDisabledDates[i].length !=2)
                        {if (scwShowRangeDisablingError)
                            alert(  scwRangeDisablingError[0] +
                                    scwDisabledDates[i] +
                                    scwRangeDisablingError[1]);
                         scwPass = false;
                        }
                     else
                        {for (var j=0;j<scwDisabledDates[i].length;j++)
                            {if (!((typeof scwDisabledDates[i][j]
                                    == 'object') &&
                                   (scwDisabledDates[i][j].constructor
                                    == Date)))
                                {if (scwShowRangeDisablingError)
                                    alert(  scwDateDisablingError[0] +
                                            scwDisabledDates[i][j] +
                                            scwDateDisablingError[1]);
                                 scwPass = false;
                                }
                            }
                        }

                     if (scwPass &&
                         (scwDisabledDates[i][0] > scwDisabledDates[i][1])
                        )
                        {scwDisabledDates[i].reverse();}
                    }
                 else
                    {if (scwShowRangeDisablingError)
                        alert(  scwDateDisablingError[0] +
                                scwDisabledDates[i] +
                                scwDateDisablingError[1]);
                    }
                }
            }

         // Calculate the number of months that the entered (or
         // defaulted) month is after the start of the allowed
         // date range.

         scwMonthSum =  12*(scwSeedDate.getFullYear()-scwBaseYear)+
                            scwSeedDate.getMonth();

         // Set the drop down boxes.

         document.getElementById('scwYears').options.selectedIndex =
            Math.floor(scwMonthSum/12);
         document.getElementById('scwMonths').options.selectedIndex=
            (scwMonthSum%12);

         // Check whether or not dragging is allowed and display drag handle
         // if necessary

         document.getElementById('scwDrag').style.display=
             (scwAllowDrag)
                ?((document.getElementById('scwIFrame')||
                   document.getElementById('scwIEgte7'))?'block':'table-row')
                :'none';

         // Display the month

         scwShowMonth(0);

         // Position the calendar box

         var offsetTop =parseInt(scwEle.offsetTop ,10) +
                        parseInt(scwEle.offsetHeight,10),
             offsetLeft=parseInt(scwEle.offsetLeft,10);

         scwTargetEle=scwEle;

         do {scwEle=scwEle.offsetParent;
             offsetTop +=parseInt(scwEle.offsetTop,10);
             offsetLeft+=parseInt(scwEle.offsetLeft,10);
            }
         while (scwEle.tagName!='BODY' && scwEle.tagName!='HTML');

         document.getElementById('scw').style.top =offsetTop +'px';
         document.getElementById('scw').style.left=offsetLeft+'px';

         if (document.getElementById('scwIframe'))
            {document.getElementById('scwIframe').style.top=offsetTop +'px';
             document.getElementById('scwIframe').style.left=offsetLeft+'px';
             document.getElementById('scwIframe').style.width=
                (document.getElementById('scw').offsetWidth-2)+'px';
             document.getElementById('scwIframe').style.height=
                (document.getElementById('scw').offsetHeight-2)+'px';
             document.getElementById('scwIframe').style.visibility='visible';
            }

         // Show it on the page

         document.getElementById('scw').style.visibility='visible';

         if (typeof event=='undefined')
                {scwSourceEle.parentNode.
                        addEventListener('click',scwStopPropagation,false);
                }
         else   {event.cancelBubble = true;}
        }

    function scwHide()
        {document.getElementById('scw').style.visibility='hidden';
         if (document.getElementById('scwIframe'))
            {document.getElementById('scwIframe').style.visibility='hidden';}

         if (typeof scwNextAction!='undefined' && scwNextAction!=null)
             {scwNextActionReturn = scwNextAction();
              // Explicit null set to prevent closure causing memory leak
              scwNextAction = null;
             }
        }

    function scwCancel(scwEvt)
        {if (scwClickToHide) scwHide();
         scwStopPropagation(scwEvt);
        }

    function scwStopPropagation(scwEvt)
        {if (scwEvt.stopPropagation)
                scwEvt.stopPropagation();     // Capture phase
         else   scwEvt.cancelBubble = true;   // Bubbling phase
        }

    function scwBeginDrag(event)
        {var elementToDrag = document.getElementById('scw');

         var deltaX    = event.clientX,
             deltaY    = event.clientY,
             offsetEle = elementToDrag;

         do {deltaX   -= parseInt(offsetEle.offsetLeft,10);
             deltaY   -= parseInt(offsetEle.offsetTop ,10);
             offsetEle = offsetEle.offsetParent;
            }
         while (offsetEle.tagName!='BODY' &&
                offsetEle.tagName!='HTML');

         if (document.addEventListener)
                {document.addEventListener('mousemove',
                                           moveHandler,
                                           true);        // Capture phase
                 document.addEventListener('mouseup',
                                           upHandler,
                                           true);        // Capture phase
                }
         else   {elementToDrag.attachEvent('onmousemove',
                                           moveHandler); // Bubbling phase
                 elementToDrag.attachEvent('onmouseup',
                                             upHandler); // Bubbling phase
                 elementToDrag.setCapture();
                }

         scwStopPropagation(event);

         function moveHandler(scwEvt)
            {if (!scwEvt) scwEvt = window.event;

             elementToDrag.style.left = (scwEvt.clientX - deltaX) + 'px';
             elementToDrag.style.top  = (scwEvt.clientY - deltaY) + 'px';

             if (document.getElementById('scwIframe'))
                {document.getElementById('scwIframe').style.left =
                    (scwEvt.clientX - deltaX) + 'px';
                 document.getElementById('scwIframe').style.top  =
                    (scwEvt.clientY - deltaY) + 'px';
                }

             scwStopPropagation(scwEvt);
            }

         function upHandler(scwEvt)
            {if (!scwEvt) scwEvt = window.event;

             if (document.removeEventListener)
                    {document.removeEventListener('mousemove',
                                                  moveHandler,
                                                  true);     // Capture phase
                     document.removeEventListener('mouseup',
                                                  upHandler,
                                                  true);     // Capture phase
                    }
             else   {elementToDrag.detachEvent('onmouseup',
                                                 upHandler); // Bubbling phase
                     elementToDrag.detachEvent('onmousemove',
                                               moveHandler); // Bubbling phase
                     elementToDrag.releaseCapture();
                    }

             scwStopPropagation(scwEvt);
            }
        }

    function scwShowMonth(scwBias)
        {// Set the selectable Month and Year
         // May be called: from the left and right arrows
         //                  (shift month -1 and +1 respectively)
         //                from the month selection list
         //                from the year selection list
         //                from the showCal routine
         //                  (which initiates the display).

         var scwShowDate  = new Date(Date.parse(new Date().toDateString())),
             scwStartDate = new Date();

         scwSelYears  = document.getElementById('scwYears');
         scwSelMonths = document.getElementById('scwMonths');

         if (scwSelYears.options.selectedIndex>-1)
            {scwMonthSum=12*(scwSelYears.options.selectedIndex)+scwBias;
             if (scwSelMonths.options.selectedIndex>-1)
                {scwMonthSum+=scwSelMonths.options.selectedIndex;}
            }
         else
            {if (scwSelMonths.options.selectedIndex>-1)
                {scwMonthSum+=scwSelMonths.options.selectedIndex;}
            }

         scwShowDate.setFullYear(scwBaseYear + Math.floor(scwMonthSum/12),
                                 (scwMonthSum%12),
                                 1);

         // If the Week numbers are displayed, shift the week day names
         // to the right.
         document.getElementById('scwWeek_').style.display=
             (scwWeekNumberDisplay)
                ?((document.getElementById('scwIFrame')||
                   document.getElementById('scwIEgte7'))?'block':'table-cell')
                :'none';

         if ((12*parseInt((scwShowDate.getFullYear()-scwBaseYear),10)) +
             parseInt(scwShowDate.getMonth(),10) < (12*scwDropDownYears)  &&
             (12*parseInt((scwShowDate.getFullYear()-scwBaseYear),10)) +
             parseInt(scwShowDate.getMonth(),10) > -1)
            {scwSelYears.options.selectedIndex=Math.floor(scwMonthSum/12);
             scwSelMonths.options.selectedIndex=(scwMonthSum%12);

             scwCurMonth = scwShowDate.getMonth();

             scwShowDate.setDate((((scwShowDate.
                                    getDay()-scwWeekStart)<0)?-6:1)+
                                 scwWeekStart-scwShowDate.getDay());

             scwStartDate = new Date(scwShowDate);

             var scwFoot = document.getElementById('scwFoot');

             function scwFootOutput() {scwSetOutput(scwDateNow);}

             if (scwDisabledDates.length==0)
                {if (scwActiveToday && scwParmActiveToday)
                    {scwFoot.onclick     = scwFootOutput;
                     scwFoot.className   = 'scwFoot';

                     if (document.getElementById('scwIFrame'))
                        {scwFoot.onmouseover  = scwChangeClass;
                         scwFoot.onmouseout   = scwChangeClass;
                        }

                    }
                 else
                    {scwFoot.onclick     = null;
                     scwFoot.className   = 'scwFootDisabled';

                     if (document.getElementById('scwIFrame'))
                        {scwFoot.onmouseover  = null;
                         scwFoot.onmouseout   = null;
                        }

                     if (document.addEventListener)
                            {scwFoot.addEventListener('click',
                                                      scwStopPropagation,
                                                      false);
                            }
                     else   {scwFoot.attachEvent('onclick',
                                                 scwStopPropagation);}
                    }
                }
             else
                {for (var k=0;k<scwDisabledDates.length;k++)
                    {if (!scwActiveToday || !scwParmActiveToday ||
                         ((typeof scwDisabledDates[k] == 'object')            &&
                             (((scwDisabledDates[k].constructor == Date)      &&
                               scwDateNow.valueOf() == scwDisabledDates[k].
                                                            valueOf()
                              ) ||
                              ((scwDisabledDates[k].constructor == Array)     &&
                               scwDateNow.valueOf() >= scwDisabledDates[k][0].
                                                        valueOf()             &&
                               scwDateNow.valueOf() <= scwDisabledDates[k][1].
                                                        valueOf()
                              )
                             )
                         )
                        )
                        {scwFoot.onclick     = null;
                         scwFoot.className   = 'scwFootDisabled';

                         if (document.getElementById('scwIFrame'))
                            {scwFoot.onmouseover  = null;
                             scwFoot.onmouseout   = null;
                            }

                         if (document.addEventListener)
                                {scwFoot.addEventListener('click',
                                                          scwStopPropagation,
                                                          false);
                                }
                         else   {scwFoot.attachEvent('onclick',
                                                     scwStopPropagation);
                                }
                         break;
                        }
                     else
                        {scwFoot.onclick=scwFootOutput;
                         scwFoot.className='scwFoot';

                         if (document.getElementById('scwIFrame'))
                            {scwFoot.onmouseover  = scwChangeClass;
                             scwFoot.onmouseout   = scwChangeClass;
                            }
                        }
                    }
                }

             function scwSetOutput(scwOutputDate)
                {scwTargetEle.value =
                    scwOutputDate.scwFormat(scwDateOutputFormat);
                 scwHide();
                }

             function scwCellOutput(scwEvt)
                {var scwEle = scwEventTrigger(scwEvt),
                     scwOutputDate = new Date(scwStartDate);

                 if (scwEle.nodeType==3) scwEle=scwEle.parentNode;

                 scwOutputDate.setDate(scwStartDate.getDate() +
                                         parseInt(scwEle.id.substr(8),10));

                 scwSetOutput(scwOutputDate);
                }

             function scwChangeClass(scwEvt)
                {var scwEle = scwEventTrigger(scwEvt);

                 if (scwEle.nodeType==3) scwEle=scwEle.parentNode;

                 switch (scwEle.className)
                    {case 'scwCells':
                        scwEle.className = 'scwCellsHover';
                        break;
                     case 'scwCellsHover':
                        scwEle.className = 'scwCells';
                        break;
                     case 'scwCellsExMonth':
                        scwEle.className = 'scwCellsExMonthHover';
                        break;
                     case 'scwCellsExMonthHover':
                        scwEle.className = 'scwCellsExMonth';
                        break;
                     case 'scwCellsWeekend':
                        scwEle.className = 'scwCellsWeekendHover';
                        break;
                     case 'scwCellsWeekendHover':
                        scwEle.className = 'scwCellsWeekend';
                        break;
                     case 'scwFoot':
                        scwEle.className = 'scwFootHover';
                        break;
                     case 'scwFootHover':
                        scwEle.className = 'scwFoot';
                        break;
                     case 'scwInputDate':
                        scwEle.className = 'scwInputDateHover';
                        break;
                     case 'scwInputDateHover':
                        scwEle.className = 'scwInputDate';
                    }

                 return true;
                }

             function scwEventTrigger(scwEvt)
                {if (!scwEvt) scwEvt = event;
                 return scwEvt.target||scwEvt.srcElement;
                }

            function scwWeekNumber(scwInDate)
                {// The base day in the week of the input date
                 var scwInDateWeekBase = new Date(scwInDate);

                 scwInDateWeekBase.setDate(scwInDateWeekBase.getDate()
                                            - scwInDateWeekBase.getDay()
                                            + scwWeekNumberBaseDay
                                            + ((scwInDate.getDay()>
                                                scwWeekNumberBaseDay)?7:0));

                 // The first Base Day in the year
                 var scwFirstBaseDay =
                        new Date(scwInDateWeekBase.getFullYear(),0,1)

                 scwFirstBaseDay.setDate(scwFirstBaseDay.getDate()
                                            - scwFirstBaseDay.getDay()
                                            + scwWeekNumberBaseDay
                                        );

                 if (scwFirstBaseDay <
                        new Date(scwInDateWeekBase.getFullYear(),0,1))
                    {scwFirstBaseDay.setDate(scwFirstBaseDay.getDate()+7);}

                 // Start of Week 01
                 var scwStartWeekOne = new Date(scwFirstBaseDay
                                                - scwWeekNumberBaseDay
                                                + scwInDate.getDay());

                 if (scwStartWeekOne > scwFirstBaseDay)
                    {scwStartWeekOne.setDate(scwStartWeekOne.getDate()-7);}

                 // Subtract the date of the current week from the date of the
                 // first week of the year to get the number of weeks in
                 // milliseconds.  Divide by the number of milliseconds
                 // in a week then round to no decimals in order to remove
                 // the effect of daylight saving.  Add one to make the first
                 // week, week 1.  Place a string zero on the front so that
                 // week numbers are zero filled.

                 var scwWeekNo =
                     '0' + (Math.round((scwInDateWeekBase -
                                        scwFirstBaseDay)/604800000,0) + 1);

                 // Return the last two characters in the week number string

                 return scwWeekNo.substring(scwWeekNo.length-2,
                                            scwWeekNo.length);
                }

             // Treewalk to display the dates.
             // I tried to use getElementsByName but IE refused to cooperate
             // so I resorted to this method which works for all tested
             // browsers.

             var scwCells = document.getElementById('scwCells');

             for (i=0;i<scwCells.childNodes.length;i++)
                {var scwRows = scwCells.childNodes[i];
                 if (scwRows.nodeType==1 && scwRows.tagName=='TR')
                    {if (scwWeekNumberDisplay)
                        {//Calculate the week number using scwShowDate
                         scwRows.childNodes[0].innerHTML =
                             scwWeekNumber(scwShowDate);
                         scwRows.childNodes[0].style.display=
                            (document.getElementById('scwIFrame')||
                             document.getElementById('scwIEgte7'))
                                ?'block'
                                :'table-cell';
                        }
                     else
                        {scwRows.childNodes[0].style.display='none';}

                     for (j=1;j<scwRows.childNodes.length;j++)
                        {var scwCols = scwRows.childNodes[j];
                         if (scwCols.nodeType==1 && scwCols.tagName=='TD')
                            {scwRows.childNodes[j].innerHTML=
                                scwShowDate.getDate();
                             var scwCell=scwRows.childNodes[j],
                                 scwDisabled =
                                    (scwOutOfRangeDisable &&
                                     (scwShowDate < (new Date(scwBaseYear,0,1))
                                      ||
                                      scwShowDate > (new Date(scwBaseYear+
                                                              scwDropDownYears-
                                                              1,11,31))
                                     )
                                    )?true:false;

                             for (var k=0;k<scwDisabledDates.length;k++)
                                {if ((typeof scwDisabledDates[k]=='object')
                                     &&
                                     (scwDisabledDates[k].constructor ==
                                      Date
                                     )
                                     &&
                                     scwShowDate.valueOf() ==
                                        scwDisabledDates[k].valueOf()
                                    )
                                    {scwDisabled = true;}
                                 else
                                    {if ((typeof scwDisabledDates[k]=='object')
                                         &&
                                         (scwDisabledDates[k].constructor ==
                                          Array
                                         )
                                         &&
                                         scwShowDate.valueOf() >=
                                             scwDisabledDates[k][0].valueOf()
                                         &&
                                         scwShowDate.valueOf() <=
                                             scwDisabledDates[k][1].valueOf()
                                        )
                                        {scwDisabled = true;}
                                    }
                                }

                             if (scwDisabled ||
                                 !scwEnabledDay[j-1+(7*((i*scwCells.
                                                          childNodes.
                                                          length)/6))] ||
                                 !scwPassEnabledDay[(j-1+(7*(i*scwCells.
                                                               childNodes.
                                                               length/6)))%7]
                                )
                                {scwRows.childNodes[j].onclick     = null;

                                 if (document.getElementById('scwIFrame'))
                                    {scwRows.childNodes[j].onmouseover  = null;
                                     scwRows.childNodes[j].onmouseout   = null;
                                    }

                                 scwCell.className=
                                    (scwShowDate.getMonth()!=scwCurMonth)
                                        ?'scwCellsExMonthDisabled'
                                        :(scwBlnFullInputDate &&
                                          scwShowDate.toDateString()==
                                          scwSeedDate.toDateString())
                                            ?'scwInputDateDisabled'
                                            :(scwShowDate.getDay()%6==0)
                                                ?'scwCellsWeekendDisabled'
                                                :'scwCellsDisabled';
                                }
                             else
                                {scwRows.childNodes[j].onclick=scwCellOutput;

                                 if (document.getElementById('scwIFrame'))
                                    {scwRows.childNodes[j].onmouseover  =
                                        scwChangeClass;
                                     scwRows.childNodes[j].onmouseout   =
                                        scwChangeClass;
                                    }

                                 scwCell.className=
                                     (scwShowDate.getMonth()!=scwCurMonth)
                                        ?'scwCellsExMonth'
                                        :(scwBlnFullInputDate &&
                                          scwShowDate.toDateString()==
                                          scwSeedDate.toDateString())
                                            ?'scwInputDate'
                                            :(scwShowDate.getDay()%6==0)
                                                ?'scwCellsWeekend'
                                                :'scwCells';

                               }

                             scwShowDate.setDate(scwShowDate.getDate()+1);
                            }
                        }
                    }
                }
            }
         // Force a re-draw to prevent Opera's poor dynamic rendering
         // from leaving garbage in the calendar when the displayed
         // month is changed.
         document.getElementById('scw').style.visibility='hidden';
         document.getElementById('scw').style.visibility='visible';
        }

// *************************
//  End of Function Library
// *************************
// ***************************
// Start of Calendar structure
// ***************************

    document.write(
     "<!--[if gte IE 7]>" +
        "<div id='scwIEgte7'></div>" +
     "<![endif]-->" +
     "<!--[if lt  IE 7]>" +
        "<iframe class='scw' src='scwblank.html' " +
                "id='scwIframe' name='scwIframe' " +
                "frameborder='0'>" +
        "</iframe>" +
     "<![endif]-->" +
     "<table id='scw' class='scw'>" +
       "<tr class='scw'>" +
         "<td class='scw'>" +
           "<table class='scwHead' id='scwHead' width='100%' " +
                    "cellspacing='0' cellpadding='0'>" +
            "<tr id='scwDrag' style='display:none;'>" +
                "<td colspan='4' class='scwDrag' " +
                    "onmousedown='scwBeginDrag(event);'>" +
                    "<div id='scwDragText'></div>" +
                "</td>" +
            "</tr>" +
            "<tr class='scwHead' >" +
                 "<td class='scwHead'>" +
                    "<input class='scwHead' id='scwHeadLeft' type='button' value='<' " +
                            "onclick='scwShowMonth(-1);'  /></td>" +
                 "<td class='scwHead'>" +
                    "<select id='scwMonths' class='scwHead' " +
                            "onchange='scwShowMonth(0);'>" +
                    "</select>" +
                 "</td>" +
                 "<td class='scwHead'>" +
                    "<select id='scwYears' class='scwHead' " +
                            "onchange='scwShowMonth(0);'>" +
                    "</select>" +
                 "</td>" +
                 "<td class='scwHead'>" +
                    "<input class='scwHead' id='scwHeadRight' type='button' value='>' " +
                            "onclick='scwShowMonth(1);' /></td>" +
                "</tr>" +
              "</table>" +
            "</td>" +
          "</tr>" +
          "<tr class='scw'>" +
            "<td class='scw'>" +
              "<table class='scwCells' align='center'>" +
                "<thead>" +
                  "<tr><td class='scwWeekNumberHead' id='scwWeek_' ></td>");

    for (i=0;i<7;i++)
        document.write( "<td class='scwWeek' id='scwWeekInit" + i + "'></td>");

    document.write("</tr>" +
                "</thead>" +
                "<tbody id='scwCells' " +
                        "onClick='scwStopPropagation(event);'>");

    for (i=0;i<6;i++)
        {document.write(
                    "<tr>" +
                      "<td class='scwWeekNo' id='scwWeek_" + i + "'></td>");
         for (j=0;j<7;j++)
            {document.write(
                        "<td class='scwCells' id='scwCell_" + (j+(i*7)) +
                        "'></td>");
            }

         document.write(
                    "</tr>");
        }

    document.write(
                "</tbody>");

    if ((new Date(scwBaseYear + scwDropDownYears, 11, 32)) > scwDateNow &&
        (new Date(scwBaseYear, 0, 0))                      < scwDateNow)
        {document.write(
                  "<tfoot class='scwFoot'>" +
                    "<tr class='scwFoot'>" +
                      "<td class='scwFoot' id='scwFoot' colspan='8'>" +
                      "</td>" +
                    "</tr>" +
                  "</tfoot>");
        }

    document.write(
              "</table>" +
            "</td>" +
          "</tr>" +
        "</table>");

    if (document.addEventListener)
            {document.getElementById('scw'         ).addEventListener('click',scwCancel,false);
             document.getElementById('scwHeadLeft' ).addEventListener('click',scwStopPropagation,false);
             document.getElementById('scwMonths'   ).addEventListener('click',scwStopPropagation,false);
             document.getElementById('scwMonths'   ).addEventListener('change',scwStopPropagation,false);
             document.getElementById('scwYears'    ).addEventListener('click',scwStopPropagation,false);
             document.getElementById('scwYears'    ).addEventListener('change',scwStopPropagation,false);
             document.getElementById('scwHeadRight').addEventListener('click',scwStopPropagation,false);
            }
    else    {document.getElementById('scw'         ).attachEvent('onclick',scwCancel);
             document.getElementById('scwHeadLeft' ).attachEvent('onclick',scwStopPropagation);
             document.getElementById('scwMonths'   ).attachEvent('onclick',scwStopPropagation);
             document.getElementById('scwMonths'   ).attachEvent('onchange',scwStopPropagation);
             document.getElementById('scwYears'    ).attachEvent('onclick',scwStopPropagation);
             document.getElementById('scwYears'    ).attachEvent('onchange',scwStopPropagation);
             document.getElementById('scwHeadRight').attachEvent('onclick',scwStopPropagation);
            }

// ***************************
//  End of Calendar structure
// ***************************
// ****************************************
// Start of document level event definition
// ****************************************

    if (document.addEventListener)
            {document.addEventListener('click',scwHide, false);}
    else    {document.attachEvent('onclick',scwHide);}

// ****************************************
//  End of document level event definition
// ****************************************
// ************************************
//  End of Simple Calendar Widget Code
// ************************************