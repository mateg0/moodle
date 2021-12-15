<?php
namespace block_adduser\output;
defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
class renderer extends plugin_renderer_base {

    public function renderAddUser(adduser $adduser){
        return $this->render_from_template('block_adduser/adduser_template', $adduser->export_for_template($this));
    }
}
