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
 * External mod_presentationfolder functions unit tests
 *
 * @package    mod_presentationfolder
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * External mod_presentationfolder functions unit tests
 *
 * @package    mod_presentationfolder
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_presentationfolder_external_testcase extends externallib_advanced_testcase {

    /**
     * Test view_presentationfolder
     */
    public function test_view_presentationfolder() {
        global $DB;

        $this->resetAfterTest(true);

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course();
        $presentationfolder = $this->getDataGenerator()->create_module('presentationfolder', array('course' => $course->id));
        $context = context_module::instance($presentationfolder->cmid);
        $cm = get_coursemodule_from_instance('presentationfolder', $presentationfolder->id);

        // Test invalid instance id.
        try {
            mod_presentationfolder_external::view_presentationfolder(0);
            $this->fail('Exception expected due to invalid mod_presentationfolder instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

        // Test not-enrolled user.
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_presentationfolder_external::view_presentationfolder($presentationfolder->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

        // Test user with full capabilities.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        $result = mod_presentationfolder_external::view_presentationfolder($presentationfolder->id);
        $result = external_api::clean_returnvalue(mod_presentationfolder_external::view_presentationfolder_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_presentationfolder\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodlepresentationfolder = new \moodle_url('/mod/presentationfolder/view.php', array('id' => $cm->id));
        $this->assertEquals($moodlepresentationfolder, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

        // Test user with no capabilities.
        // We need a explicit prohibit since this capability is only defined in authenticated user and guest roles.
        assign_capability('mod/presentationfolder:view', CAP_PROHIBIT, $studentrole->id, $context->id);
        // Empty all the caches that may be affected by this change.
        accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        try {
            mod_presentationfolder_external::view_presentationfolder($presentationfolder->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }
    }

    /**
     * Test test_mod_presentationfolder_get_presentationfolders_by_courses
     */
    public function test_mod_presentationfolder_get_presentationfolders_by_courses() {
        global $DB;

        $this->resetAfterTest(true);

        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        $student = self::getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($student->id, $course1->id, $studentrole->id);

        self::setUser($student);

        // First presentationfolder.
        $record = new stdClass();
        $record->course = $course1->id;
        $record->forcedownload = 1;
        $presentationfolder1 = self::getDataGenerator()->create_module('presentationfolder', $record);

        // Second presentationfolder.
        $record = new stdClass();
        $record->course = $course2->id;
        $record->forcedownload = 0;
        $presentationfolder2 = self::getDataGenerator()->create_module('presentationfolder', $record);

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

        $returndescription = mod_presentationfolder_external::get_presentationfolders_by_courses_returns();

        // Create what we expect to be returned when querying the two courses.
        $expectedfields = array('id', 'coursemodule', 'course', 'name', 'intro', 'introformat', 'introfiles', 'revision',
                                'timemodified', 'display', 'showexpanded', 'showdownloadpresentationfolder', 'section', 'visible',
                                'forcedownload', 'groupmode', 'groupingid');

        // Add expected coursemodule and data.
        $presentationfolder1->coursemodule = $presentationfolder1->cmid;
        $presentationfolder1->introformat = 1;
        $presentationfolder1->section = 0;
        $presentationfolder1->visible = true;
        $presentationfolder1->groupmode = 0;
        $presentationfolder1->groupingid = 0;
        $presentationfolder1->introfiles = [];

        $presentationfolder2->coursemodule = $presentationfolder2->cmid;
        $presentationfolder2->introformat = 1;
        $presentationfolder2->section = 0;
        $presentationfolder2->visible = true;
        $presentationfolder2->groupmode = 0;
        $presentationfolder2->groupingid = 0;
        $presentationfolder2->introfiles = [];

        foreach ($expectedfields as $field) {
            $expected1[$field] = $presentationfolder1->{$field};
            $expected2[$field] = $presentationfolder2->{$field};
        }

        $expectedpresentationfolders = array($expected2, $expected1);

        // Call the external function passing course ids.
        $result = mod_presentationfolder_external::get_presentationfolders_by_courses(array($course2->id, $course1->id));
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedpresentationfolders, $result['presentationfolders']);
        $this->assertCount(0, $result['warnings']);

        // Call the external function without passing course id.
        $result = mod_presentationfolder_external::get_presentationfolders_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedpresentationfolders, $result['presentationfolders']);
        $this->assertCount(0, $result['warnings']);

        // Add a file to the intro.
        $fileintroname = "fileintro.txt";
        $filerecordinline = array(
            'contextid' => context_module::instance($presentationfolder2->cmid)->id,
            'component' => 'mod_presentationfolder',
            'filearea'  => 'intro',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => $fileintroname,
        );
        $fs = get_file_storage();
        $timepost = time();
        $fs->create_file_from_string($filerecordinline, 'image contents (not really)');

        $result = mod_presentationfolder_external::get_presentationfolders_by_courses(array($course2->id, $course1->id));
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertCount(1, $result['presentationfolders'][0]['introfiles']);
        $this->assertEquals($fileintroname, $result['presentationfolders'][0]['introfiles'][0]['filename']);

        // Unenrol user from second course.
        $enrol->unenrol_user($instance2, $student->id);
        array_shift($expectedpresentationfolders);

        // Call the external function without passing course id.
        $result = mod_presentationfolder_external::get_presentationfolders_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedpresentationfolders, $result['presentationfolders']);

        // Call for the second course we unenrolled the user from, expected warning.
        $result = mod_presentationfolder_external::get_presentationfolders_by_courses(array($course2->id));
        $this->assertCount(1, $result['warnings']);
        $this->assertEquals('1', $result['warnings'][0]['warningcode']);
        $this->assertEquals($course2->id, $result['warnings'][0]['itemid']);
    }
}
