<?php
namespace enrol_approvalenrol\event;

use core\event\base;

class approval_requests_updated extends base{

    /**
     * @var string 
     * The course full name.
     */
    protected $coursefullname;

    public static function get_name(){
        return get_string('event_approval_requests_updated', 'enrol_approvalenrol');
    }

    public function init(){
        // $this->data['objecttable'] = \enrol_approvalenrol\approval_enrol::$table;
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::USER_OTHER;
    }

    public function set_coursefullname($coursefullname){
        $this->coursefullname = $coursefullname;
    }

    public function get_coursefullname(){
        return $this->coursefullname;
    }
}

