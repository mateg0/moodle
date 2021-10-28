<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External Web Service Template
 *
 * @package    Externallib
 * Developer: 2020 Ricoshae Pty Ltd (http://ricoshae.com.au)
 */


require_once($CFG->libdir . "/externallib.php");

class ajax_groups_external extends external_api
{
    /**
     * Returns welcome message
     * @return array = array('' => , ); welcome message
     */
    public static function getgroups($courseid)
    {   global $PAGE;
        $groups = self::get_groups_by_cource_id($courseid);
        $template = new \block_groupmembers\output\groupmembers_form_group($courseid, $groups);
        $renderer = $PAGE->get_renderer('block_groupmembers');
        return $renderer->renderGroupMembersFormGroup($template);
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function getgroups_parameters()
    {
        return new external_function_parameters(
          array("courseid" => new external_value(PARAM_INT, "course id"))
        );
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function getgroups_returns()
    {
        return new external_value(PARAM_RAW, 'The updated template output');
//        return new external_value();//new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }

    private static function get_groups_by_cource_id($id,  $fields = 'g.*')
    {
        global $DB;
        return $DB->get_records_sql("SELECT $fields
                                   FROM {groups} g, {course} c
                                  WHERE g.courseid = c.id AND c.id = ?
                               ORDER BY name ASC", array($id));
    }
}

class ajax_groupmembers_external extends external_api{
    /**
     * @throws coding_exception
     */
    public static function getgroupmembers($groupid){
        global $PAGE;
        $template = new \block_groupmembers\output\groupmembers($groupid);
        $renderer = $PAGE->get_renderer('block_groupmembers');
        return $renderer->renderGroupMembers($template);
    }

    public static function getgroupmembers_parameters()
    {
        return new external_function_parameters(
            array("groupid" => new external_value(PARAM_INT, "group id"))
        );
    }

    public static function getgroupmembers_returns()
    {
        return new external_value(PARAM_RAW, 'The updated template output');
    }
}