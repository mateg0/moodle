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
 * picfolder module renderer
 *
 * @package   mod_picfolder
 * @copyright 2009 Petr Skoda  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class mod_picfolder_renderer extends plugin_renderer_base {

    /**
     * Returns html to display the content of mod_picfolder
     * (Description, picfolder files and optionally Edit button)
     *
     * @param stdClass $picfolder record from 'picfolder' table (please note
     *     it may not contain fields 'revision' and 'timemodified')
     * @return string
     */
    public function display_picfolder(stdClass $picfolder) {
        $output = '';
        $picfolderinstances = get_fast_modinfo($picfolder->course)->get_instances_of('picfolder');
        if (!isset($picfolderinstances[$picfolder->id]) ||
                !($cm = $picfolderinstances[$picfolder->id]) ||
                !($context = context_module::instance($cm->id))) {
            // Some error in parameters.
            // Don't throw any errors in renderer, just return empty string.
            // Capability to view module must be checked before calling renderer.
            return $output;
        }

        if (trim($picfolder->intro)) {
            if ($picfolder->display != picfolder_DISPLAY_INLINE) {
                $output .= $this->output->box(format_module_intro('picfolder', $picfolder, $cm->id),
                        'generalbox', 'intro');
            } else if ($cm->showdescription) {
                // for "display inline" do not filter, filters run at display time.
                $output .= format_module_intro('picfolder', $picfolder, $cm->id, false);
            }
        }

        $picfoldertree = new picfolder_tree($picfolder, $cm);
        if ($picfolder->display == picfolder_DISPLAY_INLINE) {
            // Display module name as the name of the root directory.
            $picfoldertree->dir['dirname'] = $cm->get_formatted_name(array('escape' => false));
        }
        $output .= $this->output->container_start("box generalbox pt-0 pb-3 picfoldertree");
        $output .= $this->render($picfoldertree);
        $output .= $this->output->container_end();

        // Do not append the edit button on the course page.
        $downloadable = picfolder_archive_available($picfolder, $cm);

        $buttons = '';
        if ($downloadable) {
            $downloadbutton = $this->output->single_button(
                new moodle_url('/mod/picfolder/download_picfolder.php', array('id' => $cm->id)),
                get_string('downloadpicfolder', 'picfolder')
            );

            $buttons .= $downloadbutton;
        }

        // Display the "Edit" button if current user can edit picfolder contents.
        // Do not display it on the course page for the teachers because there
        // is an "Edit settings" button right next to it with the same functionality.
        if (has_capability('mod/picfolder:managefiles', $context) &&
            ($picfolder->display != picfolder_DISPLAY_INLINE || !has_capability('moodle/course:manageactivities', $context))) {
            $editbutton = $this->output->single_button(
                new moodle_url('/mod/picfolder/edit.php', array('id' => $cm->id)),
                get_string('edit')
            );

            $buttons .= $editbutton;
        }

        if ($buttons) {
            $output .= $this->output->container_start("box generalbox pt-0 pb-3 picfolderbuttons");
            $output .= $buttons;
            $output .= $this->output->container_end();
        }

        return $output;
    }

    public function render_picfolder_tree(picfolder_tree $tree) {
        static $treecounter = 0;

        $content = '';
        $id = 'picfolder_tree'. ($treecounter++);
        $content .= '<div id="'.$id.'" class="filemanager">';
        $content .= $this->htmllize_tree($tree, array('files' => array(), 'subdirs' => array($tree->dir)));
        $content .= '</div>';
        $showexpanded = true;
        if (empty($tree->picfolder->showexpanded)) {
            $showexpanded = false;
        }
        $this->page->requires->js_init_call('M.mod_picfolder.init_tree', array($id, $showexpanded));
        return $content;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     */
    protected function htmllize_tree($tree, $dir) {
        global $CFG;

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon($this->picfolder_icon(), $subdir['dirname'], 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', s($subdir['dirname']), array('class' => 'fp-filename'));
            $filename = html_writer::tag('div', $filename, array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename. $this->htmllize_tree($tree, $subdir));
        }
        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                    $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $filename, false);
            $filenamedisplay = clean_filename($filename);
            if (file_extension_in_typegroup($filename, 'web_image')) {
                $image = $url->out(false, array('preview' => 'tinyicon', 'oid' => $file->get_timemodified()));
                $image = html_writer::empty_tag('img', array('src' => $image));
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $filenamedisplay, 'moodle');
            }
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', $filenamedisplay, array('class' => 'fp-filename'));
            $urlparams = null;
            if ($tree->picfolder->forcedownload) {
                $urlparams = ['forcedownload' => 1];
            }
            $filename = html_writer::tag('span',
                html_writer::link($url->out(false, $urlparams), $filename),
                ['class' => 'fp-filename-icon']
            );
            $result .= html_writer::tag('li', $filename);
        }
        $result .= '</ul>';

        return $result;
    }

    private function picfolder_icon(){
        return '/f/picfolder-24';
    }
}

class picfolder_tree implements renderable {
    public $context;
    public $picfolder;
    public $cm;
    public $dir;

    public function __construct($picfolder, $cm) {
        $this->picfolder = $picfolder;
        $this->cm     = $cm;

        $this->context = context_module::instance($cm->id);
        $fs = get_file_storage();
        $this->dir = $fs->get_area_tree($this->context->id, 'mod_picfolder', 'content', 0);
    }
}
