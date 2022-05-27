<?php

namespace block_groupstats\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use user_picture;

class groupstats implements renderable, templatable
{
    private $groupmembers;
    private $groupmemberscount = 0;
    private $colorindex;

    public function __construct($groupid)
    {
        $this->groupmembers = groups_get_members($groupid);
        $this->groupmemberscount = count($this->groupmembers);
        $this->collect_data();
        $this->count_data();
        $this->get_metric();
    }

    private $picturesrc;
    private $userfullname;
    private $userprofile;

    private $test;

    private function collect_data()
    {
        global $CFG, $PAGE;
        $users = $this->groupmembers;
        $attendanceindex = 0;
        $paymentindex = 0;
        foreach ($users as $user) {
            $attendanceindex++;
            $this->colorindex = rand(1, 10);
            if ($attendanceindex > 4) {
                $attendanceindex = 1;
            }
            $paymentindex++;
            if ($paymentindex > 2) {
                $paymentindex = 1;
            }
            $this->picturesrc = $this->get_user_image($user);
            $this->userfullname = $user->firstname . ' ' . $user->lastname;
            $this->userprofile = $CFG->wwwroot . '/user/view.php?id=' . $user->id . '&amp;course=' . $this->course->id;
            $this->get_student_performance($user);
            $this->get_student_attendance($user, $attendanceindex);
            $this->get_student_payment($user, $paymentindex);
            //test: collect all students
            $this->set_user_array($this->test);
        }
    }

    private function get_user_image($user) {
        global $USER,$PAGE; 
        $user_picture=new user_picture($user);
        if($user->picture){
            $src=$user_picture->get_url($PAGE);
        }
        else{
            $src=$user_picture->get_url($PAGE, null,  false);
        }
        return $src;
    }

    /**
     *
     * Grades statistics
     *
     **/
    private $wellstudents;
    private $goodstudents;
    private $okaystudents;
    private $badstudents;

    /*private $wellgrades;
    private $goodgrades;
    private $okaygrades;
    private $badgrades;
*/
    private $performance;
    private $kquality;
    private $learning;

    private $found;

    private function get_student_performance($student)
    {
        $grades = $this->get_student_grades_by_student_id($student->id);
        $gradescount = count($grades);
        //for($i = 1; $i < $gradescount; $i++)
       /* $index = 0;
        foreach($grades as $singlegrade)
        {
            if($index == 0)
            {
                $index++;
                continue;
            }
            //$thegrade = $grades[$i];
            $thegrade = $singlegrade;
            if(isset($thegrade->finalgrade))
            {
                $currentgrade = $thegrade->finalgrade / $thegrade->rawgrademax;

                $this->found =  $gradescount;
                if ($currentgrade < 0.65) {
    
                } elseif ($currentgrade >= 0.65 && $currentgrade < 0.75) {
                    $this->okaygrades[] = $currentgrade;
    
                } elseif ($currentgrade >= 0.75 && $currentgrade < 0.85) {
                    $this->goodgrades[] = $currentgrade;
    
                } elseif ($currentgrade > 0.85) {
                    $this->wellgrades[] = $currentgrade;
    
                }
            }  
        }
*/
        $total = array_shift($grades);
        $gradetotal = $total->finalgrade / $total->rawgrademax;

        //based on 100 points
        if ($gradetotal < 0.65) {
            $this->set_user_array($this->badstudents);
        } elseif ($gradetotal >= 0.65 && $gradetotal < 0.75) {
            $this->set_user_array($this->okaystudents);
        } elseif ($gradetotal >= 0.75 && $gradetotal < 0.85) {
            $this->set_user_array($this->goodstudents);
        } elseif ($gradetotal > 0.85) {
            $this->set_user_array($this->wellstudents);
        }
    }

    
    private function get_metric()
    {
        /*
            Успеваемость = (кол-во "5" + кол-во "4" + "кол-во "3") / общее количество учащихся

            Качество знаний = (кол-во "5" + кол-во "4") / общее количество учащихся

            Обученность = (кол-во "5" + кол-во "4" * 0,64 + кол-во "3" * 0,36 + кол-во "2" * 0,16 + кол-во "н/а" * 0,08 ) / общее количество учащихся
        */
        /*$wellcount = count($this->wellgrades);
        $goodcount = count($this->goodgrades);
        $okaycount = count($this->okaygrades);
        $badcount = count($this->badgrades);
*/
        $performance = (($this->wellstudentscount + $this->goodstudentscount + $this->okaystudentscount) / $this->groupmemberscount) * 100;
        $kquality = (($this->wellstudentscount + $this->goodstudentscount) / $this->groupmemberscount) * 100;
        $learning = (($this->wellstudentscount + ($this->goodstudentscount * 0.64) + ($this->okaystudentscount * 0.36) + ($this->badstudentscount * 0.16)) / $this->groupmemberscount) * 100;
    
        $this->performance = round($performance, 2);
        $this->kquality = round($kquality, 2);
        $this->learning = round($learning, 2);
    }
  

    private function get_student_grades_by_student_id($studentid, $fields = 'g.*')
    {
        global $DB;
        return $DB->get_records_sql("SELECT $fields
                                   FROM {grade_grades} g, {user} u
                                  WHERE g.userid = u.id AND u.id = ?
                               ORDER BY finalgrade DESC", array($studentid));
    }

    /**
     *
     * Attending statistics
     *
     **/
    private $wellattendingstudents;
    private $goodattendingstudents;
    private $okayattendingstudents;
    private $badattendingstudents;

    private function get_student_attendance($student, $index)
    {
        switch ($index) {
            case 1: 
                $this->set_user_array($this->wellattendingstudents);
                break;
                case 2: 
                    $this->set_user_array($this->goodattendingstudents);
                    break;
                    case 3: 
                        $this->set_user_array($this->okayattendingstudents);
                        break;
                        case 4: 
                            $this->set_user_array($this->badattendingstudents);
                            break;
        }
    }

    /**
     *
     * Payment statistics
     *
     **/
    private $paystudents;
    private $didntpaystudents;

    private function get_student_payment($student, $index)
    {

        switch ($index) {
            case 1: 
                $this->set_user_array($this->paystudents);
                break;
                case 2: 
                    $this->set_user_array($this->didntpaystudents);
                    break;
                    
        }
    }

    /**
     *
     * Data exports
     *
     **/
    public function export_for_template(renderer_base $output)
    {
        $groupPerformance = $this->performance;
        $groupKquality = $this->kquality;
        $groupLearning = $this->learning;
        $groupFound = $this->found;

        $wellStudentsCount = $this->wellstudentscount;
        $goodStudentsCount = $this->goodstudentscount;
        $okayStudentsCount = $this->okaystudentscount;
        $badStudentsCount = $this->badstudentscount;

        $exportData = [
            'groupmemberscount' => $this->groupmemberscount,

            'wellstudentscount' => $wellStudentsCount,
            'goodstudentscount' => $goodStudentsCount,
            'okaystudentscount' => $okayStudentsCount,
            'badstudentscount' => $badStudentsCount,

            'wellattendingstudentscount' => $this->wellattendingstudentscount,
            'goodattendingstudentscount' => $this->goodattendingstudentscount,
            'okayattendingstudentscount' => $this->okayattendingstudentscount,
            'badattendingstudentscount' => $this->badattendingstudentscount,

            'paystudentscount' => $this->paystudentscount,
            'didntpaystudentscount' => $this->didntpaystudentscount,

            'wellstudents' => $this->wellstudents,
            'goodstudents' => $this->goodstudents,
            'okaystudents' => $this->okaystudents,
            'badstudents' => $this->badstudents,

            'wellattendingstudents' => $this->wellattendingstudents,
            'goodattendingstudents' => $this->goodattendingstudents,
            'okayattendingstudents' => $this->okayattendingstudents,
            'badattendingstudents' => $this->badattendingstudents,

            'paystudents' => $this->paystudents,
            'didntpaystudents' => $this->didntpaystudents
        ];

        if ( !is_nan($groupPerformance) ) {
            $exportData['performance'] = $groupPerformance;
        }

        if ( !is_nan($groupKquality) ) {
            $exportData['kquality'] = $groupKquality;
        }

        if ( !is_nan($groupLearning) ) {
            $exportData['learning'] = $groupLearning;
        }

        if ( !is_nan($groupFound) ) {
            $exportData['found'] = $groupFound;
        }

        if (
            $wellStudentsCount == 0 &&
            $goodStudentsCount == 0 &&
            $okayStudentsCount == 0 &&
            $badStudentsCount == 0
        ) {
            $exportData['performanceEmptyImageURL'] = $output->image_url('blank_diagram', 'block_groupstats')->out();
        }

        return $exportData;
    }

    public function export_for_js()
    {
        return [
            'groupmemberscount' => $this->groupmemberscount,

            'wellstudentscount' => $this->wellstudentscount,
            'goodstudentscount' => $this->goodstudentscount,
            'okaystudentscount' => $this->okaystudentscount,
            'badstudentscount' => $this->badstudentscount,

            'wellattendingstudentscount' => $this->wellattendingstudentscount,
            'goodattendingstudentscount' => $this->goodattendingstudentscount,
            'okayattendingstudentscount' => $this->okayattendingstudentscount,
            'badattendingstudentscount' => $this->badattendingstudentscount,

            'paystudentscount' => $this->paystudentscount,
            'didntpaystudentscount' => $this->didntpaystudentscount
        ];
    }

    /**
     *
     * Counters
     *
     **/
    private $wellstudentscount = 0;
    private $goodstudentscount = 0;
    private $okaystudentscount = 0;
    private $badstudentscount = 0;

    private $wellattendingstudentscount = 0;
    private $goodattendingstudentscount = 0;
    private $okayattendingstudentscount = 0;
    private $badattendingstudentscount = 0;

    private $paystudentscount = 0;
    private $didntpaystudentscount = 0;

    private function count_data()
    {
        $this->wellstudentscount = count($this->wellstudents);
        $this->goodstudentscount = count($this->goodstudents);
        $this->okaystudentscount = count($this->okaystudents);
        $this->badstudentscount = count($this->badstudents);

        $this->wellattendingstudentscount = count($this->wellattendingstudents);
        $this->goodattendingstudentscount = count($this->goodattendingstudents);
        $this->okayattendingstudentscount = count($this->okayattendingstudents);
        $this->badattendingstudentscount = count($this->badattendingstudents);

        $this->paystudentscount = count($this->paystudents);
        $this->didntpaystudentscount = count($this->didntpaystudents);
    }

    /**
     *
     * Other methods
     *
     **/
    private function set_user_array(&$array)
    {
        $array[] = array(
            'fullname' => $this->userfullname,
            'picturesrc' => $this->picturesrc,
            'userprofile' => $this->userprofile,
            'colorindex' => $this->colorindex
        );
    }

    private function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}
