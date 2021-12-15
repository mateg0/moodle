<?php

namespace block_adduser\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class adduser implements renderable, templatable
{

    public function export_for_template(renderer_base $output)
    {
        global $CFG;
        $link = $CFG->wwwroot . '/user/editadvanced.php?id=-1';
        return [
            'link' => $link
        ];
    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
