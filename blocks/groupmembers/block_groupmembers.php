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
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->page->requires->css('/blocks/groupmembers/styles.css');
        $this->page->requires->js('/blocks/groupmembers/assets/ajaxform.js');


        $this->content = new stdClass;
        $this->content->text = '';
        $mform = new \block_groupmembers\form\courcegroupform();

        $this->content->text = $mform->render();
        $this->content->text .= '<div id="block-groupmembers-holder"></div>';

        return $this->content;
    }
    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
