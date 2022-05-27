<?php
namespace local_startpage\output;
defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
class renderer extends plugin_renderer_base {
    public function renderStartPage(startpage $startpage){
        return $this->render_from_template('local_startpage/startpage', $startpage->export_for_template($this));
    }
}