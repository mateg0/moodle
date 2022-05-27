<?php
defined('MOODLE_INTERNAL') || die();

class block_groupmembers extends block_base
{
    function hide_header(): bool
    {
        return true;
    }

    function allow_multiple(): bool
    {
        return false;
    }

    /**
     * @throws coding_exception
     */
    function init()
    {
        $this->title = get_string('pluginname', 'block_groupmembers');
    }

    /**
     * @throws coding_exception
     */
    function get_content()
    {
        global $CFG;
        
        if ($this->content !== NULL) {
            return $this->content;
        }
/*
        switch($CFG->colorscheme) {
            case 1:
                $this->page->requires->css('/blocks/groupmembers/subcolors/subcolors.css');
            break;

            case 2:
                $this->page->requires->css('/blocks/groupmembers/subcolors/subcolors 2.css');
            break;
            
            case 3:
                $this->page->requires->css('/blocks/groupmembers/subcolors/subcolors 3.css');
            break;

            case 4:
                $this->page->requires->css('/blocks/groupmembers/subcolors/subcolors 4.css');
            break;

            case 5:
                $this->page->requires->css('/blocks/groupmembers/subcolors/subcolors 5.css');
            break;
        }
        */

        $this->page->requires->css('/blocks/groupmembers/styles.css');
        $this->page->requires->js('/blocks/groupmembers/assets/ajaxform.js');


        $this->content = new stdClass;
        $this->content->text = '';
        $mform = new \block_groupmembers\form\courcegroupform();

        $this->content->text = $mform->render();
        $this->content->text .= '<div id="block-groupmembers-holder"></div>';

        $template = new \block_groupmembers\output\groupmembers_blank();
        $renderer = $this->page->get_renderer('block_groupmembers');
        $this->content->text .=$renderer->renderGroupMembersBlank($template);
        return $this->content;
    }
    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
