<?php

namespace enrol_approvalenrol\local;

use \enrol_approvalenrol\approval_enrol;

class approvalenrolrequests{


    /**
     * Fetch User Requests.
     *
     * @param array $params Filters requests based on the provided array.
     * @param string $fields The string of fields to fetch from User Requests data. Defaults to '*'.
     * @param bool $single If true, fetch a single record; otherwise fetch all matching records. Defaults to false
     *
     * @return object|array Returns a single record object if $single is true, otherwise an array of record objects.
     */
    
    public static function get_requests_data(array $params,string $fields = 'er.*', bool $single = false){
        global $DB;
        
        $table = \enrol_approvalenrol\approval_enrol::TABLE;
        
        $filtercondition = '';
        if (isset($params['userid'])) {
            $filtercondition .= 'AND er.userid = :userid';
        }
        if(isset($params['courseid'])){
            $filtercondition .= 'AND er.courseid = :courseid';
        }
        if(isset($params['approval_status'])) {
           $filtercondition .= 'AND er.approval_status =:approval_status'; 
        }
        if($fields != 'er.*') {
            $fieldsarray = array_map('trim', explode(',', $fields));

            foreach($fieldsarray as $index=>$item) {
                $fieldsarray[$index] = 'er.' . $item;
            }

            $fields = implode(',', $fieldsarray);
        } else  {
            $fields .= ',u.email,u.firstname,u.lastname';
        }
        $filtercondition = preg_replace('/(?<!\s)and/', ' and', \core_text::strtolower($filtercondition));
        $sql = "SELECT {$fields} FROM 
        {{$table}} AS er
        JOIN {user} u ON u.id = er.userid
        WHERE 1=1 $filtercondition 
        ";
        if($single){
            return $DB->get_record_sql($sql, $params);
        }else{
            return $DB->get_records_sql($sql, $params, $params['page'], \enrol_approvalenrol\approval_enrol::PAGE_LIMIT);
        }
    }

    /**
     * Create fresh user enrolment request based on the approval status
     * @param int $courseid
     * @param int $approval_status(status of requests to be created eg.pending,accepeted,rejected)
     * @param int $userid
     * 
     * @return bool|int $newrequestid
     */
    public static function create_enrol_approval_requests($courseid, $approval_status, $userid){
        global $DB;
        $newrequest = new \stdClass();
        $newrequest->courseid = $courseid;
        $newrequest->approval_status = $approval_status;
        $newrequest->userid = $userid;
        $newrequest->timecreated = time();

        $newrequestid = $DB->insert_record(approval_enrol::TABLE, $newrequest);

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

    /**
     * Update Course enrolment request data
     * @param \stdclass $request
     * @param array $data
     * 
     * @return bool true
     * @throw dml exception
     */
    public static function update_enrol_approval_requestsdata(\stdclass $request) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        
        $request->timemodified = time();

        $DB->update_record(\enrol_approvalenrol\approval_enrol::TABLE, $request);
        
        $transaction->allow_commit();

        return true;
        
    }

    /**
     * fetch list of participants who are elligible to be approvers
     * 
     * @return ?array 
     */
    public static function fetch_approvers_candidates () {
        global $DB;

        $admins = get_admins();
        $siteadmins = array_keys($admins);

        list($notinsql, $params) = $DB->get_in_or_equal($siteadmins, SQL_PARAMS_NAMED, 'param', false);

        if($siteadmins) {
            return $DB->get_records_sql(
                "Select id,email,concat(firstname,' ', lastname) AS name from {user} where id $notinsql and id <> 1 and suspended = 0 and deleted = 0", $params
            );
        }else{
            throw new \moodle_exception('nositeadminfound','enrol_approvalenrol');
        }
    }

    /**
     * Check if user is actively enrolled via this enrolment instance
     * 
     * @param int $enrolmentinstanceid
     * @param int|stdClass $userid 
     * @return bool
     */
    public static function is_user_enrolled(int $enrolmentinstanceid, $userid) {
        global $DB;

        return $DB->record_exists('user_enrolments', [
            'enrolid' => $enrolmentinstanceid,
            'userid' => $userid,
            'status' => ENROL_USER_ACTIVE
        ]);
    }

    /**
     * check enrolment instance is enabled or not
     * 
     * @param int $courseid
     * @return bool
     */
    public static function is_enrol_approvalenrol_enabled($courseid) {

        $instances = enrol_get_instances($courseid, true);

        foreach ($instances as $instance) {
            if($instance->enrol == 'approvalenrol') {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch list of participants who were selected as approver in specified course
     * 
     * @param int $courseid
     * @param int $instanceid
     * 
     * @return array of users otherwise returns false when no participants found.
     */
    public static function get_course_approvers(int $courseid, int $instanceid):array|bool {
      global $DB;

      $userids = $DB->get_field('enrol','customtext1', ['id'=>$instanceid, 'courseid' => $courseid, 'enrol' => 'approvalenrol']);

      if(empty($userids)) {
         return false;
      }

      return array_map('intval', explode(',',$userids));

    }

    /**
     * check if the participant is approver or not
     * @param int $courseid
     * @param int $userid
     * 
     * @return bool
     */
    public static function is_course_approver($courseid, $userid) {
        $enrolinstances = enrol_get_instances($courseid, true);
        foreach($enrolinstances as $enrolinstance) {
            if($enrolinstance->enrol === 'approvalenrol') {
                if (empty($enrolinstance->customtext1)) {
                     continue;
                }

                $approverids = array_map('trim', explode(",", $enrolinstance->customtext1));

                if(in_array($userid, $approverids)) {
                    return true;
                } 
            }
        }
        return false;
    }

    /**
     * checks if the particant can access the approval request
     * @param int $courseid 
     * @param int $userid
     * 
     * @return bool
     */
    public static function can_manage_approval_requests(int $courseid, int $userid) {

            $context = \context_course::instance($courseid);

            return has_capability('enrol/approvalenrol:viewapprovaldashboard', $context) || self::is_course_approver($context->instanceid, $userid);
    }
}