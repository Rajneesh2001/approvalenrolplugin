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

    //Email Triggered context
    public const EMAIL_REQUESTED = 6;

    // ID of the approver to whom the approval request mail will be sent;
    private string $approverid;
    private string $coursefullname;

    public function __construct(private int $courseid, private string $email, private string $firstname, private string $lastname, private int $userid){
    
      $this->coursefullname = (get_course($this->courseid))->fullname;    

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
            $this->notify_approveruser();
         }
         return $id;
    }

    /**
     * send automated mail to the approver
     * @param \stdClass $sender
     * @param \stdClass $approver
     * @param string $url
     * @param string $subject
     * 
     * @return bool
     */
    public function send_mail_to_approver(\stdClass $sender,\stdClass $approver, string $url, string $subject):bool{
        global $CFG;
        require_once($CFG->libdir . '/moodlelib.php');
        
        $fromemail = isset($sender->email)?$sender->email:NULL;
        if (empty($fromemail)) {
            debugging('nosender', DEBUG_DEVELOPER);
        }

        $message = $this->generate_message_body(self::EMAIL_REQUESTED, [
            'email' => $fromemail, 
            'url' => $url]);
            
        if(!\enrol_approvalenrol\local\helper::send_message($sender, $approver, $subject, $message)){
            return false;
        } 
            
        return true;    
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
            // $requests = $DB->get_records(self::$table, $params);
            $requests = \enrol_approvalenrol\local\approvalenrolrequests::get_requests_data($params);
            return $requests?:[];
        }catch(\Exception $e) {
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

    /**
     * Notify approvers about course approval request
     * 
     * @return void
     */
    private function notify_approveruser() {
        $configdata = \enrol_approvalenrol\local\approvalenrolrequests::fetch_enrolapprovalenrol_configdata($this->courseid);
        
        $fromuser = \core_user::get_user($this->userid);
        $url = (new \moodle_url('/enrol/approvalenrol/approval.php',['courseid' => $this->courseid, 'status' => 2]))->out(false);
        $subject = get_string('course_enrol_req_sub', 'enrol_approvalenrol');
        
        if (is_bool($configdata)) {
            $debugcontext = 'admins';
            $sentmail = $this->send_mail_to_siteadmins($fromuser, $url, $subject);
        } else {
            $debugcontext = 'approver';
            $approver = \core_user::get_user(is_object($configdata)?$configdata->approvers:$configdata);
            $sentmail = $this->send_mail_to_approver($fromuser,$approver, $url, $subject);
        }

        if (!$sentmail) {
            debugging(get_string('emailnotsend','enrol_approvalenrol', ['context' => $debugcontext]), DEBUG_DEVELOPER);
        }

    }


    /**
     * Send Automated mail to all the site admins
     * 
     * @param \stdClass $sender
     * @param string $url
     * @param string $subject
     * 
     * @return bool
     */
    private function send_mail_to_siteadmins(\stdClass $sender, string $url, string $subject) {
        $admins = get_admins();
        foreach($admins as $admin) {
            $message = $this->generate_message_body(self::EMAIL_REQUESTED,[
                'email' => $admin->email,
                'url' => $url
            ]);
            if(!\enrol_approvalenrol\local\helper::send_message($sender, $admin, $subject, $message)){
                return false;
            }
        }
        
        return true;
    }

    /**
     * generate message body for email and moodle notifications\
     * 
     * @param string $context set the message body according to contexts
     * Email Request or Pending Email
     * @param array $requestdata
     * 
     * @return string $messagebody else throw an error 
     */
    private function generate_message_body(string $context, array $requestdata) {
        if(empty($context)) {
            throw new \moodle_exception('empty_context', 'enrol_approvalenrol');
        }

        $messagebody = new \stdClass();
        $messagebody->email = $requestdata['email'];
        $messagebody->coursename = $this->coursefullname;
        $messagebody->url = $requestdata['url'];
        
        if($context == self::EMAIL_REQUESTED) {
            return get_string('course_enrol_req_body', 'enrol_approvalenrol', $messagebody);
        } else if($context === self::PENDING_REQUEST) {

        }
    }
}



