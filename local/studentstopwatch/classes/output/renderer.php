<?php
namespace local_studentstopwatch\output;
defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
class renderer extends plugin_renderer_base {
    public function renderStudentStopwatch(studentstopwatch $studentstopwatch){
        return $this->render_from_template('local_studentstopwatch/studentstopwatch', $studentstopwatch->export_for_template($this));
    }

    public function renderStudentStopwatchMini(studentstopwatch $studentstopwatch){
        return $this->render_from_template('local_studentstopwatch/studentstopwatch_mini', $studentstopwatch->export_for_template($this));
    }
}
