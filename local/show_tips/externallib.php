<?php
defined('MOODLE_INTERNAL') || die();


class show_tips_external extends external_api
{

    public static function remove_old_preferences($id)
    {
        global $DB, $USER;

        $sql = 'DELETE FROM mdl_user_preferences WHERE userid='. $USER->id .' AND name LIKE "tool_usertours_tour_completion_time%"';

        try {
            $DB->execute($sql);
        } catch (dml_exception $e) {
            return false;
        }

        return true;
    }

    public static function remove_old_preferences_parameters()
    {
        return new external_function_parameters(array(
            'empty' => new external_value(PARAM_RAW, "sample param")
        ));
    }

    public static function remove_old_preferences_returns()
    {
        return new external_value(PARAM_BOOL, 'Delete complete status');
    }
}