<?php

class block_student_involvement extends block_base {

    public function init() {
        $this->title = get_string('student_involvement', 'block_student_involvement');
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

        $studentachievements = new \local_studentachievements\output\studentachievements();
        $studentstopwatch = new \local_studentstopwatch\output\studentstopwatch();
        $onlineclassmates = new \local_onlineclassmates\output\onlineclassmates();

        $achievementsRenderer = $this->page->get_renderer('local_studentachievements');
        $stopwatchRenderer = $this->page->get_renderer('local_studentstopwatch');
        $onlineclassmatesRenderer = $this->page->get_renderer('local_onlineclassmates');

        $this->content->text .= '<div id="student-header-wrapper" class="student-header-wrapper">';

        $this->content->text .= '<div id="studentachievements-block" class="student-header-block">';
        $this->content->text .= $achievementsRenderer->renderStudentsAchievements($studentachievements);
        $this->content->text .= '</div>';

        $this->content->text .= '<div id="studentstopwatch-block" class="student-header-block">';
        $this->content->text .= $stopwatchRenderer->renderStudentStopwatch($studentstopwatch);
        $this->content->text .= '</div>';

        $this->content->text .= '<div id="onlineclassmates-block" class="student-header-block">';
        $this->content->text .= $onlineclassmatesRenderer->renderOnlineClassmates($onlineclassmates);
        $this->content->text .= '</div>';

        $this->content->text .= '</div>';

        $this->content->text .= '<div id="student-header-wrapper-mini" class="student-header-wrapper mini">';

        $this->content->text .= $achievementsRenderer->renderStudentsAchievementsMini($studentachievements);

        $this->content->text .= $stopwatchRenderer->renderStudentStopwatchMini($studentstopwatch);

        $this->content->text .= $onlineclassmatesRenderer->renderOnlineClassmatesMini($onlineclassmates);

        $this->content->text .= '</div>';

        $this->page->requires->js('/local/onlineclassmates/assets/onlineclassmates.js');
        $this->page->requires->js('/local/studentachievements/assets/studentachievements.js');
        $this->page->requires->js('/local/studentstopwatch/assets/studentstopwatch.js');
        
        return $this->content;
    }

}