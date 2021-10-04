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
 * presentationfolder download
 *
 * @package   mod_presentationfolder
 * @copyright 2015 Andrew Hancox <andrewdchancox@googlemail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

$id = required_param('id', PARAM_INT);  // Course module ID.
$cm = get_coursemodule_from_id('presentationfolder', $id, 0, true, MUST_EXIST);

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/presentationfolder:view', $context);

$presentationfolder = $DB->get_record('presentationfolder', array('id' => $cm->instance), '*', MUST_EXIST);

$downloadable = presentationfolder_archive_available($presentationfolder, $cm);
if (!$downloadable) {
    print_error('cannotdownloaddir', 'repository');
}

presentationfolder_downloaded($presentationfolder, $course, $cm, $context);

$fs = get_file_storage();
$file = $fs->get_file($context->id, 'mod_presentationfolder', 'content', 0, '/', '.');
if (!$file) {
    print_error('cannotdownloaddir', 'repository');
}

$zipper   = get_file_packer('application/zip');
$filename = shorten_filename(clean_filename($presentationfolder->name . "-" . date("Ymd")) . ".zip");
$temppath = make_request_directory() . $filename;

if ($zipper->archive_to_pathname(array('/' => $file), $temppath)) {
    send_temp_file($temppath, $filename);
} else {
    print_error('cannotdownloaddir', 'repository');
}
