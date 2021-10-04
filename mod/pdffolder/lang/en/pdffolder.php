<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'pdffolder', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   mod_pdffolder
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['bynameondate'] = 'by {$a->name} - {$a->date}';
$string['contentheader'] = 'Content';
$string['dnduploadmakepdffolder'] = 'Unzip files and create pdffolder';
$string['downloadpdffolder'] = 'Download pdffolder';
$string['eventallfilesdownloaded'] = 'Zip archive of pdffolder downloaded';
$string['eventpdffolderupdated'] = 'pdffolder updated';
$string['pdffolder:addinstance'] = 'Add a new pdffolder';
$string['pdffolder:managefiles'] = 'Manage files in pdffolder module';
$string['pdffolder:view'] = 'View pdffolder content';
$string['pdffoldercontent'] = 'Files and subpdffolders';
$string['forcedownload'] = 'Force download of files';
$string['forcedownload_help'] = 'Whether certain files, such as images or HTML files, should be displayed in the browser rather than being downloaded. Note that for security reasons, the setting should only be unticked if all users with the capability to manage files in the pdffolder are trusted users.';
$string['indicator:cognitivedepth'] = 'pdffolder cognitive';
$string['indicator:cognitivedepth_help'] = 'This indicator is based on the cognitive depth reached by the student in a pdffolder resource.';
$string['indicator:cognitivedepthdef'] = 'pdffolder cognitive';
$string['indicator:cognitivedepthdef_help'] = 'The participant has reached this percentage of the cognitive engagement offered by the pdffolder resources during this analysis interval (Levels = No view, View)';
$string['indicator:cognitivedepthdef_link'] = 'Learning_analytics_indicators#Cognitive_depth';
$string['indicator:socialbreadth'] = 'pdffolder social';
$string['indicator:socialbreadth_help'] = 'This indicator is based on the social breadth reached by the student in a pdffolder resource.';
$string['indicator:socialbreadthdef'] = 'pdffolder social';
$string['indicator:socialbreadthdef_help'] = 'The participant has reached this percentage of the social engagement offered by the pdffolder resources during this analysis interval (Levels = No participation, Participant alone)';
$string['indicator:socialbreadthdef_link'] = 'Learning_analytics_indicators#Social_breadth';
$string['modulename'] = 'pdffolder';
$string['modulename_help'] = 'The pdffolder module enables a teacher to display a number of related files inside a single pdffolder, reducing scrolling on the course page. A zipped pdffolder may be uploaded and unzipped for display, or an empty pdffolder created and files uploaded into it.

A pdffolder may be used

* For a series of files on one topic, for example a set of past examination papers in pdf format or a collection of image files for use in student projects
* To provide a shared uploading space for teachers on the course page (keeping the pdffolder hidden so that only teachers can see it)';
$string['modulename_link'] = 'mod/pdffolder/view';
$string['modulenameplural'] = 'pdffolders';
$string['newpdffoldercontent'] = 'New pdffolder content';
$string['page-mod-pdffolder-x'] = 'Any pdffolder module page';
$string['page-mod-pdffolder-view'] = 'pdffolder module main page';
$string['privacy:metadata'] = 'The pdffolder resource plugin does not store any personal data.';
$string['pluginadministration'] = 'pdffolder administration';
$string['pluginname'] = 'pdffolder';
$string['display'] = 'Display pdffolder contents';
$string['display_help'] = 'If you choose to display the pdffolder contents on a course page, there will be no link to a separate page. The description will be displayed only if \'Display description on course page\' is ticked. Note that participants view actions cannot be logged in this case.';
$string['displaypage'] = 'On a separate page';
$string['displayinline'] = 'Inline on a course page';
$string['noautocompletioninline'] = 'Automatic completion on viewing of activity can not be selected together with "Display inline" option';
$string['search:activity'] = 'pdffolder';
$string['showdownloadpdffolder'] = 'Show download pdffolder button';
$string['showdownloadpdffolder_help'] = 'If set to \'yes\', a button will be displayed allowing the contents of the pdffolder to be downloaded as a zip file.';
$string['showexpanded'] = 'Show subpdffolders expanded';
$string['showexpanded_help'] = 'If set to \'yes\', subpdffolders are shown expanded by default; otherwise they are shown collapsed.';
$string['maxsizetodownload'] = 'Maximum pdffolder download size (MB)';
$string['maxsizetodownload_help'] = 'The maximum size of pdffolder that can be downloaded as a zip file. If set to zero, the pdffolder size is unlimited.';
