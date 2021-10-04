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
 * presentationfolder module main user interface
 *
 * @package   mod_presentationfolder
 * @copyright 2009 Petr Skoda  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/presentationfolder/locallib.php");
require_once("$CFG->dirroot/repository/lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);  // Course module ID
$f  = optional_param('f', 0, PARAM_INT);   // presentationfolder instance id

if ($f) {  // Two ways to specify the module
    $presentationfolder = $DB->get_record('presentationfolder', array('id'=>$f), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('presentationfolder', $presentationfolder->id, $presentationfolder->course, true, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('presentationfolder', $id, 0, true, MUST_EXIST);
    $presentationfolder = $DB->get_record('presentationfolder', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/presentationfolder:view', $context);
if ($presentationfolder->display == presentationfolder_DISPLAY_INLINE) {
    redirect(course_get_url($presentationfolder->course, $cm->sectionnum));
}

$params = array(
    'context' => $context,
    'objectid' => $presentationfolder->id
);
$event = \mod_presentationfolder\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('presentationfolder', $presentationfolder);
$event->trigger();

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/presentationfolder/view.php', array('id' => $cm->id));

$PAGE->set_title($course->shortname.': '.$presentationfolder->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($presentationfolder);


$output = $PAGE->get_renderer('mod_presentationfolder');

echo $output->header();

echo $output->heading(format_string($presentationfolder->name), 2);

echo $output->display_presentationfolder($presentationfolder);

echo $output->footer();
