<?php
namespace local_studentstopwatch\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use stdClass;
use templatable;

class studentstopwatch implements renderable, templatable{

    public $test = '<h1> WORKS 3 </h1>';

    public function export_for_template(renderer_base $output)
    {
        return[];
    }

    public function __construct()
    {

    }
}