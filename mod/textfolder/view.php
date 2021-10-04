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
 * textfolder module main user interface
 *
 * @package   mod_textfolder
 * @copyright 2009 Petr Skoda  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/textfolder/locallib.php");
require_once("$CFG->dirroot/repository/lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);  // Course module ID
$f  = optional_param('f', 0, PARAM_INT);   // textfolder instance id

if ($f) {  // Two ways to specify the module
    $textfolder = $DB->get_record('textfolder', array('id'=>$f), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('textfolder', $textfolder->id, $textfolder->course, true, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('textfolder', $id, 0, true, MUST_EXIST);
    $textfolder = $DB->get_record('textfolder', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/textfolder:view', $context);
if ($textfolder->display == textfolder_DISPLAY_INLINE) {
    redirect(course_get_url($textfolder->course, $cm->sectionnum));
}

$params = array(
    'context' => $context,
    'objectid' => $textfolder->id
);
$event = \mod_textfolder\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('textfolder', $textfolder);
$event->trigger();

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/textfolder/view.php', array('id' => $cm->id));

$PAGE->set_title($course->shortname.': '.$textfolder->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($textfolder);


$output = $PAGE->get_renderer('mod_textfolder');

echo $output->header();

echo $output->heading(format_string($textfolder->name), 2);

echo $output->display_textfolder($textfolder);

echo $output->footer();
