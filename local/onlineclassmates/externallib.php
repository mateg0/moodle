<?php
defined('MOODLE_INTERNAL') || die();


class onlineclassmates_external extends external_api
{

    public static function update_onlineclassmates($id)
    {
        global $PAGE;
        $onlineclassmates = new \local_onlineclassmates\output\onlineclassmates();
        $studentHeaderRenderer = $PAGE->get_renderer('local_onlineclassmates');

        return $studentHeaderRenderer->renderOnlineClassmates($onlineclassmates);
    }

    public static function update_onlineclassmates_parameters()
    {
        return new external_function_parameters(array(
            'id'=> new external_value(PARAM_INT, "sample param")
        ));
    }

    public static function update_onlineclassmates_returns()
    {
        return new external_value(PARAM_RAW, 'The updated template output');
    }
}