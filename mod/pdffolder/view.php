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
 * pdffolder module main user interface
 *
 * @package   mod_pdffolder
 * @copyright 2009 Petr Skoda  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/pdffolder/locallib.php");
require_once("$CFG->dirroot/repository/lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);  // Course module ID
$f  = optional_param('f', 0, PARAM_INT);   // pdffolder instance id

if ($f) {  // Two ways to specify the module
    $pdffolder = $DB->get_record('pdffolder', array('id'=>$f), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('pdffolder', $pdffolder->id, $pdffolder->course, true, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('pdffolder', $id, 0, true, MUST_EXIST);
    $pdffolder = $DB->get_record('pdffolder', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/pdffolder:view', $context);
if ($pdffolder->display == pdffolder_DISPLAY_INLINE) {
    redirect(course_get_url($pdffolder->course, $cm->sectionnum));
}

$params = array(
    'context' => $context,
    'objectid' => $pdffolder->id
);
$event = \mod_pdffolder\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('pdffolder', $pdffolder);
$event->trigger();

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/pdffolder/view.php', array('id' => $cm->id));

$PAGE->set_title($course->shortname.': '.$pdffolder->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($pdffolder);


$output = $PAGE->get_renderer('mod_pdffolder');

echo $output->header();

echo $output->heading(format_string($pdffolder->name), 2);

echo $output->display_pdffolder($pdffolder);

echo $output->footer();
