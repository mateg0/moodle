<?php
defined('MOODLE_INTERNAL') || die();

class block_adduser extends block_base
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
        $this->title = get_string('pluginname', 'block_adduser');
    }

    /**
     * @throws coding_exception
     */
    function get_content()
    {
        if ($this->content !== NULL) {
                return $this->content;
            }
            $this->content = new stdClass;
            $this->content->text = '';
            //$this->content->text .= '<h5>добавить пользователя</h5>';
            $template = new \block_adduser\output\adduser();
            $renderer = $this->page->get_renderer('block_adduser');
            $this->content->text .=$renderer->renderAddUser($template);
            $this->page->requires->css('/blocks/adduser/styles.css');
            return $this->content;
    }
}
