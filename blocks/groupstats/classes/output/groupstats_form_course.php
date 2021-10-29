<?php

namespace block_groupstats\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class groupstats_form_course implements renderable, templatable {

    private $courseid;
    private $coursename;
    private $courses;
    private $searchcource = true;
    private $lastcourseid;
    private $lastcoursename;

    public function export_for_template(renderer_base $output){
        if(count($this->courses) > 0 ){
            $this->searchcource = true;
        }
        return [
            'searchcource' => $this->searchcource,
            'courses' => $this->courses,
            'lastcourseid' => $this->lastcourseid,
            'lastcoursename' => $this->lastcoursename
        ];
    }

    public function __construct($lastcourseid)
    {
        $courses = enrol_get_my_courses();

        foreach ($courses as $course) {
            $this->courseid = $course->id;
            $this->coursename = $course->fullname;
            $this->set_courses_array($this->courses);
        }
        if ($lastcourseid > 0) {
            $course = get_course($lastcourseid);
            $this->lastcourseid = $lastcourseid;
            $this->lastcoursename = $course->fullname;
        } else {
            $this->lastcourseid = false;
            $this->lastcoursename = false;
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
