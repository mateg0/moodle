<?php

defined('MOODLE_INTERNAL') || die();

class mod_jitsi_observer {

    /**
     * Observer for \core\event\course_created event.
     *
     * @param \core\event\course_created $event
     * @return void | bool
     * @throws dml_exception
     */
    public static function course_created(\core\event\course_created $event) {
        global $CFG, $DB, $OUTPUT;

        $course = $event->get_record_snapshot('course', $event->objectid);
        $courseid = $event->objectid;
        $format = course_get_format($course);
        if ($format->supports_news() && !empty($course->newsitems)) {
            require_once($CFG->dirroot . '/mod/jitsi/lib.php');

            $jitsiModuleId = $DB -> get_field("modules", "id", ["name" => "jitsi"]);

            $jitsi = new stdClass();
            $jitsi -> course = $courseid;
            $jitsi -> coursemodule = $jitsiModuleId;
            $jitsi -> name = get_string('defaultcoursemodulename', 'jitsi');
            $jitsi -> intro = "";
            $jitsi -> id = jitsi_add_instance($jitsi);

            $mod = new stdClass();
            $mod -> id = 0;
            $mod -> course = $courseid;
            $mod -> module = $jitsiModuleId;
            $mod -> instance = $jitsi -> id;
            $mod -> section = 0;

            require_once($CFG->dirroot.'/course/lib.php');
            if (! $mod->coursemodule = add_course_module($mod) ) {
                echo $OUTPUT->notification("Could not add a new course module to the course '" . $courseid . "'");
                return false;
            }

            course_add_cm_to_section($courseid, $mod->coursemodule, 0);


        }

    }
}
