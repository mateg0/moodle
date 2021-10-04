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
 * videofolder module main user interface
 *
 * @package   mod_videofolder
 * @copyright 2009 Petr Skoda  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/videofolder/locallib.php");
require_once("$CFG->dirroot/repository/lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);  // Course module ID
$f  = optional_param('f', 0, PARAM_INT);   // videofolder instance id

if ($f) {  // Two ways to specify the module
    $videofolder = $DB->get_record('videofolder', array('id'=>$f), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('videofolder', $videofolder->id, $videofolder->course, true, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('videofolder', $id, 0, true, MUST_EXIST);
    $videofolder = $DB->get_record('videofolder', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videofolder:view', $context);
if ($videofolder->display == videofolder_DISPLAY_INLINE) {
    redirect(course_get_url($videofolder->course, $cm->sectionnum));
}

$params = array(
    'context' => $context,
    'objectid' => $videofolder->id
);
$event = \mod_videofolder\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('videofolder', $videofolder);
$event->trigger();

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/videofolder/view.php', array('id' => $cm->id));

$PAGE->set_title($course->shortname.': '.$videofolder->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($videofolder);


$output = $PAGE->get_renderer('mod_videofolder');

echo $output->header();

echo $output->heading(format_string($videofolder->name), 2);

echo $output->display_videofolder($videofolder);

echo $output->footer();
