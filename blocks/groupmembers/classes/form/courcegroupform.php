<?php

namespace block_groupmembers\form;
defined('MOODLE_INTERNAL') || die();

use moodleform;

global $CFG;

require_once("$CFG->libdir/formslib.php");

class courcegroupform extends moodleform
{

    public function definition()
    {
        global $PAGE, $DB;

        $mform = $this->_form; // Don't forget the underscore!
        $mform->disable_form_change_checker();
	$mform->addElement('html', '<div class="cs-form-holder">');
        $mform->addElement('html', '<h5 style="max-width: 300px; margin: 0 auto;">Состав группы</h5>');
        $mform->addElement('hidden', 'csgm_groupid');
        $mform->setType('groupid', PARAM_INT);
        $mform->setDefault('groupid', -1);

        $template = new \block_groupmembers\output\groupmembers_form_course();
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
}
