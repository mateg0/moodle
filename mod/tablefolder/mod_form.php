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
 * tablefolder configuration form
 *
 * @package   mod_tablefolder
 * @copyright 2009 Petr Skoda  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_tablefolder_mod_form extends moodleform_mod {
    function definition() {
        global $CFG;
        $mform = $this->_form;

        $config = get_config('tablefolder');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        //-------------------------------------------------------
        $mform->addElement('header', 'content', get_string('contentheader', 'tablefolder'));
        $mform->addElement('filemanager', 'files', get_string('files'), null, array('subdirs'=>1, 'accepted_types'=>['.csv', '.xls', '.xlsx', '.ods']));
        $mform->addElement('select', 'display', get_string('display', 'mod_tablefolder'),
                array(tablefolder_DISPLAY_PAGE => get_string('displaypage', 'mod_tablefolder'),
                    tablefolder_DISPLAY_INLINE => get_string('displayinline', 'mod_tablefolder')));
        $mform->addHelpButton('display', 'display', 'mod_tablefolder');
        if (!$this->courseformat->has_view_page()) {
            $mform->setConstant('display', tablefolder_DISPLAY_PAGE);
            $mform->hardFreeze('display');
        }
        $mform->setExpanded('content');

        // Adding option to show sub-tablefolders expanded or collapsed by default.
        $mform->addElement('hidden', 'showexpanded', $config->showexpanded);
        // $mform->addElement('advcheckbox', 'showexpanded', get_string('showexpanded', 'tablefolder'));
        // $mform->addHelpButton('showexpanded', 'showexpanded', 'mod_tablefolder');
        // $mform->setDefault('showexpanded', $config->showexpanded);

        // Adding option to enable downloading archive of tablefolder.
        $mform->addElement('hidden', 'showdownloadtablefolder', true);
        // $mform->addElement('advcheckbox', 'showdownloadtablefolder', get_string('showdownloadtablefolder', 'tablefolder'));
        // $mform->addHelpButton('showdownloadtablefolder', 'showdownloadtablefolder', 'mod_tablefolder');
        // $mform->setDefault('showdownloadtablefolder', true);

        // Adding option to enable viewing of individual files.
        $mform->addElement('hidden', 'forcedownload', false);
        // $mform->addElement('advcheckbox', 'forcedownload', get_string('forcedownload', 'tablefolder'));
        // $mform->addHelpButton('forcedownload', 'forcedownload', 'mod_tablefolder');
        // $mform->setDefault('forcedownload', true);

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            // editing existing instance - copy existing files into draft area
            $draftitemid = file_get_submitted_draft_itemid('files');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_tablefolder', 'content', 0, array('subdirs'=>true));
            $default_values['files'] = $draftitemid;
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Completion: Automatic on-view completion can not work together with
        // "display inline" option
        if (empty($errors['completion']) &&
                array_key_exists('completion', $data) &&
                $data['completion'] == COMPLETION_TRACKING_AUTOMATIC &&
                !empty($data['completionview']) &&
                $data['display'] == tablefolder_DISPLAY_INLINE) {
            $errors['completion'] = get_string('noautocompletioninline', 'mod_tablefolder');
        }

        return $errors;
    }
}
