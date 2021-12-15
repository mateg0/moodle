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
 * Navigation Progress block settings
 *
 * @package   block_navigation_progress
 * @copyright  2021 Nikolay Terekhin
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/blocks/navigation_progress/lib.php');

if ($ADMIN->fulltree) {

    $options = array(10 => 10, 12 => 12, 14 => 14, 16 => 16, 18 => 18, 20 => 20);
    $settings->add(new admin_setting_configselect('block_navigation_progress/wrapafter',
        get_string('wrapafter', 'block_navigation_progress'),
        '',
        DEFAULT_COMPLETIONPROGRESS_WRAPAFTER,
        $options)
    );

    $options = array(
        'squeeze' => get_string('config_squeeze', 'block_navigation_progress'),
        'scroll' => get_string('config_scroll', 'block_navigation_progress'),
        'wrap' => get_string('config_wrap', 'block_navigation_progress'),
    );
    $settings->add(new admin_setting_configselect('block_navigation_progress/defaultlongbars',
        get_string('defaultlongbars', 'block_navigation_progress'),
        '',
        DEFAULT_COMPLETIONPROGRESS_LONGBARS,
        $options)
    );

    $options = array(
        'shortname' => get_string('shortname', 'block_navigation_progress'),
        'fullname' => get_string('fullname', 'block_navigation_progress')
    );
    $settings->add(new admin_setting_configselect('block_navigation_progress/coursenametoshow',
        get_string('coursenametoshow', 'block_navigation_progress'),
        '',
        DEFAULT_COMPLETIONPROGRESS_COURSENAMETOSHOW,
        $options)
    );

    $coloursetting = new admin_setting_configcolourpicker('block_navigation_progress/completed_colour',
        get_string('completed_colour_title', 'block_navigation_progress'),
        get_string('completed_colour_descr', 'block_navigation_progress'),
        get_string('completed_colour', 'block_navigation_progress'),
        null );
    $coloursetting->set_updatedcallback('block_navigation_progress::increment_cache_value');
    $settings->add($coloursetting);

    $coloursetting = new admin_setting_configcolourpicker('block_navigation_progress/submittednotcomplete_colour',
        get_string('submittednotcomplete_colour_title', 'block_navigation_progress'),
        get_string('submittednotcomplete_colour_descr', 'block_navigation_progress'),
        get_string('submittednotcomplete_colour', 'block_navigation_progress'),
        null );
    $coloursetting->set_updatedcallback('block_navigation_progress::increment_cache_value');
    $settings->add($coloursetting);

    $coloursetting = new admin_setting_configcolourpicker('block_navigation_progress/notCompleted_colour',
        get_string('notCompleted_colour_title', 'block_navigation_progress'),
        get_string('notCompleted_colour_descr', 'block_navigation_progress'),
        get_string('notCompleted_colour', 'block_navigation_progress'),
        null );
    $coloursetting->set_updatedcallback('block_navigation_progress::increment_cache_value');
    $settings->add($coloursetting);

    $coloursetting = new admin_setting_configcolourpicker('block_navigation_progress/futureNotCompleted_colour',
        get_string('futureNotCompleted_colour_title', 'block_navigation_progress'),
        get_string('futureNotCompleted_colour_descr', 'block_navigation_progress'),
        get_string('futureNotCompleted_colour', 'block_navigation_progress'),
        null );
    $coloursetting->set_updatedcallback('block_navigation_progress::increment_cache_value');
    $settings->add($coloursetting);

    $settings->add(new admin_setting_configcheckbox('block_navigation_progress/showinactive',
        get_string('showinactive', 'block_navigation_progress'),
        '',
        DEFAULT_COMPLETIONPROGRESS_SHOWINACTIVE)
    );
    $settings->add(new admin_setting_configcheckbox('block_navigation_progress/showlastincourse',
        get_string('showlastincourse', 'block_navigation_progress'),
        '',
        DEFAULT_COMPLETIONPROGRESS_SHOWLASTINCOURSE)
    );
    $settings->add(new admin_setting_configcheckbox('block_navigation_progress/forceiconsinbar',
        get_string('forceiconsinbar', 'block_navigation_progress'),
        '',
        DEFAULT_COMPLETIONPROGRESS_FORCEICONSINBAR)
    );
}
