<?php

namespace block_groupmembers\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class groupmembers_blank implements renderable, templatable {


    public function export_for_template(renderer_base $output){
        //$imageurl = $output->image_url('blank_diagram', 'block_groupstats')->out();
        return [
            //'imageurl' => $imageurl,
            'info' => "this is blank. select values to render stats"
        ];
    }

    public function __construct()
    {

    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
