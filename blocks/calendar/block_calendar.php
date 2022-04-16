<?php

require_once($CFG->dirroot . '/calendar/lib.php');

class block_calendar extends block_base {

    public function init() {
        $this->title = get_string('calendar', 'block_calendar');
    }

    function hide_header(): bool
    {
        return true;
    }

    function allow_multiple(): bool
    {
        return false;
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
      
        $this->content = new stdClass;
        $this->content->text = '<p></p>';

        $this->page->requires->js('/local/calendarajax/assets/calendar_ajax.js');
        $time = time();
        $courseid = SITEID;
        $categoryid = null;
        $view = 'month';
        $calendar = calendar_information::create($time, $courseid, $categoryid);
        $renderer = $this->page->get_renderer('core_calendar');
        $this->content->text .= html_writer::start_tag('div', array('class'=>'path-calendar'));
        $this->content->text .= $renderer->start_layout();
        $this->content->text .= html_writer::start_tag('div', array('class'=>'heightcontainer', 'id'=>'calendar_ajax'));
        list($data, $template) = calendar_get_view($calendar, $view, true, false, null);
    
        if($view == "day"){
            $calendarday = new \core_calendar\output\calendarday($data);
            $dayevents = $calendarday->getevents();
            $data->events = $dayevents;
        }
    
        $this->content->text .= $renderer->render_from_template($template, $data);
        $this->content->text .= html_writer::end_tag('div');
        $this->content->text .= $renderer->complete_layout();
        $this->content->text .= html_writer::end_tag('div');
        
        return $this->content;
    }

}