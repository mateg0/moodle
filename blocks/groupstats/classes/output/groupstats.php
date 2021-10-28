<?php

namespace block_groupstats\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use user_picture;

class groupstats implements renderable, templatable
{
    private $groupmembers;
    private $groupmemberscount;

    public function __construct($groupid)
    {
        $this->groupmembers = groups_get_members($groupid);
        $this->groupmemberscount = count($this->groupmembers);
        $this->collect_data();
        $this->count_data();
    }

    private $picturesrc;
    private $userfullname;
    private $userprofile;

    private $test;

    private function collect_data()
    {
        global $CFG, $PAGE;
        $users = $this->groupmembers;

        foreach ($users as $user) {
            $user_picture = new user_picture($user);
            $this->picturesrc = $user_picture->get_url($PAGE);
            $this->userfullname = $user->firstname . ' ' . $user->lastname;
            $this->userprofile = $CFG->wwwroot . '/user/view.php?id=' . $user->id . '&amp;course=' . $this->course->id;
            $this->get_student_performance($user);
            $this->get_student_attendance($user);
            $this->get_student_payment($user);
            //test: collect all students
            $this->set_user_array($this->test);
        }
    }

    /**
     *
     * Grades statistics
     *
     **/
    private $wellstudents;
    private $goodstudents;
    private $okaystudents;
    private $badstudents;

    private function get_student_performance($student)
    {
        $grades = $this->get_student_grades_by_student_id($student->id);
        $total = array_shift($grades);
        $gradetotal = $total->finalgrade / $total->rawgrademax;

        //based on 100 points
        if ($gradetotal < 0.65) {
            $this->set_user_array($this->badstudents);
        } elseif ($gradetotal >= 0.65 && $gradetotal < 0.75) {
            $this->set_user_array($this->okaystudents);
        } elseif ($gradetotal >= 0.75 && $gradetotal < 0.85) {
            $this->set_user_array($this->goodstudents);
        } elseif ($gradetotal > 0.85) {
            $this->set_user_array($this->wellstudents);
        }
    }

    private function get_student_grades_by_student_id($studentid, $fields = 'g.*')
    {
        global $DB;
        return $DB->get_records_sql("SELECT $fields
                                   FROM {grade_grades} g, {user} u
                                  WHERE g.userid = u.id AND u.id = ?
                               ORDER BY finalgrade DESC", array($studentid));
    }

    /**
     *
     * Attending statistics
     *
     **/
    private $wellattendingstudents;
    private $goodattendingstudents;
    private $okayattendingstudents;
    private $badattendingstudents;

    private function get_student_attendance($student)
    {
        $this->set_user_array($this->wellattendingstudents);
    }

    /**
     *
     * Payment statistics
     *
     **/
    private $paystudents;
    private $didntpaystudents;

    private function get_student_payment($student)
    {
        $this->set_user_array($this->paystudents);
    }

    /**
     *
     * Data exports
     *
     **/
    public function export_for_template(renderer_base $output)
    {
        return [
            'groupmemberscount' => $this->groupmemberscount,

            'wellstudentscount' => $this->wellstudentscount,
            'goodstudentscount' => $this->goodstudentscount,
            'okaystudentscount' => $this->okaystudentscount,
            'badstudentscount' => $this->badstudentscount,

            'wellattendingstudentscount' => $this->wellattendingstudentscount,
            'goodattendingstudentscount' => $this->goodattendingstudentscount,
            'okayattendingstudentscount' => $this->okayattendingstudentscount,
            'badattendingstudentscount' => $this->badattendingstudentscount,

            'paystudentscount' => $this->paystudentscount,
            'didntpaystudentscount' => $this->didntpaystudentscount,

            'wellstudents' => $this->wellstudents,
            'goodstudents' => $this->goodstudents,
            'okaystudents' => $this->okaystudents,
            'badstudents' => $this->badstudents,

            'wellattendingstudents' => $this->wellattendingstudents,
            'goodattendingstudents' => $this->goodattendingstudents,
            'okayattendingstudents' => $this->okayattendingstudents,
            'badattendingstudents' => $this->badattendingstudents,

            'paystudents' => $this->paystudents,
            'didntpaystudents' => $this->didntpaystudents
        ];
    }

    public function export_for_js()
    {
        return [
            'groupmemberscount' => $this->groupmemberscount,

            'wellstudentscount' => $this->wellstudentscount,
            'goodstudentscount' => $this->goodstudentscount,
            'okaystudentscount' => $this->okaystudentscount,
            'badstudentscount' => $this->badstudentscount,

            'wellattendingstudentscount' => $this->wellattendingstudentscount,
            'goodattendingstudentscount' => $this->goodattendingstudentscount,
            'okayattendingstudentscount' => $this->okayattendingstudentscount,
            'badattendingstudentscount' => $this->badattendingstudentscount,

            'paystudentscount' => $this->paystudentscount,
            'didntpaystudentscount' => $this->didntpaystudentscount
        ];
    }

    /**
     *
     * Counters
     *
     **/
    private $wellstudentscount = 0;
    private $goodstudentscount = 0;
    private $okaystudentscount = 0;
    private $badstudentscount = 0;

    private $wellattendingstudentscount = 0;
    private $goodattendingstudentscount = 0;
    private $okayattendingstudentscount = 0;
    private $badattendingstudentscount = 0;

    private $paystudentscount = 0;
    private $didntpaystudentscount = 0;

    private function count_data()
    {
        $this->wellstudentscount = count($this->wellstudents);
        $this->goodstudentscount = count($this->goodstudents);
        $this->okaystudentscount = count($this->okaystudents);
        $this->badstudentscount = count($this->badstudents);

        $this->wellattendingstudentscount = count($this->wellattendingstudents);
        $this->goodattendingstudentscount = count($this->goodattendingstudents);
        $this->okayattendingstudentscount = count($this->okayattendingstudents);
        $this->badattendingstudentscount = count($this->badattendingstudents);

        $this->paystudentscount = count($this->paystudents);
        $this->didntpaystudentscount = count($this->didntpaystudents);
    }

    /**
     *
     * Other methods
     *
     **/
    private function set_user_array(&$array)
    {
        $array[] = array(
            'fullname' => $this->userfullname,
            'picturesrc' => $this->picturesrc,
            'userprofile' => $this->userprofile
        );
    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
