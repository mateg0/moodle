<?php
namespace block_groupstats\output;
defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
class renderer extends plugin_renderer_base {
    public function renderGroupStats(groupstats $groupstats){
        return $this->render_from_template('block_groupstats/groupstats_template', $groupstats->export_for_template($this));
    }

    public function renderGroupStatsFormCourse(groupstats_form_course $groupstatsformcourse){
        return $this->render_from_template('block_groupstats/groupstats_form_template', $groupstatsformcourse->export_for_template($this));
    }

    public function renderGroupStatsFormGroup(groupstats_form_group $groupstatsformcourse){
        return $this->render_from_template('block_groupstats/groupstats_group_form_template', $groupstatsformcourse->export_for_template($this));
    }

    public function renderGroupStatsBlank(groupstats_blank $groupstatsformcourse){
        return $this->render_from_template('block_groupstats/groupstats_blank_template', $groupstatsformcourse->export_for_template($this));
    }
}
