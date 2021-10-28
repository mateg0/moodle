<?php

namespace block_groupmembers\course;
defined('MOODLE_INTERNAL') || die();

class last_course_teacher
{
    public function __construct()
    {

    }

    public function get_last_course_teacher_enrolled($lastcourses, $enrolledcourses)
    {

        global $USER;

        if (count($lastcourses) == 0 || count($enrolledcourses) == 0) {
            return -1;
        }

        foreach ($lastcourses as $lastcourse) {
            foreach ($enrolledcourses as $enrolledcourse) {
                if ($lastcourse->courseid == $enrolledcourse->id) {

                    $context = \context_course::instance($enrolledcourse->id);
                    if (user_has_role_assignment($USER->id, 3, $context->id)) {//3 - editingteacher
                        return $enrolledcourse->id;
                    } else
                        if (user_has_role_assignment($USER->id, 4, $context->id)) {//4 -teacher
                            return $enrolledcourse->id;
                        } else
                            if (user_has_role_assignment($USER->id, 2, $context->id)) {//2 - coursecreator
                                return $enrolledcourse->id;
                            }
                }
            }
        }

        return -1;
    }

    /**
     * @throws \dml_exception
     */
    public function get_last_acessed_courses_from_log()
    {

        global $DB, $USER;

        $veriflastcourse = $DB->count_records('logstore_standard_log', array('action' => "viewed", 'target' => "course", 'userid' => $USER->id));

        if ($veriflastcourse !== 0) {
            $sql = 'SELECT * FROM {logstore_standard_log} WHERE ';
            $sql .= 'action = ?  AND target = ? AND userid = ? AND courseid != 1 ';
            $sql .= 'order by timecreated desc';

            return $DB->get_records_sql($sql, array('viewed', 'course', $USER->id));
        }
        return [];
    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}