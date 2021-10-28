<?php
defined('MOODLE_INTERNAL') || die();

class block_groupstats extends block_base
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
        $this->title = get_string('pluginname', 'block_groupstats');
    }

    /**
     * @throws coding_exception
     */
    function get_content()
    {
        if ($this->content !== NULL) {
                return $this->content;
            }

            $this->page->requires->css('/blocks/groupstats/styles.css');
            $this->page->requires->js('/blocks/groupstats/assets/ajaxform.js');


            $this->content = new stdClass;
            $this->content->text = '';
            $mform = new \block_groupstats\form\courcegroupform();

            $this->content->text = $mform->render();
            $this->content->text .= '<div id="gs-block-groupstats-holder"></div>';

            $template = new \block_groupstats\output\groupstats_blank();
            $renderer = $this->page->get_renderer('block_groupstats');
            $this->content->text .=$renderer->renderGroupStatsBlank($template);

            $this->content->text .= '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                     <script src="https://cdn.jsdelivr.net/gh/emn178/chartjs-plugin-labels/src/chartjs-plugin-labels.js"></script>';

            return $this->content;
    }
}
