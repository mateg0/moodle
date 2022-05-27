<?php

namespace local_onlineclassmates\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use user_picture;

class onlineclassmates implements renderable, templatable
{
    private $onlinestudentscount = 0;
    private $onlinestudents = [];
    private $usergroups = [];
    private $usergroupcount = 0;
    private $courseid = 0;
    private $usercourses = [];
    private $usercoursescount = 0;

    public function export_for_template(renderer_base $output)
    {
        global $PAGE;

        $imageurl = $output->image_url('spider', 'local_onlineclassmates')->out();

        if ($this->$onlinestudentscount === 0) {
            $this->$onlinestudentscount = false;
        }

        $templateData = [
            "onlinestudentscount" => $this->onlinestudentscount,
            "onlinestudents" => $this->onlinestudents,
            "usergroupcount" => $this->usergroupcount,
            "courseid" => $this->courseid,
            'imageurl' => $imageurl,
            'usercoursescount' => $this->usercoursescount
        ];

        return $templateData;
    }

    public function __construct()
    {
        global $PAGE, $CFG, $USER, $DB;

        if($PAGE->course->id == SITEID){
            $sql = 'SELECT * FROM {logstore_standard_log} WHERE ';
            $sql .= 'action = ?  AND target = ? AND userid = ? AND courseid != 1 ';
            $sql .= 'order by timecreated desc';
            $lastcourse = $DB->get_records_sql($sql, array('viewed', 'course', $USER->id),0, 1);
            $course = reset($lastcourse);
            $this->courseid = $course->courseid;
        }else{
            $this->courseid = $PAGE->course->id;
        }

        $this->usercourses = enrol_get_my_courses();
        $this->usercoursescount = count($this->usercourses);

        /*
        foreach($this->usercourses as $usercourse){
            array_push($this->usergroups, groups_get_all_groups($usercourse->courseid, $USER->id));
        }
        */

        $this->usergroups = groups_get_all_groups($this->courseid, $USER->id);
        $this->usergroupcount = count($this->usergroups);

        $timetoshowusers = 90; //Seconds default 
        $now = time();

        
        //Calculate if we are in separate groups
        $isseparategroups = ($PAGE->course->groupmode == SEPARATEGROUPS
            && $PAGE->course->groupmodeforce
            && !has_capability('moodle/site:accessallgroups', $PAGE->context));

        //Get the user current group
        $currentgroup = $isseparategroups ? groups_get_course_group($PAGE->course) : NULL;
        


        $sitelevel = $PAGE->course->id == SITEID || $PAGE->context->contextlevel < CONTEXT_COURSE;

        $onlineusers = new fetcher($currentgroup, $now, $timetoshowusers, $PAGE->context, $sitelevel, $PAGE->course->id);

        $userlimit = 50; // We'll just take the most recent 50 maximum.
        if ($users = $onlineusers->get_users($userlimit)) {

            foreach ($this->usergroups as $usergroup) {
                $groupmembers = groups_get_members($usergroup->id);
                foreach ($users as $user) {
                    foreach ($groupmembers as $groupmember) {
                        if ($user->id === $groupmember->id && $groupmember->id !== $USER->id) {
                            $colorindex = rand(1, 10);
                            $picturesrc = $this->get_user_image($user);
                            $userfullname = $user->firstname . ' ' . $user->lastname;
                            $userprofile = $CFG->wwwroot . '/user/view.php?id=' . $user->id . '&amp;course=' . $this->course->id;
                            $this->onlinestudents[] = array(
                                'name' => $userfullname,
                                'picturesrc' => $picturesrc,
                                'userprofile' => $userprofile,
                                'colorindex' => $colorindex
                            );
                        }
                    }
                }
            }
        }
        $this->onlinestudentscount = count($this->onlinestudents);
    }

    private function get_user_image($user) {
        global $USER,$PAGE; 
        $user_picture=new user_picture($user);
        if($user->picture){
            $src=$user_picture->get_url($PAGE);
        }
        else{
            $src=$user_picture->get_url($PAGE, null,  false);
        }
        return $src;
    }
}