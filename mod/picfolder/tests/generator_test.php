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
 * mod_picfolder generator tests
 *
 * @package    mod_picfolder
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Genarator tests class for mod_picfolder.
 *
 * @package    mod_picfolder
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_picfolder_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('picfolder', array('course' => $course->id)));
        $picfolder = $this->getDataGenerator()->create_module('picfolder', array('course' => $course));
        $records = $DB->get_records('picfolder', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($picfolder->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another picfolder');
        $picfolder = $this->getDataGenerator()->create_module('picfolder', $params);
        $records = $DB->get_records('picfolder', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another picfolder', $records[$picfolder->id]->name);

        // Examples of adding a picfolder with files (do not validate anything, just check for exceptions).
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
        $picfolder = $this->getDataGenerator()->create_module('picfolder', $params);
    }
}
