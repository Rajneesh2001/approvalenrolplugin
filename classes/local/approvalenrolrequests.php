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
    
    public static function get_requests_data(array $params,string $fields = '*', bool $single = false){
        global $DB;
        $table = \enrol_approvalenrol\approval_enrol::$table;
        $filtercondition = '';
        $courseid = isset($params['courseid'])??0;
        if(isset($params['courseid'])){
            $filtercondition .= 'AND courseid = :courseid';
        }
        if(isset($params['approval_status'])) {
           $filtercondition .= 'AND requeststatus =:requeststatus'; 
        }
        $filtercondition = preg_replace('(?<!\s)and', ' and', \core_text::strtolower($filtercondition));
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

    public static function insert_approver_record($userid, $courseid) {
        global $DB;
    
        $approverdata = new \stdClass();
        $approverdata->userid = $userid;
        $approverdata->courseid = $courseid;

        try{
        $approverid = $DB->insert_record('enrol_approvalenrol_approvers', $approverdata);
        if($approverid) {
            $event =\enrol_approvalenrol\event\approver_created::create([
                'objectid' => $approverid,
                'context' => \context_course::instance($courseid),
                'userid' => $userid,
                'other' => [
                    'courseid' => $courseid
                ]
            ]);

            $event->trigger();
        }
        } catch(\Exception $e){
            throw new \moodle_exception('dmlerror','enrol_approvalenrol',$e->getMessage(), '', $e->getMessage());
        }

        return $approverid;
    }

    public static function update_approver_record($id, $userid, $courseid) {
        global $DB;

        $approverdata = new \stdClass();
        $approverdata->id = $id;
        $approverdata->userid = $userid;

        try{
            $DB->update_record('enrol_approvalenrol_approvers', $approverdata);

            $event = \enrol_approvalenrol\event\approver_updated::create([
                'objectid' => $id,
                'context' => \context_course::instance($courseid),
                'other' => [
                    'courseid' => $courseid
                ]
            ]);

            $event->trigger();
        } catch(\Exception $e) {
            throw new \moodle_exception('dmlerror','enrol_approvalenrol',$e->getMessage(), '', $e->getMessage());
        }

        return true;
    }

    public static function get_course_approver_field($courseid, $field) {
       global $DB;

       return $DB->get_field('enrol_approvalenrol_approvers', $field ,[
                        'courseid' => $courseid
                    ]);
    }

    public static function is_course_approver_exists($userid, $courseid) {
        global $DB;
        
        return $DB->record_exists('enrol_approvalenrol_approvers', [
            'userid' => $userid,
            'courseid' => $courseid
        ]);
    }

     public static function upsert_course_approver(\stdClass $formdata)
    {
        if (isset($formdata) && is_numeric($formdata->userid) && !empty($formdata->userid)) {

            $approverrecordid = \enrol_approvalenrol\local\approvalenrolrequests::get_course_approver_field($formdata->courseid, 'id');
            if (!empty($approverrecordid)) {
                \enrol_approvalenrol\local\approvalenrolrequests::update_approver_record($approverrecordid, $formdata->userid, $formdata->courseid);

                \core\notification::success("Approver Updated");


            } else {

                $is_approveradded = \enrol_approvalenrol\local\approvalenrolrequests::insert_approver_record($formdata->userid, $formdata->courseid);

                if (!$is_approveradded) {
                    throw new \moodle_exception('approvernotadd', 'enrol_approvalenrol');
                }

                \core\notification::success("Approver Added");
            }
        }
    }

    public static function is_enrol_approvalenrol_enabled($courseid) {

        $instances = enrol_get_instances($courseid, true);

        $instanceavailable = false;

        foreach ($instances as $instance) {
            if($instance->enrol == 'approvalenrol') {
                $instanceavailable = true;
                break;
            }
        }

        return $instanceavailable;
    }

    /**
     * Fetch email approval verify configs
     * @param int $courseid
     * @return \stdClass $config
     */
    public static function fetch_enrolapprovalenrol_configdata($courseid) {

        global $DB;

        if(empty($courseid)) {
            throw new \moodle_exception('invalid_courseid');
        }

        if(!enrol_is_enabled('approvalenrol')) {
             throw new \moodle_exception('pluginnotenabled', 'enrol_approvalenrol');
        }

        $approver = $DB->get_field('enrol_approvalenrol_approvers', 'userid', ['courseid' => $courseid]);

        if(!$approver) {
            $approver = get_config('enrol_approvalenrol');
            if(!$approver->approvers) {
                return false;
            }
        }
        
        return $approver;
    }


    public static function remove_courseapprover($courseid, $userid): void {
        global $DB;

        try {
        $recordid = $DB->get_field('enrol_approvalenrol_approvers', 'id', ['courseid' => $courseid, 'userid' => $userid]);

        if($recordid) {
            $DB->delete_records('enrol_approvalenrol_approvers', ['id' => $recordid]);
            return;
        }
        }catch(\moodle_exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER);
        }
    }




}