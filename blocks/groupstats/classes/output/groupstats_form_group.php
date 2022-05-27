<?php

namespace block_groupstats\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class groupstats_form_group implements renderable, templatable {

    private $groupid;
    private $groupimage;
    private $groupname;
    private $groups;
    private $searchgroups = false;
    private $colorindex;

    public function export_for_template(renderer_base $output){
        if(count($this->groups) > 0) {
            $this->searchgroups = true;
        }
        return [
            'searchgroups' =>  $this->searchgroups,
            'groups' => $this->groups
        ];
    }

    public function __construct($courseid, $groups)
    {
        foreach ($groups as $group) {
            $this->colorindex = rand(1, 10);
            $this->groupid = $group->id;
            $this->groupname = $group->name;
            $this->groupimage = get_group_picture_url($group, $courseid);
            if($this->groupimage === ""){
                $this->groupimage = false;
            }
            $this->set_groups_array($this->groups);
        }
    }

    private function set_groups_array(&$array)
    {
        $array[] = array(
            'groupid' => $this->groupid,
            'groupimage' => $this->groupimage,
            'groupname' => $this->groupname,
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
