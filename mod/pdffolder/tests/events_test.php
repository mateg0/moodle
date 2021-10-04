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
 * @package    mod_pdffolder
 * @category   test
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_pdffolder_events_testcase extends advanced_testcase {

    /**
     * Tests set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test the pdffolder updated event.
     *
     * There is no external API for updating a pdffolder, so the unit test will simply create
     * and trigger the event and ensure the legacy log data is returned as expected.
     */
    public function test_pdffolder_updated() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $pdffolder = $this->getDataGenerator()->create_module('pdffolder', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($pdffolder->cmid),
            'objectid' => $pdffolder->id,
            'courseid' => $course->id
        );
        $event = \mod_pdffolder\event\pdffolder_updated::create($params);
        $event->add_record_snapshot('pdffolder', $pdffolder);

        // Trigger and capturing the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_pdffolder\event\pdffolder_updated', $event);
        $this->assertEquals(context_module::instance($pdffolder->cmid), $event->get_context());
        $this->assertEquals($pdffolder->id, $event->objectid);
        $expected = array($course->id, 'pdffolder', 'edit', 'edit.php?id=' . $pdffolder->cmid, $pdffolder->id, $pdffolder->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    /**
     * Test the pdffolder updated event.
     *
     * There is no external API for updating a pdffolder, so the unit test will simply create
     * and trigger the event and ensure the legacy log data is returned as expected.
     */
    public function test_all_files_downloaded() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $pdffolder = $this->getDataGenerator()->create_module('pdffolder', array('course' => $course->id));
        $context = context_module::instance($pdffolder->cmid);
        $cm = get_coursemodule_from_id('pdffolder', $pdffolder->cmid, $course->id, true, MUST_EXIST);

        $sink = $this->redirectEvents();
        pdffolder_downloaded($pdffolder, $course, $cm, $context);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_pdffolder\event\all_files_downloaded', $event);
        $this->assertEquals(context_module::instance($pdffolder->cmid), $event->get_context());
        $this->assertEquals($pdffolder->id, $event->objectid);
        $expected = array($course->id, 'pdffolder', 'edit', 'edit.php?id=' . $pdffolder->cmid, $pdffolder->id, $pdffolder->cmid);
        $this->assertEventContextNotUsed($event);
    }
}
