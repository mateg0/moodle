<?php
namespace local_onlineclassmates\output;
defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
class renderer extends plugin_renderer_base {
    public function renderOnlineClassmates(onlineclassmates $onlineclassmates){
        return $this->render_from_template('local_onlineclassmates/onlineclassmates', $onlineclassmates->export_for_template($this));
    }

    public function renderOnlineClassmatesMini(onlineclassmates $onlineclassmates){
        return $this->render_from_template('local_onlineclassmates/onlineclassmates_mini', $onlineclassmates->export_for_template($this));
    }
}
