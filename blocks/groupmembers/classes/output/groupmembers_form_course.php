<?php

namespace block_groupmembers\output;
defined('MOODLE_INTERNAL') || die();

use context_course;
use moodle_url;
use renderable;
use renderer_base;
use templatable;

class groupmembers_form_course implements renderable, templatable {

   
    private $courseid;
    private $coursename;
    private $courses;
    private $searchcource = false;
    private $lastcourseid;
    private $lastcoursename;
    private $coursesrc;
    private $colorindex;

    public function export_for_template(renderer_base $output) {
        if(count($this->courses) > 0 ){
            $this->searchcource = true;
        }
        return [
            'searchcource' => $this->searchcource,
            'courses' => $this->courses,
            'lastcourseid' => $this->lastcourseid,
            'lastcoursename' => $this->lastcoursename,
            'coursesrc' => $this->coursesrc,
            'colorindex' => $this->colorindex
        ];
    }

    public function __construct($lastcourseid)
    {
        $courses = enrol_get_my_courses();
        foreach ($courses as $course) {
            $this->colorindex = rand(1, 10);
            $this->courseid = $course->id;
            $this->coursename = $course->fullname;
            $this->coursesrc = $this->get_course_image($course);
            if($this->coursesrc === ""){
                $this->coursesrc = false;
            }
            $this->set_courses_array($this->courses);
        }
        if ($lastcourseid > 0) {
        $course = get_course($lastcourseid);
        $this->lastcourseid = $lastcourseid;
        $this->lastcoursename = $course->fullname;
        $this->colorindex = rand(1, 10);
        $this->coursesrc = $this->get_course_image($course);
            if($this->coursesrc === ""){
                $this->coursesrc = false;
            }
        } else {
            $this->lastcourseid = false;
            $this->lastcoursename = false;
            $this->coursesrc = false;
        }
    }

    private function set_courses_array(&$array)
    {
        $array[] = array(
            'courseid' => $this->courseid,
            'coursename' => $this->coursename,
            'coursesrc' => $this->coursesrc,
            'colorindex' => $this->colorindex
        );
    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }

    private function get_course_image($course)
    {
        global $CFG;
        $url = '';
        require_once( $CFG->libdir . '/filelib.php' );

        $context = context_course::instance( $course->id );
        $fs = get_file_storage();
        $files = $fs->get_area_files( $context->id, 'course', 'overviewfiles', 0 );

        foreach ( $files as $f )
        {
            if ( $f->is_valid_image() )
            {
                $url = moodle_url::make_pluginfile_url( $f->get_contextid(), $f->get_component(), $f->get_filearea(), null, $f->get_filepath(), $f->get_filename(), false );
            }
        }

        return $url;
    }
}
