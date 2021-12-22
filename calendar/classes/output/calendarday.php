<?php

namespace core_calendar\output;
defined('MOODLE_INTERNAL') || die();

class calendarday
{
    private $events;

    public function getevents(){
        return $this->events;
    }

    public function __construct($data)
    {
       $dayevents = $data->events;
               foreach($dayevents as $event){

               $eventdate = date('H', $event->timestart);

                   switch($eventdate){
                       case 0: $this->events['0h'][] = $event; break;
                       case 1: $this->events['1h'][] = $event; break;
                       case 2: $this->events['2h'][] = $event; break;
                       case 3: $this->events['3h'][] = $event; break;
                       case 4: $this->events['4h'][] = $event; break;
                       case 5: $this->events['5h'][] = $event; break;
                       case 6: $this->events['6h'][] = $event; break;
                       case 7: $this->events['7h'][] = $event;break;
                       case 8: $this->events['8h'][] = $event; break;
                       case 9: $this->events['9h'][] = $event; break;
                       case 10: $this->events['10h'][] = $event; break;
                       case 11: $this->events['11h'][] = $event; break;
                       case 12: $this->events['12h'][] = $event; break;
                       case 13: $this->events['13h'][] = $event; break;
                       case 14: $this->events['14h'][] = $event; break;
                       case 15: $this->events['15h'][] = $event; break;
                       case 16: $this->events['16h'][] = $event; break;
                       case 17: $this->events['17h'][] = $event; break;
                       case 18: $this->events['18h'][] = $event; break;
                       case 19: $this->events['19h'][] = $event; break;
                       case 20: $this->events['20h'][] = $event; break;
                       case 21:  $this->events['21h'][] = $event; break;
                       case 22:  $this->events['22h'][] = $event; break;
                       case 23:  $this->events['23h'][] = $event; break;
                       case 24:  $this->events['24h'][] = $event; break;
                   }
               }
    }

    public function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
