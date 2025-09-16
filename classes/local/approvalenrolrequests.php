<?php

namespace enrol_approvalenrol\local;

use \enrol_approvalenrol\approval_enrol;

class approvalenrolrequests{


    /**
     * Fetch User Requests
     * @param string $fields the strings of fields to be fetch from User Requests data
     * @param int $courseid , fetch the records/record on the basis of courses(course id)  
     *             if set 0 then fetch the records for all the courses
     * @param bool $single , if true then fetch all the records of user data , else fetch single record
     */
    
    public static function get_requests_data($fields, $courseid, $single){
        global $DB;
        $table = \enrol_approvalenrol\approval_enrol::$table;
        $params = [];
        $filtercondition = '';
        if($courseid){
            $filtercondition .= 'AND courseid = :courseid';
            $params['courseid'] = $courseid; 
        }
        $sql = "SELECT {$fields} FROM 
        {{$table}} AS er
        JOIN {user} u ON u.id = er.userid
        WHERE 1=1 $filtercondition
        ";
        
        if($single){
            return $DB->get_record_sql($sql, $params);
        }else{
            return $DB->get_records_sql($sql, $params);
        }
    }


    public static function create_enrol_approval_requests($courseid, $approval_status, $userid){
        global $DB;

        $newrequest = new \stdClass();
        $newrequest->courseid = $courseid;
        $newrequest->approval_status = $approval_status;
        $newrequest->userid = $userid;
        $newrequest->timecreated = time();

        $newrequestid = $DB->insert_record(approval_enrol::$table, $newrequest);

        $coursecontext = \context_course::instance($courseid);

        $event = \enrol_approvalenrol\event\request_created::create([
            'objectid' => $newrequestid,
            'context' => $coursecontext,
            'userid' => $userid,
            'other' => [
                'courseid' => $courseid
            ]
        ]);

        $event->trigger();

        return $newrequestid;
    }

}