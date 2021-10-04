<?php
// This file is part of Moodle - http://moodle.org/
//
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
 * picfolder external API
 *
 * @package    mod_picfolder
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * picfolder external functions
 *
 * @package    mod_picfolder
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_picfolder_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_picfolder_parameters() {
        return new external_function_parameters(
            array(
                'picfolderid' => new external_value(PARAM_INT, 'picfolder instance id')
            )
        );
    }

    /**
     * Simulate the picfolder/view.php web interface page: trigger events, completion, etc...
     *
     * @param int $picfolderid the picfolder instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_picfolder($picfolderid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/picfolder/lib.php");

        $params = self::validate_parameters(self::view_picfolder_parameters(),
                                            array(
                                                'picfolderid' => $picfolderid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $picfolder = $DB->get_record('picfolder', array('id' => $params['picfolderid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($picfolder, 'picfolder');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/picfolder:view', $context);

        // Call the page/lib API.
        picfolder_view($picfolder, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function view_picfolder_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_picfolders_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_picfolders_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of picfolders in a provided list of courses.
     * If no list is provided all picfolders that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and picfolders
     * @since Moodle 3.3
     */
    public static function get_picfolders_by_courses($courseids = array()) {

        $warnings = array();
        $returnedpicfolders = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_picfolders_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the picfolders in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $picfolders = get_all_instances_in_courses("picfolder", $courses);
            foreach ($picfolders as $picfolder) {
                $context = context_module::instance($picfolder->coursemodule);
                // Entry to return.
                $picfolder->name = external_format_string($picfolder->name, $context->id);

                $options = array('noclean' => true);
                list($picfolder->intro, $picfolder->introformat) =
                    external_format_text($picfolder->intro, $picfolder->introformat, $context->id, 'mod_picfolder', 'intro', null, $options);
                $picfolder->introfiles = external_util::get_area_files($context->id, 'mod_picfolder', 'intro', false, false);

                $returnedpicfolders[] = $picfolder;
            }
        }

        $result = array(
            'picfolders' => $returnedpicfolders,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_picfolders_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_picfolders_by_courses_returns() {
        return new external_single_structure(
            array(
                'picfolders' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Module id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'Page name'),
                            'intro' => new external_value(PARAM_RAW, 'Summary'),
                            'introformat' => new external_format_value('intro', 'Summary format'),
                            'introfiles' => new external_files('Files in the introduction text'),
                            'revision' => new external_value(PARAM_INT, 'Incremented when after each file changes, to avoid cache'),
                            'timemodified' => new external_value(PARAM_INT, 'Last time the picfolder was modified'),
                            'display' => new external_value(PARAM_INT, 'Display type of picfolder contents on a separate page or inline'),
                            'showexpanded' => new external_value(PARAM_INT, '1 = expanded, 0 = collapsed for sub-picfolders'),
                            'showdownloadpicfolder' => new external_value(PARAM_INT, 'Whether to show the download picfolder button'),
                            'forcedownload' => new external_value(PARAM_INT, 'Whether file download is forced'),
                            'section' => new external_value(PARAM_INT, 'Course section id'),
                            'visible' => new external_value(PARAM_INT, 'Module visibility'),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                            'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }
}
