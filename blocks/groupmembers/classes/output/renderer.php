<?php
namespace block_groupmembers\output;
defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
class renderer extends plugin_renderer_base {
    public function renderGroupMembers(groupmembers $groupmembers){
        return $this->render_from_template('block_groupmembers/groupmembers_template', $groupmembers->export_for_template($this));
    }

    public function renderGroupMembersFormCourse(groupmembers_form_course $groupmembersformcourse){
        return $this->render_from_template('block_groupmembers/groupmembers_form_template', $groupmembersformcourse->export_for_template($this));
    }

    public function renderGroupMembersFormGroup(groupmembers_form_group $groupmembersformcourse){
        return $this->render_from_template('block_groupmembers/groupmembers_group_form_template', $groupmembersformcourse->export_for_template($this));
    }
}
