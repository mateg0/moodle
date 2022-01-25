<?php
$functions = array(
    'getcalendarview' => array(
        'classname' => 'core_calendar_external_view',
        'methodname' => 'get_new_calendar_view',
        'classpath' => 'local/calendarajax/externallib.php',
        'description' => 'Return requested calendar view',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true
    ),
    'getcalendarperiod' => array(
            'classname' => 'core_calendar_external_periods',
            'methodname' => 'get_new_calendar_period',
            'classpath' => 'local/calendarajax/externallib.php',
            'description' => 'Return requested calendar period',
            'type' => 'read',
            'ajax' => true,
            'capabilities' => '',
            'loginrequired' => true
    ),
    'calendar_ajax_get_courses_by_user_id' => [
        'classname' => 'core_calendar_external_courses',
        'methodname' => 'get_courses_where_user_teacher',
        'classpath' => 'local/calendarajax/externallib.php',
        'description' => 'Return teacher courses',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true
    ],
);
