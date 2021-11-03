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
 *
 * Demo part of future plugin for enrolment by hyperlink
 *
 * @package    enrol_hyperlink
 * @category   external
 * @copyright  sassless
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot.'/enrol/locallib.php'); // enrolment libs
require_once($CFG->dirroot.'/group/lib.php'); // group libs

// require_login();

$groupid = required_param('id', PARAM_INT);
$group = $DB->get_record('groups', array('id'=>$groupid), '*', MUST_EXIST);
$courseid = $group->courseid;
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$enrol = $DB->get_record('enrol', array('courseid'=>$courseid, 'enrol'=>'manual'), '*', MUST_EXIST);
$enrolid = $enrol->id;
$userid = $USER->id;
// current hard binds -->
$roleid = 5; // Student role

if ($userid > 1) {
    // ENROLMENT PREPARE -->
    $manager = new course_enrolment_manager($PAGE, $course);
    $instances = $manager->get_enrolment_instances();
    $instance = $instances[$enrolid];
    $plugins = $manager->get_enrolment_plugins(true); // Do not allow actions on disabled plugins.
    $plugin = $plugins[$instance->enrol];
    $today = time();
    $timestart = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
    // ENROLMENT -->
    $plugin->enrol_user($instance, $userid, $roleid, $timestart, 0, null, 0);
    // ADDING TO GROUP -->
    $result = groups_add_member($groupid, $userid);
    // FINAL REDIRECT -->
    $courseurl = new moodle_url('/course/view.php', array('id'=>$courseid));
    redirect($courseurl);
} else {
    $url = new moodle_url('/group/enrolme.php', array('id'=>$groupid));
    $site = get_site();
    global $PAGE, $OUTPUT;
    $PAGE->set_url('/group/enrolme.php?id='.$groupid);
    $PAGE->set_pagelayout('base');
    $PAGE->set_title("$site->fullname: Запись в группу");
    $PAGE->set_heading($site->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading('Ошибка при записи в группу');
    echo '<div>Вы не авторизованны. Пожалуйста, авторизуйтесь и перейдите по <a href='.$url.'>ссылке</a> еще раз.</div>';
    echo $OUTPUT->footer();
}
