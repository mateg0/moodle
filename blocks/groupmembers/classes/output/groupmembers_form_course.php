<?php

namespace block_groupmembers\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class groupmembers_form_course implements renderable, templatable {

    private $courseid;
    private $coursename;
    private $courses;

    public function export_for_template(renderer_base $output){
        return [
            'courses' => $this->courses
        ];
    }

    public function __construct()
    {
        global $DB;
        $courses = $DB->get_records('course');

        foreach ($courses as $course) {
            $this->courseid = $course->id;
            $this->coursename = $course->fullname;
            $this->set_courses_array($this->courses);
        }
    }

    private function set_courses_array(&$array)
    {
        $array[] = array(
            'courseid' => $this->courseid,
            'coursename' => $this->coursename,
        );
    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
