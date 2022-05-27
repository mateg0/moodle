<?php
global $PAGE, $CFG;

require_once('../config.php');

if (isloggedin() && !isguestuser()) {
    redirect($CFG->wwwroot . '/my');
}

$startpage = new \local_startpage\output\startpage();

$startpageRenderer = $PAGE->get_renderer('local_startpage');

echo $startpageRenderer->renderStartPage($startpage);