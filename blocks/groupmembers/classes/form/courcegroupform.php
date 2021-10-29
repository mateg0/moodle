<?php

namespace block_groupmembers\form;
defined('MOODLE_INTERNAL') || die();

use core\event\role_assigned;
use moodleform;

global $CFG;

require_once("$CFG->libdir/formslib.php");

class courcegroupform extends moodleform
{

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function definition()
    {
        global $PAGE;

        $lastcourseteacher = new \block_groupmembers\course\last_course_teacher();

        $lastcourses = $lastcourseteacher->get_last_acessed_courses_from_log();
        $enrolledcourses = enrol_get_my_courses();
        $lastcourceid = $lastcourseteacher->get_last_course_teacher_enrolled($lastcourses, $enrolledcourses);

        $mform = $this->_form; // Don't forget the underscore!
        $mform->disable_form_change_checker();
        $mform->addElement('html', '<div class="cs-form-holder">');
        $mform->addElement('html', '<h5 style="max-width: 300px; margin: 0 auto;">Состав группы</h5>');
        $mform->addElement('hidden', 'csgm_groupid');
        $mform->setType('groupid', PARAM_INT);
        $mform->setDefault('groupid', -1);
        $template = new \block_groupmembers\output\groupmembers_form_course($lastcourceid);
        $renderer = $PAGE->get_renderer('block_groupmembers');

        $courseselect = $renderer->renderGroupMembersFormCourse($template);

        $mform->addElement('html', $courseselect);
        $mform->addElement('html', '<div id="cs-groups-holder">');
        $template = new \block_groupmembers\output\groupmembers_form_group(-1, -1);
        $renderer = $PAGE->get_renderer('block_groupmembers');

        $groupselect = $renderer->renderGroupMembersFormGroup($template);

        $mform->addElement('html', $groupselect);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
    }

    //Custom validation should be added here
    function validation($data, $files)
    {
        return array();
    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
