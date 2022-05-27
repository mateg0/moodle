<?php

namespace block_groupmembers\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use user_picture;

class groupmembers implements renderable, templatable
{
    private $link;
    private $groupmembers;
    private $groupmemberscount;
    private $picturesrc;
    private $userfullname;
    private $userprofile;
    private $students;
    private $colorindex;

    public function export_for_template(renderer_base $output){
        $imageurl = $output->image_url('alpha', 'block_groupmembers')->out();
        return [
            'link' => $this->link,
            'alpha' => $imageurl,
            'students' => $this->students
        ];
    }

    public function __construct($groupid)
    {
        global $CFG;
        $this->link = $CFG->wwwroot . '/group/members.php?group=' . $groupid;
        $this->groupmembers = groups_get_members($groupid);
        $this->groupmemberscount = count($this->groupmembers);
        $this->collect_data();
    }

    private function collect_data()
    {
        global $CFG, $PAGE;
        $users = $this->groupmembers;

        foreach ($users as $user) {
            $this->colorindex = rand(1, 10);
            $this->picturesrc = $this->get_user_image($user);
            $this->userfullname = $user->firstname . ' ' . $user->lastname;
            $this->userprofile = $CFG->wwwroot . '/user/view.php?id=' . $user->id . '&amp;course=' . $this->course->id;
            $this->set_user_array($this->students);
        }
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

    private function set_user_array(&$array)
    {
        $array[] = array(
            'fullname' => $this->userfullname,
            'picturesrc' => $this->picturesrc,
            'userprofile' => $this->userprofile,
            'colorindex' => $this->colorindex
        );
    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
