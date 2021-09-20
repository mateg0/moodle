<?php

/**
 * Forum event handler definition.
 *
 * @package mod_jitsi
 * @category event
 */

$observers = array(
    array(
        'eventname' => '\core\event\course_created',
        'callback'  => 'mod_jitsi_observer::course_created',
        'priority' => 9999,
    ),
);
