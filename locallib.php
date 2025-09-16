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

    public function __construct(private int $courseid, private string $email, private string $firstname, private string $lastname, private int $userid){}
   
    /**
     * Provide the enrolment status for the loggedin user.
     * @return int
     */
    public function has_made_enrolment_request():int{
        $status = \enrol_approvalenrol\local\approvalenrolrequests::get_requests_data('approval_status', $this->courseid, true);

        if(!$status){
            return self::NO_APPROVAL_REQUEST;
        }
        return $status->approval_status;
    }

    /**
     * Create user enrolment record in the Approval Requests table and send the email to notify the Approver
     * @return int
     */
    public function create_enrolment_request():int{
         global $DB;
         if($this->has_made_enrolment_request() !== self::NO_APPROVAL_REQUEST){
            throw new \moodle_exception(get_string('requestexists', 'enrol_approvalenrol'));
         }
         
         $id = \enrol_approvalenrol\local\approvalenrolrequests::create_enrol_approval_requests($this->courseid, self::PENDING_REQUEST, $this->userid);
         
         if($id){
            $this->send_email_to_approver();
         }
         return $id;
    }

    /**
     * send automated mail to the approver
     * @return void
     */
    public function send_email_to_approver():void{
        global $CFG,$USER;

        if(!$this->userid){
            $this->userid = $USER->id;
        }

        require_once($CFG->libdir . '/moodlelib.php');

        $touser = \core_user::get_noreply_user();
        $fromuser = \core_user::get_user($this->userid);
        $subject = 'Course Enrolment Approval Request';

        // var_dump($fromuser);
        var_dump($touser);die;
        //dummy text
        $text = "Hi this is the approval request from the email {$fromuser->email}";
        
        if(!\enrol_approvalenrol\local\helper::send_message(\core_user::get_support_user(), $touser, $subject, $text)){
            throw new \moodle_exception(get_string('emailnotsend','enrol_approvalenrol'));
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
}



