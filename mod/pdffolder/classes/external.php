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
 * pdffolder external API
 *
 * @package    mod_pdffolder
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * pdffolder external functions
 *
 * @package    mod_pdffolder
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_pdffolder_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_pdffolder_parameters() {
        return new external_function_parameters(
            array(
                'pdffolderid' => new external_value(PARAM_INT, 'pdffolder instance id')
            )
        );
    }

    /**
     * Simulate the pdffolder/view.php web interface page: trigger events, completion, etc...
     *
     * @param int $pdffolderid the pdffolder instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_pdffolder($pdffolderid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/pdffolder/lib.php");

        $params = self::validate_parameters(self::view_pdffolder_parameters(),
                                            array(
                                                'pdffolderid' => $pdffolderid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $pdffolder = $DB->get_record('pdffolder', array('id' => $params['pdffolderid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($pdffolder, 'pdffolder');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/pdffolder:view', $context);

        // Call the page/lib API.
        pdffolder_view($pdffolder, $course, $cm, $context);

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
    public static function view_pdffolder_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_pdffolders_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_pdffolders_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of pdffolders in a provided list of courses.
     * If no list is provided all pdffolders that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and pdffolders
     * @since Moodle 3.3
     */
    public static function get_pdffolders_by_courses($courseids = array()) {

        $warnings = array();
        $returnedpdffolders = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_pdffolders_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the pdffolders in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $pdffolders = get_all_instances_in_courses("pdffolder", $courses);
            foreach ($pdffolders as $pdffolder) {
                $context = context_module::instance($pdffolder->coursemodule);
                // Entry to return.
                $pdffolder->name = external_format_string($pdffolder->name, $context->id);

                $options = array('noclean' => true);
                list($pdffolder->intro, $pdffolder->introformat) =
                    external_format_text($pdffolder->intro, $pdffolder->introformat, $context->id, 'mod_pdffolder', 'intro', null, $options);
                $pdffolder->introfiles = external_util::get_area_files($context->id, 'mod_pdffolder', 'intro', false, false);

                $returnedpdffolders[] = $pdffolder;
            }
        }

        $result = array(
            'pdffolders' => $returnedpdffolders,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_pdffolders_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_pdffolders_by_courses_returns() {
        return new external_single_structure(
            array(
                'pdffolders' => new external_multiple_structure(
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
                            'timemodified' => new external_value(PARAM_INT, 'Last time the pdffolder was modified'),
                            'display' => new external_value(PARAM_INT, 'Display type of pdffolder contents on a separate page or inline'),
                            'showexpanded' => new external_value(PARAM_INT, '1 = expanded, 0 = collapsed for sub-pdffolders'),
                            'showdownloadpdffolder' => new external_value(PARAM_INT, 'Whether to show the download pdffolder button'),
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
