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
 * Events tests.
 *
 * @package    mod_videofolder
 * @category   test
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_videofolder_events_testcase extends advanced_testcase {

    /**
     * Tests set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test the videofolder updated event.
     *
     * There is no external API for updating a videofolder, so the unit test will simply create
     * and trigger the event and ensure the legacy log data is returned as expected.
     */
    public function test_videofolder_updated() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $videofolder = $this->getDataGenerator()->create_module('videofolder', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($videofolder->cmid),
            'objectid' => $videofolder->id,
            'courseid' => $course->id
        );
        $event = \mod_videofolder\event\videofolder_updated::create($params);
        $event->add_record_snapshot('videofolder', $videofolder);

        // Trigger and capturing the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_videofolder\event\videofolder_updated', $event);
        $this->assertEquals(context_module::instance($videofolder->cmid), $event->get_context());
        $this->assertEquals($videofolder->id, $event->objectid);
        $expected = array($course->id, 'videofolder', 'edit', 'edit.php?id=' . $videofolder->cmid, $videofolder->id, $videofolder->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    /**
     * Test the videofolder updated event.
     *
     * There is no external API for updating a videofolder, so the unit test will simply create
     * and trigger the event and ensure the legacy log data is returned as expected.
     */
    public function test_all_files_downloaded() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $videofolder = $this->getDataGenerator()->create_module('videofolder', array('course' => $course->id));
        $context = context_module::instance($videofolder->cmid);
        $cm = get_coursemodule_from_id('videofolder', $videofolder->cmid, $course->id, true, MUST_EXIST);

        $sink = $this->redirectEvents();
        videofolder_downloaded($videofolder, $course, $cm, $context);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_videofolder\event\all_files_downloaded', $event);
        $this->assertEquals(context_module::instance($videofolder->cmid), $event->get_context());
        $this->assertEquals($videofolder->id, $event->objectid);
        $expected = array($course->id, 'videofolder', 'edit', 'edit.php?id=' . $videofolder->cmid, $videofolder->id, $videofolder->cmid);
        $this->assertEventContextNotUsed($event);
    }
}
