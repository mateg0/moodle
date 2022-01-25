<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot.'/calendar/lib.php');

class core_calendar_external_view extends external_api{
    public static function get_new_calendar_view($view){

        global $PAGE;

        $time = time();
        $courseid = SITEID;
        $categoryid = null;
        $renderer = $PAGE->get_renderer('core_calendar');
        $calendar = calendar_information::create($time, $courseid, $categoryid);
        $output = '<div class="heightcontainer" id="calendar_ajax">';
        list($data, $template) = calendar_get_view($calendar, $view, true, false, null);

        if($view == "day"){
            $calendarday = new \core_calendar\output\calendarday($data);
            $dayevents = $calendarday->getevents();
            $data->events = $dayevents;
        }

        $output .= $renderer->render_from_template($template, $data);
        $output .= '</div>';

        return $output;
    }

    public static function get_new_calendar_view_parameters(){
            return new external_function_parameters(
               array("view" => new external_value(PARAM_TEXT, "new view"))
            );
    }

     public static function get_new_calendar_view_returns() {
            return new external_value(PARAM_RAW, 'The updated template output');
        }
 }

 class core_calendar_external_periods extends external_api{

    public static function get_new_calendar_period($time, $view){
       global $PAGE;
       $courseid = SITEID;
       $categoryid = null;
       $renderer = $PAGE->get_renderer('core_calendar');
          $calendar = calendar_information::create($time, $courseid, $categoryid);
          $output = '<div class="heightcontainer" id="calendar_ajax">';
          list($data, $template) = calendar_get_view($calendar, $view, true, false, null);

          if($view == "day"){
             $calendarday = new \core_calendar\output\calendarday($data);
             $dayevents = $calendarday->getevents();
             $data->events = $dayevents;
          }

          $output .= $renderer->render_from_template($template, $data);
          $output .= '</div>';

       return $output;
    }
    public static function get_new_calendar_period_parameters(){
        return new external_function_parameters(
                array(
                    "time" => new external_value(PARAM_TEXT, "new time"),
                    "view" => new external_value(PARAM_TEXT, "new view")
                )
           );
      }
      public static function get_new_calendar_period_returns() {
         return new external_value(PARAM_RAW, 'The updated template output');
      }
 }

 class core_calendar_external_courses extends external_api {
   public static function get_courses_where_user_teacher()
   {
       global $USER, $DB;

       $courses = [];

       if ($roles = get_roles_with_capability('moodle/course:update', CAP_ALLOW)) {
           foreach ($roles as $role) {
               if($DB->record_exists('role_assignments', array('roleid' => $role->id, 'userid' => $USER->id))){
                   $userRoleAssignments = $DB->get_records(
                       'role_assignments',
                       array('roleid' => $role->id, 'userid' => $USER->id)
                   );

                   if (isset($userRoleAssignments)) {
                       foreach ($userRoleAssignments as $userRoleAssignment) {
                           $contextOfUserRoleAssignment = $DB->get_record(
                               'context',
                               array('id' => $userRoleAssignment->contextid)
                           );

                           if ($DB->record_exists('course', array('id' => $contextOfUserRoleAssignment -> instanceid))) {
                               $course = get_course($contextOfUserRoleAssignment -> instanceid);

                               array_push($courses, $course);
                           }
                       }
                   }
               }
           }
       }

       return $courses;
   }

   public static function get_courses_where_user_teacher_returns()
   {
       return new external_multiple_structure(
           new external_single_structure(array(
               'id' => new external_value(PARAM_INT, 'Course id'),
               'fullname' => new external_value(PARAM_TEXT, 'Course name')
           ))
       );
   }

   public static function get_courses_where_user_teacher_parameters()
   {
       return new external_function_parameters([]);
   }
}