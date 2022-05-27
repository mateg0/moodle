<?php
namespace local_studentachievements\output;
defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
class renderer extends plugin_renderer_base {
    public function renderStudentsAchievements(studentachievements $studentachievements){
        return $this->render_from_template('local_studentachievements/studentachievements', $studentachievements->export_for_template($this));
    }

    public function renderStudentsAchievementsMini(studentachievements $studentachievements){
        return $this->render_from_template('local_studentachievements/studentachievements_mini', $studentachievements->export_for_template($this));
    }
}
