<?php

namespace block_groupstats\form;
defined('MOODLE_INTERNAL') || die();

use moodleform;

global $CFG;

require_once("$CFG->libdir/formslib.php");

class courcegroupform extends moodleform
{

    public function definition()
    {
        global $PAGE, $DB;

        $lastcourseteacher = new \block_groupmembers\course\last_course_teacher();

        $lastcourses = $lastcourseteacher->get_last_acessed_courses_from_log();
        $enrolledcourses = enrol_get_my_courses();
        $lastcourceid = $lastcourseteacher->get_last_course_teacher_enrolled($lastcourses, $enrolledcourses);

        $mform = $this->_form; // Don't forget the underscore!
        $mform->disable_form_change_checker();
        $mform->addElement('html', '<div class="gs-cs-form-header">');
        $mform->addElement('html', '<h5>Статистика группы</h5>');
        $mform->addElement('html', '<div class="gs-cs-form-holder">');
        $mform->addElement('hidden', 'gs-csgm_groupid');
        $mform->setType('groupid', PARAM_INT);
        $mform->setDefault('groupid', -1);

        $template = new \block_groupstats\output\groupstats_form_course($lastcourceid);
        $renderer = $PAGE->get_renderer('block_groupstats');
        $courseselect = $renderer->renderGroupStatsFormCourse($template);
        $mform->addElement('html', $courseselect);

        $mform->addElement('html', '<div id="gs-cs-groups-holder">');

        $template = new \block_groupstats\output\groupstats_form_group(-1, -1);
        $renderer = $PAGE->get_renderer('block_groupstats');
        $groupselect = $renderer->renderGroupStatsFormGroup($template);
        $mform->addElement('html', $groupselect);

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
    }

    //Custom validation should be added here
    function validation($data, $files)
    {
        return array();
    }
}
