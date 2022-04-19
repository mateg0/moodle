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
 * My Moodle -- a user's personal dashboard
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - only the user can see their own dashboard
 * - users can add any blocks they want
 * - the administrators can define a default site dashboard for users who have
 *   not created their own dashboard
 *
 * This script implements the user's view of the dashboard, and allows editing
 * of the dashboard.
 *
 * @package    moodlecore
 * @subpackage my
 * @copyright  2010 Remote-Learner.net
 * @author     Hubert Chathi <hubert@remote-learner.net>
 * @author     Olav Jordan <olav.jordan@remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');

redirect_if_major_upgrade_required();

// TODO Add sesskey check to edit
$edit   = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off
$reset  = optional_param('reset', null, PARAM_BOOL);

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}

$strmymoodle = get_string('myhome');

if (isguestuser()) {  // Force them to see system default, no editing allowed
    // If guests are not allowed my moodle, send them to front page.
    if (empty($CFG->allowguestmymoodle)) {
        redirect(new moodle_url('/', array('redirect' => 0)));
    }

    $userid = null;
    $USER->editing = $edit = 0;  // Just in case
    $context = context_system::instance();
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :)
    $header = "$SITE->shortname: $strmymoodle (GUEST)";
    $pagetitle = $header;

} else {        // We are trying to view or edit our own My Moodle page
    $userid = $USER->id;  // Owner of the page
    $context = context_user::instance($USER->id);
    $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
    $header = fullname($USER);
    $pagetitle = $strmymoodle;
}

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PRIVATE)) {
    print_error('mymoodlesetup');
}

// Start setting up the page
$params = array();
$PAGE->set_context($context);
$PAGE->set_url('/my/index.php', $params);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('my-index');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($header);
$PAGE->blocks->add_region('horizontal');
$PAGE->blocks->add_region('center-pre');
//$PAGE->blocks->add_region('content');

if (!isguestuser()) {   // Skip default home page for guests
    if (get_home_page() != HOMEPAGE_MY) {
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_MY);
        } else if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_USER) {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'), new moodle_url('/my/', array('setdefaulthome' => true)),
                    navigation_node::TYPE_SETTING);
        }
    }
}

// Toggle the editing state and switches
if (empty($CFG->forcedefaultmymoodle) && $PAGE->user_allowed_editing()) {
    if ($reset !== null) {
        if (!is_null($userid)) {
            require_sesskey();
            if (!$currentpage = my_reset_page($userid, MY_PAGE_PRIVATE)) {
                print_error('reseterror', 'my');
            }
            redirect(new moodle_url('/my'));
        }
    } else if ($edit !== null) {             // Editing state was specified
        $USER->editing = $edit;       // Change editing state
    } else {                          // Editing state is in session
        if ($currentpage->userid) {   // It's a page we can edit, so load from session
            if (!empty($USER->editing)) {
                $edit = 1;
            } else {
                $edit = 0;
            }
        } else {
            // For the page to display properly with the user context header the page blocks need to
            // be copied over to the user context.
            if (!$currentpage = my_copy_page($USER->id, MY_PAGE_PRIVATE)) {
                print_error('mymoodlesetup');
            }
            $context = context_user::instance($USER->id);
            $PAGE->set_context($context);
            $PAGE->set_subpage($currentpage->id);
            // It's a system page and they are not allowed to edit system pages
            $USER->editing = $edit = 0;          // Disable editing completely, just to be safe
        }
    }

    // Add button for editing page
    $params = array('edit' => !$edit);

    $resetbutton = '';
    $resetstring = get_string('resetpage', 'my');
    $reseturl = new moodle_url("$CFG->wwwroot/my/index.php", array('edit' => 1, 'reset' => 1));

    if (!$currentpage->userid) {
        // viewing a system page -- let the user customise it
        $editstring = get_string('updatemymoodleon');
        $params['edit'] = 1;
    } else if (empty($edit)) {
        $editstring = get_string('updatemymoodleon');
    } else {
        $editstring = get_string('updatemymoodleoff');
        $resetbutton = $OUTPUT->single_button($reseturl, $resetstring);
    }

    $url = new moodle_url("$CFG->wwwroot/my/index.php", $params);
    $button = $OUTPUT->single_button($url, $editstring);
    $PAGE->set_button($resetbutton . $button);

} else {
    $USER->editing = $edit = 0;
}

echo $OUTPUT->header();

if (core_userfeedback::should_display_reminder()) {
    core_userfeedback::print_reminder_block();
}

if (false && has_capability('moodle/course:create', context_system::instance())) {
    $PAGE->requires->js('/local/calendarajax/assets/calendar_ajax.js');

    $time = time();
    $courseid = SITEID;
    $categoryid = null;
    $view = 'month';
    $calendar = calendar_information::create($time, $courseid, $categoryid);
    $renderer = $PAGE->get_renderer('core_calendar');
    echo html_writer::start_tag('div', array('class'=>'path-calendar'));
    echo $renderer->start_layout();
    echo html_writer::start_tag('div', array('class'=>'heightcontainer', 'id'=>'calendar_ajax'));
    list($data, $template) = calendar_get_view($calendar, $view, true, false, null);

    if($view == "day"){
        $calendarday = new \core_calendar\output\calendarday($data);
        $dayevents = $calendarday->getevents();
        $data->events = $dayevents;
    }

    echo $renderer->render_from_template($template, $data);
    echo html_writer::end_tag('div');
    echo $renderer->complete_layout();
    echo html_writer::end_tag('div');
}

$veriflastcourse = $DB->count_records('logstore_standard_log', array('action' => "viewed",
    'target' => "course", 'userid' => $USER->id));

if ($veriflastcourse !== 0) {
    $sql = 'SELECT * FROM {logstore_standard_log} WHERE ';
    $sql .= 'action = ?  AND target = ? AND userid = ? AND courseid != 1 ';
    $sql .= 'order by timecreated desc';
    $lastcourse = $DB->get_records_sql($sql, array('viewed', 'course', $USER->id),0, 1);
    if (count($lastcourse) > 0) {
        $record = reset($lastcourse);
        $id = intval($record->courseid);

        //    $id          = optional_param('id', 16, PARAM_INT);
        $marker      = optional_param('marker',-1 , PARAM_INT);

        $params = array('id' => $id);
        $course = $DB->get_record('course', $params, '*', MUST_EXIST);

        // Fix course format if it is no longer installed
        $course->format = course_get_format($course)->get_format();

        //Course title wrapper
        echo html_writer::start_tag('div', array('class'=>'course-title'));
        echo html_writer::start_tag('h4');
        echo $course->fullname;
        echo html_writer::end_tag('h4');
        echo html_writer::end_tag('div');


        if (user_has_role_assignment($USER->id, 5)) {
            $studentachievements = new \local_studentachievements\output\studentachievements();
            $studentstopwatch = new \local_studentstopwatch\output\studentstopwatch();
            $onlineclassmates = new \local_onlineclassmates\output\onlineclassmates();

            $achievementsRenderer = $PAGE->get_renderer('local_studentachievements');
            $stopwatchRenderer = $PAGE->get_renderer('local_studentstopwatch');
            $onlineclassmatesRenderer = $PAGE->get_renderer('local_onlineclassmates');

            echo '<div id="student-header-wrapper" class="student-header-wrapper">';

            echo '<div id="studentachievements-block" class="student-header-block">';
            echo $achievementsRenderer->renderStudentsAchievements($studentachievements);
            echo '</div>';

            echo '<div id="studentstopwatch-block" class="student-header-block">';
            echo $stopwatchRenderer->renderStudentStopwatch($studentstopwatch);
            echo '</div>';

            echo '<div id="onlineclassmates-block" class="student-header-block">';
            echo $onlineclassmatesRenderer->renderOnlineClassmates($onlineclassmates);
            echo '</div>';

            echo '</div>';

            $PAGE->requires->js('/local/onlineclassmates/assets/onlineclassmates.js');
            $PAGE->requires->js('/local/studentachievements/assets/studentachievements.js');
            $PAGE->requires->js('/local/studentstopwatch/assets/studentstopwatch.js');
        }
        echo $OUTPUT->custom_block_region('horizontal');
        echo $OUTPUT->custom_block_region('center-pre');

        // Course wrapper start.
        echo html_writer::start_tag('div', array('class'=>'course-content'));

        // make sure that section 0 exists (this function will create one if it is missing)
        course_create_sections_if_missing($course, 0);

        // get information about course modules and existing module types
        // format.php in course formats may rely on presence of these variables
        $modinfo = get_fast_modinfo($course);
        $modnames = get_module_types_names();
        $modnamesplural = get_module_types_names(true);
        $modnamesused = $modinfo->get_used_module_names();
        $mods = $modinfo->get_cms();
        $sections = $modinfo->get_section_info_all();

        // CAUTION, hacky fundamental variable defintion to follow!
        // Note that because of the way course fromats are constructed though
        // inclusion we pass parameters around this way..
        //$displaysection = $section;

        // Include the actual course format.
        require($CFG->dirroot .'/course/format/'. $course->format .'/format.php');

        // Content wrapper end.
        echo html_writer::end_tag('div');

    }
}
else {
    if (user_has_role_assignment($USER->id, 5)) {
        $studentachievements = new \local_studentachievements\output\studentachievements();
        $studentstopwatch = new \local_studentstopwatch\output\studentstopwatch();
        $onlineclassmates = new \local_onlineclassmates\output\onlineclassmates();

        $achievementsRenderer = $PAGE->get_renderer('local_studentachievements');
        $stopwatchRenderer = $PAGE->get_renderer('local_studentstopwatch');
        $onlineclassmatesRenderer = $PAGE->get_renderer('local_onlineclassmates');

        echo '<div id="student-header-wrapper" class="student-header-wrapper">';

        echo '<div id="studentachievements-block" class="student-header-block">';
        echo $achievementsRenderer->renderStudentsAchievements($studentachievements);
        echo '</div>';

        echo '<div id="studentstopwatch-block" class="student-header-block">';
        echo $stopwatchRenderer->renderStudentStopwatch($studentstopwatch);
        echo '</div>';

        echo '<div id="onlineclassmates-block" class="student-header-block">';
        echo $onlineclassmatesRenderer->renderOnlineClassmates($onlineclassmates);
        echo '</div>';

        echo '</div>';

        $PAGE->requires->js('/local/onlineclassmates/assets/onlineclassmates.js');
        $PAGE->requires->js('/local/studentachievements/assets/studentachievements.js');
        $PAGE->requires->js('/local/studentstopwatch/assets/studentstopwatch.js');
    }
    echo $OUTPUT->custom_block_region('horizontal');
    echo $OUTPUT->custom_block_region('center-pre');
}

echo $OUTPUT->footer();

// Trigger dashboard has been viewed event.
$eventparams = array('context' => $context);
$event = \core\event\dashboard_viewed::create($eventparams);
$event->trigger();
