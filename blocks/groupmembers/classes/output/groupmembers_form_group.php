<?php

namespace block_groupmembers\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class groupmembers_form_group implements renderable, templatable {

    private $groupid;
    private $groupimage;
    private $groupname;
    private $groups;

    public function export_for_template(renderer_base $output){
        return [
            'groups' => $this->groups
        ];
    }

    public function __construct($courseid, $groups)
    {
        foreach ($groups as $group) {
            $this->groupid = $group->id;
            $this->groupname = $group->name;
            $this->groupimage = get_group_picture_url($group, $courseid);
            //$this->console_log(get_group_picture_url($courseid, $group));
            $this->set_groups_array($this->groups);
        }
    }

    private function set_groups_array(&$array)
    {
        $array[] = array(
            'groupid' => $this->groupid,
            'groupimage' => $this->groupimage,
            'groupname' => $this->groupname
        );
    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
