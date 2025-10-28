<?php
namespace enrol_approvalenrol;

defined('MOODLE_INTERNAL') || die();

class approval_enrol {
    public const NO_APPROVAL_REQUEST = 0;
    public const REQUEST_ACCEPTED = 1;
    public const PENDING_REQUEST = 2;
    public const REQUEST_REJECTED = 3;
    public const REQUEST_ALL = 4;
    public static $table = 'enrol_approvalenrol_requests';

    // ID of the approver to whom the approval request mail will be sent;
    private string $approverid;

    public function __construct(private int $courseid, private string $email, private string $firstname, private string $lastname, private int $userid){

        

    }
   
    /**
     * Provide the enrolment status for the loggedin user.
     * @return int
     */
    public function has_made_enrolment_request():int{
        global $DB;
        $status = $DB->get_record(self::$table, [
                'userid' => $this->userid,
                'courseid' => $this->courseid,
            ], 'approval_status');
        
        if(!$status){
            return self::NO_APPROVAL_REQUEST;
        }
        return $status->approval_status;
    }

    /**
     * Create user enrolment record in the Approval Requests table and send the email to notify the Approver
     * @return int
     */
    public function create_request():int{
         global $DB, $PAGE;
         if($this->has_made_enrolment_request() !== self::NO_APPROVAL_REQUEST){
            redirect(new \moodle_url($PAGE->url));
         }

         $id = \enrol_approvalenrol\local\approvalenrolrequests::create_enrol_approval_requests($this->courseid, self::PENDING_REQUEST, $this->userid);
         
         if($id){
            $this->send_email_to_approver();
         }
         return $id;
    }

    /**
     * send automated mail to the user
     * @return void
     */
    public function send_email_to_approver():void{
        global $CFG,$USER;
        require_once($CFG->libdir . '/moodlelib.php');
        
        $subject = get_string('course_enrol_req_sub', 'enrol_approvalenrol');
        $messagebodydata = new \stdClass();
        $messagebodydata->email = isset($fromuser->email)?$fromuser->email:NULL;
        $messagebodydata->coursename = (get_course($this->courseid))->fullname;
        $messagebodydata->url = (new \moodle_url('/enrol/approvalenrol/approval.php',['courseid' => $this->courseid, 'status' => 2]))->out(false);

        $configdata = \enrol_approvalenrol\local\approvalenrolrequests::fetch_enrolapprovalenrol_configdata($this->courseid);
        $fromuser = \core_user::get_user($USER->id);
        if (is_bool($configdata) && !$configdata) {
            self::sent_mail_to_siteadmins($fromuser, $subject, $messagebodydata);
            return;
        } else {
            $text = get_string('course_enrol_req_body', 'enrol_approvalenrol', $messagebodydata);
            $touser = \core_user::get_user(is_object($configdata)?$configdata->approvers:$configdata);
            if(!\enrol_approvalenrol\local\helper::send_message($fromuser, $touser, $subject, $text)){
                debugging(get_string('emailnotsend','enrol_approvalenrol'), DEBUG_DEVELOPER);
            }
        }
    }

    /**
     * Retrieves user enrolment requests for specific course based on approval requests.
     * @param int $request status 
     * @param int $courseid
     * 
     * @return array $requests
     */

    public static function get_approval_user_requests($requeststatus, $courseid):array{
        global $DB;

        if($courseid <=0 ){
            throw new \moodle_exception(get_string('invalid_courseid', 'enrol_approvalenrol'));
        }

        $params = ['courseid' => $courseid];
        if($requeststatus !== self::REQUEST_ALL){
            $params['approval_status'] = $requeststatus;
        }

        try{
            $requests = $DB->get_records(self::$table, $params);
            return $requests?:[];
        }catch(Exception $e) {
            throw new \moodle_exception('Failed to retrieve approval requests: ' . $e->getMessage());
        }
    }

    /**
     * Retrieves the following:
     * approved_counts: the count of users whose enrolment requests were accepted
     * rejected_counts: the count of users whose enrolment requests were rejected
     * pending_counts: the counts of users whose enrolment requests are still pending
     * total_counts: Total Enrolment Requests.
     * 
     * @param int $courseid
     * @return array $requestscountarray
     */
    public static function get_request_counts($courseid):array{
        global $DB;
        $requestcounts = ['approved_counts' => 0,'rejected_counts' => 0,'pending_counts' => 0,'total_counts' => 0];
        $sql = "SELECT case when approval_status = " .self::REQUEST_ACCEPTED. " then 'approved_counts'
                when approval_status = " . self::REQUEST_REJECTED . " then 'rejected_counts'
                else 'pending_counts' end AS status
                ,count(approval_status) AS request_counts from {".
                self::$table ."} 
                where courseid = :courseid group by approval_status";

        $requestsarray = $DB->get_records_sql($sql, [
                                    'courseid' => $courseid,
                                ]);
        foreach($requestsarray as $requests){
            $requestcounts[$requests->status] = $requests->request_counts?:0;
            $requestcounts['total_counts'] += $requests->request_counts;
        }

        return $requestcounts;
    }


    public static function sent_mail_to_siteadmins($sender, $subject, $messagebody) {
        error_log('Hello World');
        $admins = get_admins();
        foreach($admins as $admin) {
            error_log(print_r($admin, true));
            $messagebody->email = $admin->email;
            $text = get_string('course_enrol_req_body', 'enrol_approvalenrol', $messagebody);
            \enrol_approvalenrol\local\helper::send_message($sender, $admin, $subject, $text);
        }
        
        return true;
    }
}



