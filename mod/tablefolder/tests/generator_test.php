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
 * mod_tablefolder generator tests
 *
 * @package    mod_tablefolder
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Genarator tests class for mod_tablefolder.
 *
 * @package    mod_tablefolder
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tablefolder_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('tablefolder', array('course' => $course->id)));
        $tablefolder = $this->getDataGenerator()->create_module('tablefolder', array('course' => $course));
        $records = $DB->get_records('tablefolder', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($tablefolder->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another tablefolder');
        $tablefolder = $this->getDataGenerator()->create_module('tablefolder', $params);
        $records = $DB->get_records('tablefolder', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another tablefolder', $records[$tablefolder->id]->name);

        // Examples of adding a tablefolder with files (do not validate anything, just check for exceptions).
        $params = array(
            'course' => $course->id,
            'files' => file_get_unused_draft_itemid()
        );
        $usercontext = context_user::instance($USER->id);
        $filerecord = array('component' => 'user', 'filearea' => 'draft',
                'contextid' => $usercontext->id, 'itemid' => $params['files'],
                'filename' => 'file1.txt', 'filepath' => '/');
        $fs = get_file_storage();
        $fs->create_file_from_string($filerecord, 'Test file contents');
        $tablefolder = $this->getDataGenerator()->create_module('tablefolder', $params);
    }
}
