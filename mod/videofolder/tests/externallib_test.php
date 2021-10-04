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
 * External mod_videofolder functions unit tests
 *
 * @package    mod_videofolder
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * External mod_videofolder functions unit tests
 *
 * @package    mod_videofolder
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_videofolder_external_testcase extends externallib_advanced_testcase {

    /**
     * Test view_videofolder
     */
    public function test_view_videofolder() {
        global $DB;

        $this->resetAfterTest(true);

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course();
        $videofolder = $this->getDataGenerator()->create_module('videofolder', array('course' => $course->id));
        $context = context_module::instance($videofolder->cmid);
        $cm = get_coursemodule_from_instance('videofolder', $videofolder->id);

        // Test invalid instance id.
        try {
            mod_videofolder_external::view_videofolder(0);
            $this->fail('Exception expected due to invalid mod_videofolder instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

        // Test not-enrolled user.
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_videofolder_external::view_videofolder($videofolder->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

        // Test user with full capabilities.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        $result = mod_videofolder_external::view_videofolder($videofolder->id);
        $result = external_api::clean_returnvalue(mod_videofolder_external::view_videofolder_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_videofolder\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodlevideofolder = new \moodle_url('/mod/videofolder/view.php', array('id' => $cm->id));
        $this->assertEquals($moodlevideofolder, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

        // Test user with no capabilities.
        // We need a explicit prohibit since this capability is only defined in authenticated user and guest roles.
        assign_capability('mod/videofolder:view', CAP_PROHIBIT, $studentrole->id, $context->id);
        // Empty all the caches that may be affected by this change.
        accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        try {
            mod_videofolder_external::view_videofolder($videofolder->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }
    }

    /**
     * Test test_mod_videofolder_get_videofolders_by_courses
     */
    public function test_mod_videofolder_get_videofolders_by_courses() {
        global $DB;

        $this->resetAfterTest(true);

        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        $student = self::getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($student->id, $course1->id, $studentrole->id);

        self::setUser($student);

        // First videofolder.
        $record = new stdClass();
        $record->course = $course1->id;
        $record->forcedownload = 1;
        $videofolder1 = self::getDataGenerator()->create_module('videofolder', $record);

        // Second videofolder.
        $record = new stdClass();
        $record->course = $course2->id;
        $record->forcedownload = 0;
        $videofolder2 = self::getDataGenerator()->create_module('videofolder', $record);

        // Execute real Moodle enrolment as we'll call unenrol() method on the instance later.
        $enrol = enrol_get_plugin('manual');
        $enrolinstances = enrol_get_instances($course2->id, true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "manual") {
                $instance2 = $courseenrolinstance;
                break;
            }
        }
        $enrol->enrol_user($instance2, $student->id, $studentrole->id);

        $returndescription = mod_videofolder_external::get_videofolders_by_courses_returns();

        // Create what we expect to be returned when querying the two courses.
        $expectedfields = array('id', 'coursemodule', 'course', 'name', 'intro', 'introformat', 'introfiles', 'revision',
                                'timemodified', 'display', 'showexpanded', 'showdownloadvideofolder', 'section', 'visible',
                                'forcedownload', 'groupmode', 'groupingid');

        // Add expected coursemodule and data.
        $videofolder1->coursemodule = $videofolder1->cmid;
        $videofolder1->introformat = 1;
        $videofolder1->section = 0;
        $videofolder1->visible = true;
        $videofolder1->groupmode = 0;
        $videofolder1->groupingid = 0;
        $videofolder1->introfiles = [];

        $videofolder2->coursemodule = $videofolder2->cmid;
        $videofolder2->introformat = 1;
        $videofolder2->section = 0;
        $videofolder2->visible = true;
        $videofolder2->groupmode = 0;
        $videofolder2->groupingid = 0;
        $videofolder2->introfiles = [];

        foreach ($expectedfields as $field) {
            $expected1[$field] = $videofolder1->{$field};
            $expected2[$field] = $videofolder2->{$field};
        }

        $expectedvideofolders = array($expected2, $expected1);

        // Call the external function passing course ids.
        $result = mod_videofolder_external::get_videofolders_by_courses(array($course2->id, $course1->id));
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedvideofolders, $result['videofolders']);
        $this->assertCount(0, $result['warnings']);

        // Call the external function without passing course id.
        $result = mod_videofolder_external::get_videofolders_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedvideofolders, $result['videofolders']);
        $this->assertCount(0, $result['warnings']);

        // Add a file to the intro.
        $fileintroname = "fileintro.txt";
        $filerecordinline = array(
            'contextid' => context_module::instance($videofolder2->cmid)->id,
            'component' => 'mod_videofolder',
            'filearea'  => 'intro',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => $fileintroname,
        );
        $fs = get_file_storage();
        $timepost = time();
        $fs->create_file_from_string($filerecordinline, 'image contents (not really)');

        $result = mod_videofolder_external::get_videofolders_by_courses(array($course2->id, $course1->id));
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertCount(1, $result['videofolders'][0]['introfiles']);
        $this->assertEquals($fileintroname, $result['videofolders'][0]['introfiles'][0]['filename']);

        // Unenrol user from second course.
        $enrol->unenrol_user($instance2, $student->id);
        array_shift($expectedvideofolders);

        // Call the external function without passing course id.
        $result = mod_videofolder_external::get_videofolders_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedvideofolders, $result['videofolders']);

        // Call for the second course we unenrolled the user from, expected warning.
        $result = mod_videofolder_external::get_videofolders_by_courses(array($course2->id));
        $this->assertCount(1, $result['warnings']);
        $this->assertEquals('1', $result['warnings'][0]['warningcode']);
        $this->assertEquals($course2->id, $result['warnings'][0]['itemid']);
    }
}
