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
 * Classic theme settings file.
 *
 * @package    theme_classic
 * @copyright  2018 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings = new theme_boost_admin_settingspage_tabs('themesettingclassic', get_string('configtitle', 'theme_classic'));
    $page = new admin_settingpage('theme_classic_general', get_string('generalsettings', 'theme_boost'));

    $name = 'theme_classic/navbardark';
    $title = get_string('navbardark', 'theme_classic');
    $description = get_string('navbardarkdesc', 'theme_classic');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Preset.
    $name = 'theme_classic/preset';
    $title = get_string('preset', 'theme_classic');
    $description = get_string('preset_desc', 'theme_classic');
    $default = 'default.scss';

    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'theme_classic', 'preset', 0, 'itemid, filepath, filename', false);

    $choices = [];
    foreach ($files as $file) {
        $choices[$file->get_filename()] = $file->get_filename();
    }

    // These are the built in presets.
    $choices['default.scss'] = 'default.scss';
    $choices['plain.scss'] = 'plain.scss';

    $setting = new admin_setting_configthemepreset($name, $title, $description, $default, $choices, 'classic');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Preset files setting.
    $name = 'theme_classic/presetfiles';
    $title = get_string('presetfiles', 'theme_classic');
    $description = get_string('presetfiles_desc', 'theme_classic');

    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));
    $page->add($setting);

    // Background image setting.
    $name = 'theme_classic/backgroundimage';
    $title = get_string('backgroundimage', 'theme_boost');
    $description = get_string('backgroundimage_desc', 'theme_boost');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'backgroundimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $body-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_classic/brandcolor';
    $title = get_string('brandcolor', 'theme_boost');
    $description = get_string('brandcolor_desc', 'theme_boost');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    $settings->add($page);

    // Advanced settings.
    $page = new admin_settingpage('theme_classic_advanced', get_string('advancedsettings', 'theme_boost'));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_scsscode('theme_classic/scsspre',
        get_string('rawscsspre', 'theme_boost'), get_string('rawscsspre_desc', 'theme_boost'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_classic/scss', get_string('rawscss', 'theme_boost'),
        get_string('rawscss_desc', 'theme_boost'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    // Section with site colour palette settings -->

    $page = new admin_settingpage('theme_classic_colours', 'Colours');

    $update_colours = function() {

        $scss_path = __DIR__.'/scss/custom.scss';
        $scss_mapper = array(
            // 'scss_var_name' => 'colour_picker_name',
            'custom-main-bg-color' => 'main_color_background',
            'custom-main-header-color' => 'main_color_navbar',
            'custom-main-link-color' => 'main_color_links',
            'custom-card-shadow-color' => 'main_color_card_shadow'
        );

        if (file_exists($scss_path)) {

            global $DB;
            $scss_var_definition_pattern = "/(\s*)(\\$)([\w-]+)(\s*)(:)(\s*)(\S.*\S)(\s*)(;)(\s*)/";

            $scss_file_lines = file($scss_path);
            $scss_file = fopen($scss_path, 'wt');
            flock($scss_file, LOCK_EX);

            try {

                $scss_file_output = array();

                foreach ($scss_file_lines as $line) {
                    $matches = array();
                    $match = preg_match($scss_var_definition_pattern, $line, $matches);
                    if ($match) {
                        $scss_var_name = $matches[3];
                        if (array_key_exists($scss_var_name, $scss_mapper)) {
                            $config_name = $scss_mapper[$scss_var_name];
                            $record = $DB->get_record('config_plugins', array('plugin' => 'theme_classic', 'name' => $config_name));
                            if ($record && !empty($record->value)) {
                                $matches[7] = $record->value;
                                $scss_file_output[] = implode("", array_slice($matches, 1));
                                continue;
                            }
                        }
                    }
                    $scss_file_output[] = $line;
                }

                $scss_file_output = implode("", $scss_file_output);
                fwrite($scss_file, $scss_file_output);

            } catch (Throwable $e) {
                $scss_file_output = implode("", $scss_file_lines);
                fwrite($scss_file, $scss_file_output);
            } finally {
                flock($scss_file, LOCK_UN);
                fclose($scss_file);
            }

            theme_reset_all_caches();

        }

    };

    $colourpickers = array(
        0 => array(
            'name' => 'main_color_background',
            'title' => 'Background color',
            'description' => '',
            'default' => '#F8FFF3'
        ),
        1 => array(
            'name' => 'main_color_navbar',
            'title' => 'Navigation bar color',
            'description' => '',
            'default' => '#C3E4AF'
        ),
        2 => array(
            'name' => 'main_color_links',
            'title' => 'Links color',
            'description' => '',
            'default' => '#222222'
        ),
        3 => array(
            'name' => 'main_color_card_shadow',
            'title' => 'Card shadow color',
            'description' => '',
            'default' => '#ECECEC'
        )
    );

    foreach ($colourpickers as $colourpicker) {
        $name = 'theme_classic/'.$colourpicker['name'];
        $title = $colourpicker['title'];
        $description = $colourpicker['description'];
        $default = $colourpicker['default'];
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
        $setting->set_updatedcallback($update_colours);
        $page->add($setting);
    }

    $settings->add($page);

}
