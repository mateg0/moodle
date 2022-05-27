<?php
namespace local_studentachievements\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class studentachievements implements renderable, templatable{
    private $studentLevel = 1;
    public function export_for_template(renderer_base $output)
    {
        return [
            "studentlevel" => $this->studentLevel
        ];
    }

    public function __construct()
    {

    }
}