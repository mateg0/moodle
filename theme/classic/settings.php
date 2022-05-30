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

    // Section with site color scheme settings -->
    
    $page = new admin_settingpage('theme_classic_colors', 'Color schemes');

    $update_colors = function() {

        global $CFG;
        $scss_path = __DIR__.'/scss/custom.scss';
        $scss_color_schemes = [
            //green theme
            1 => [
                //main colors
                'custom-theme-color-1' => '#C3E4AF',
                'custom-theme-color-2' => '#F8FFF3',
                'custom-theme-color-3' => '#4E9A5F',
                'custom-theme-color-4' => '#C0C7BA',
                //subcolors
                'custom-theme-subcolor-1' =>  '#C3E4AF',
                'custom-theme-subcolor-2' =>  '#A3D97D',
                'custom-theme-subcolor-3' =>  '#4E9A5F',
                'custom-theme-subcolor-4' =>  '#3D8244',
                'custom-theme-subcolor-5' =>  '#53795B',
                'custom-theme-subcolor-6' =>  '#1F612D',
                'custom-theme-subcolor-7' =>  '#92BD78',
                'custom-theme-subcolor-8' =>  '#82AE4A',
                'custom-theme-subcolor-9' =>  '#809470',
                'custom-theme-subcolor-10' =>  '#BCCAB2',
                //event colors
                'group-event-color-1' =>  '#CC7167',
                'group-event-color-2' =>  '#5B508D',
                'group-event-color-3' =>  '#467E86',
                'group-event-color-4' =>  '#478646',
                'group-event-color-5' =>  '#466F86',
                'group-event-color-6' =>  '#50AC4E',
                'group-event-color-7' =>  '#6790C0',
                'group-event-color-8' =>  '#BC6BA5',
                'group-event-color-9' =>  '#BB5549',
                'group-event-color-10' =>  '#8C7DD1',
                //no group event color
                'nogroup-event-color' =>  '#CCB867',
                //pie diagram colors
                'pie-sector-color-1' => '#E9ACA6',
                'pie-sector-color-2' => '#A99DE0',
                'pie-sector-color-3' => '#9CD5DD',
                'pie-sector-color-4' => '#A0DF9F',
                // Logo color set
                'icon-fill-color-1' => '#4E9A5F',
                'icon-fill-color-2' => '#353535',
                'icon-fill-color-3' => '#353535',
            ],
            //yellow theme
            2 => [
                //main colors
                'custom-theme-color-1' => '#E3E4AF',
                'custom-theme-color-2' => '#FFFEF3',
                'custom-theme-color-3' => '#9A8E4E',
                'custom-theme-color-4' => '#C7C6BA',
                //subcolors
                'custom-theme-subcolor-1' =>  '#E9DB94',
                'custom-theme-subcolor-2' =>  '#E3E4AF',
                'custom-theme-subcolor-3' =>  '#C1B262',
                'custom-theme-subcolor-4' =>  '#9A8E4E',
                'custom-theme-subcolor-5' =>  '#C7C6BA',
                'custom-theme-subcolor-6' =>  '#796F3B',
                'custom-theme-subcolor-7' =>  '#BCBD78',
                'custom-theme-subcolor-8' =>  '#D8DA78',
                'custom-theme-subcolor-9' =>  '#D8DA8B',
                'custom-theme-subcolor-10' =>  '#B1A772',
                //event colors
                'group-event-color-1' =>  '#DB7979',
                'group-event-color-2' =>  '#DB79C5',
                'group-event-color-3' =>  '#7E5392',
                'group-event-color-4' =>  '#4B8882',
                'group-event-color-5' =>  '#8D79DB',
                'group-event-color-6' =>  '#6394AF',
                'group-event-color-7' =>  '#6A64B6',
                'group-event-color-8' =>  '#E4BA69',
                'group-event-color-9' =>  '#DB79C5',
                'group-event-color-10' =>  '#CE6767',
                //no group event color
                'nogroup-event-color' =>  '#DBAB79',
                //pie diagram colors
                'pie-sector-color-1' => '#E0A5A5',
                'pie-sector-color-2' => '#EAB7DF',
                'pie-sector-color-3' => '#C699DB',
                'pie-sector-color-4' => '#8ACCC5',
                 // Logo color set
                 'icon-fill-color-1' => '#9A8E4E',
                 'icon-fill-color-2' => '#353535',
                 'icon-fill-color-3' => '#353535',
            ],
            //blue theme
            3 => [
                //main colors
                'custom-theme-color-1' => '#AFCBE4',
                'custom-theme-color-2' => '#F3F8FF',
                'custom-theme-color-3' => '#4E719A',
                'custom-theme-color-4' => '#BAC2C7',
                //subcolors
                'custom-theme-subcolor-1' =>  '#93AED2',
                'custom-theme-subcolor-2' =>  '#AFCBE4',
                'custom-theme-subcolor-3' =>  '#6A83A9',
                'custom-theme-subcolor-4' =>  '#4E719A',
                'custom-theme-subcolor-5' =>  '#BAC2C7',
                'custom-theme-subcolor-6' =>  '#7E9BAD',
                'custom-theme-subcolor-7' =>  '#7AB1E2',
                'custom-theme-subcolor-8' =>  '#83A4C1',
                'custom-theme-subcolor-9' =>  '#707A94',
                'custom-theme-subcolor-10' =>  '#395B83',
                //event colors
                'group-event-color-1' =>  '#E38874',
                'group-event-color-2' =>  '#D874E3',
                'group-event-color-3' =>  '#4BB1B8',
                'group-event-color-4' =>  '#B4D136',
                'group-event-color-5' =>  '#D3E374',
                'group-event-color-6' =>  '#5BBFA7',
                'group-event-color-7' =>  '#9174E3',
                'group-event-color-8' =>  '#4B84B8',
                'group-event-color-9' =>  '#74E393',
                'group-event-color-10' =>  '#A5E374',
                //no group event color
                'nogroup-event-color' =>  '#FFCB81',
                //pie diagram colors
                'pie-sector-color-1' => '#E3AB9E',
                'pie-sector-color-2' => '#E6A6ED',
                'pie-sector-color-3' => '#98D7DB',
                'pie-sector-color-4' => '#D3E390',
                 // Logo color set
                 'icon-fill-color-1' => '#4E719A',
                 'icon-fill-color-2' => '#353535',
                 'icon-fill-color-3' => '#353535',
            ],
            //purple theme
            4 => [
                //main colors
                'custom-theme-color-1' => '#B0AFE4',
                'custom-theme-color-2' => '#F3F3FF',
                'custom-theme-color-3' => '#4E5A9A',
                'custom-theme-color-4' => '#BAC2C7',
                //subcolors
                'custom-theme-subcolor-1' =>  '#A57BB9',
                'custom-theme-subcolor-2' =>  '#CCCCFE',
                'custom-theme-subcolor-3' =>  '#7381C9',
                'custom-theme-subcolor-4' =>  '#4E5A9A',
                'custom-theme-subcolor-5' =>  '#B0AFE4',
                'custom-theme-subcolor-6' =>  '#BAC2C7',
                'custom-theme-subcolor-7' =>  '#8FB7CF',
                'custom-theme-subcolor-8' =>  '#8F8FB5',
                'custom-theme-subcolor-9' =>  '#8686CF',
                'custom-theme-subcolor-10' =>  '#B4A2DA',
                //event colors
                'group-event-color-1' =>  '#C3705E',
                'group-event-color-2' =>  '#B54F88',
                'group-event-color-3' =>  '#41967A',
                'group-event-color-4' =>  '#ABD25B',
                'group-event-color-5' =>  '#E17F69',
                'group-event-color-6' =>  '#6E5DB0',
                'group-event-color-7' =>  '#9E5BD2',
                'group-event-color-8' =>  '#418296',
                'group-event-color-9' =>  '#60C35E',
                'group-event-color-10' =>  '#C3A75E',
                //no group event color
                'nogroup-event-color' =>  '#E1BC62',
                //pie diagram colors
                'pie-sector-color-1' => '#DA8AB7',
                'pie-sector-color-2' => '#D89688',
                'pie-sector-color-3' => '#C2DB8F',
                'pie-sector-color-4' => '#80D2B7',
                 // Logo color set
                 'icon-fill-color-1' => '#4E5A9A',
                 'icon-fill-color-2' => '#353535',
                 'icon-fill-color-3' => '#353535',
            ],
            //light green theme
            5 => [
                //main colors
                'custom-theme-color-1' => '#DCF4B5',
                'custom-theme-color-2' => '#F8FFF3',
                'custom-theme-color-3' => '#92B861',
                'custom-theme-color-4' => '#C0C7BA',
                //subcolors
                'custom-theme-subcolor-1' =>  '#AED09A',

                'custom-theme-subcolor-2' =>  '#DCF4B5',
                'custom-theme-subcolor-3' =>  '#A5CA87',
                'custom-theme-subcolor-4' =>  '#92B861',
                'custom-theme-subcolor-5' =>  '#C0C7BA',
                'custom-theme-subcolor-6' =>  '#949F8B',
                'custom-theme-subcolor-7' =>  '#92BD78',
                'custom-theme-subcolor-8' =>  '#7A8F5E',
                'custom-theme-subcolor-9' =>  '#AFDB8D',
                'custom-theme-subcolor-10' =>  '#739A41',
                //event colors
                'group-event-color-1' =>  '#CA6A6A',
                'group-event-color-2' =>  '#A6577F',
                'group-event-color-3' =>  '#7057A6',
                'group-event-color-4' =>  '#4F9564',
                'group-event-color-5' =>  '#6ACA79',
                'group-event-color-6' =>  '#91CCDE',
                'group-event-color-7' =>  '#CE894A',
                'group-event-color-8' =>  '#C79E4E',
                'group-event-color-9' =>  '#6A8BCA',
                'group-event-color-10' =>  '#6ACAB9',
                //no group event color
                'nogroup-event-color' =>  '#CACA6A',
                //pie diagram colors
                'pie-sector-color-1' => '#D292B2',
                'pie-sector-color-2' => '#D7A5A5',
                'pie-sector-color-3' => '#A697C6',
                'pie-sector-color-4' => '#97C9A6',
                 // Logo color set
                 'icon-fill-color-1' => '#92B861',
                 'icon-fill-color-2' => '#353535',
                 'icon-fill-color-3' => '#353535',
            ],
            6 => [
                //main colors
                'custom-theme-color-1' => '#FFFFFF',
                'custom-theme-color-2' => '#F3F3F3',
                'custom-theme-color-3' => '#00B9AE',
                'custom-theme-color-4' => '#DADADA',
                //subcolors
                'custom-theme-subcolor-1' =>  '#00B9AE',

                'custom-theme-subcolor-2' =>  '#ADE9E6',
                'custom-theme-subcolor-3' =>  '#00B9AE',
                'custom-theme-subcolor-4' =>  '#199890',
                'custom-theme-subcolor-5' =>  '#93D4D1',
                'custom-theme-subcolor-6' =>  '#08C5B9',
                'custom-theme-subcolor-7' =>  '#65C1BC',
                'custom-theme-subcolor-8' =>  '#41CEC6',
                'custom-theme-subcolor-9' =>  '#69AFAB',
                'custom-theme-subcolor-10' =>  '#92B9B7',
                //event colors
                'group-event-color-1' =>  '#00B9AE',
                'group-event-color-2' =>  '#85C6C2',
                'group-event-color-3' =>  '#4ABEB7',
                'group-event-color-4' =>  '#5ED0CB',
                'group-event-color-5' =>  '#92B9B7',
                'group-event-color-6' =>  '#69AFAB',
                'group-event-color-7' =>  '#41CEC6',
                'group-event-color-8' =>  '#65C1BC',
                'group-event-color-9' =>  '#6A8BCA',
                'group-event-color-10' =>  '#56C5BF',
                //no group event color
                'nogroup-event-color' =>  '#FAC660',
                //pie diagram colors
                'pie-sector-color-1' => '#B277D9',
                'pie-sector-color-2' => '#FA608E',
                'pie-sector-color-3' => '#3AE09E',
                'pie-sector-color-4' => '#00B9AE',
                 // Logo color set
                 'icon-fill-color-1' => '#00B9AE',
                 'icon-fill-color-2' => '#353535',
                 'icon-fill-color-3' => '#353535',
            ],
            7 => [
                //main colors
                'custom-theme-color-1' => '#2B3141',
                'custom-theme-color-2' => '#16181E',
                'custom-theme-color-3' => '#00B9AE',
                'custom-theme-color-4' => '#252934',
                //subcolors
                'custom-theme-subcolor-1' =>  '#00B9AE',

                'custom-theme-subcolor-2' =>  '#00B9AE',
                'custom-theme-subcolor-3' =>  '#FF565E',
                'custom-theme-subcolor-4' =>  '#FFC542',
                'custom-theme-subcolor-5' =>  '#00B9AE',
                'custom-theme-subcolor-6' =>  '#85C6C2',
                'custom-theme-subcolor-7' =>  '#199890',
                'custom-theme-subcolor-8' =>  '#65C1BC',
                'custom-theme-subcolor-9' =>  '#08C5B9',
                'custom-theme-subcolor-10' =>  '#93D4D1',
                //event colors
                'group-event-color-1' =>  '#E0555C',
                'group-event-color-2' =>  '#B8EB50',
                'group-event-color-3' =>  '#52DAAD',
                'group-event-color-4' =>  '#EE7C63',
                'group-event-color-5' =>  '#EA5B3B',
                'group-event-color-6' =>  '#7256E4',
                'group-event-color-7' =>  '#B573E9',
                'group-event-color-8' =>  '#72C6E1',
                'group-event-color-9' =>  '#74EB71',
                'group-event-color-10' =>  '#E2B235',
                //no group event color
                'nogroup-event-color' =>  '#E7BE62',
                //pie diagram colors
                'pie-sector-color-1' => '#C03ED5',
                'pie-sector-color-2' => '#FF565E',
                'pie-sector-color-3' => '#FFC542',
                'pie-sector-color-4' => '#00B9AE',
                 // Logo color set
                 'icon-fill-color-1' => '#00B9AE',
                 'icon-fill-color-2' => '#FFFFFF',
                 'icon-fill-color-3' => '#FFFFFF',
            ]
        ];
        
        if (file_exists($scss_path)) {
            
            if (isset($CFG->colorscheme) && array_key_exists($CFG->colorscheme, $scss_color_schemes)) {
                $selected_color_scheme = $scss_color_schemes[$CFG->colorscheme];
            } else {
                return;
            }
            
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
                        if (array_key_exists($scss_var_name, $selected_color_scheme)) {
                            $matches[7] = $selected_color_scheme[$scss_var_name];
                            $scss_file_output[] = implode("", array_slice($matches, 1));
                            continue;
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

    $color_schemes = [
        1 => 'Scheme 1 (Green)',
        2 => 'Scheme 2 (Yellow)',
        3 => 'Scheme 3 (Blue)',
        4 => 'Scheme 4 (Purple)',
        5 => 'Scheme 5 (Light Green)',
        6 => 'Scheme 6 (Contrast light)',
        7 => 'Scheme 7 (Contrast dark)'
    ];

    $colorschemeselector = new admin_setting_configselect('colorscheme', 'Color scheme', '', 1, $color_schemes);
    $colorschemeselector->set_updatedcallback($update_colors);
    $page->add($colorschemeselector);
    
    $settings->add($page);

}
