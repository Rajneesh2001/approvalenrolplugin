<?php
namespace enrol_approvalenrol;

defined('MOODLE_INTERNAL') || die();

class approval_enrol {
    public const NO_APPROVAL_REQUEST = 0;
    public const REQUEST_ACCEPTED = 1;
    public const PENDING_REQUEST = 2;
    public const REQUEST_REJECTED = 3;
    public const REQUEST_ALL = 4;
    public const ENROL_STATUS_REVOKED = 5;
    public const ENROL_STATUS_UNENROLED = 6;
    public const PAGE_LIMIT = 10;
    public static $table = 'enrol_approvalenrol_requests';

    //Email Triggered context
    public const EMAIL_REQUESTED = 6;

    private string $coursefullname; 

    private ?\stdClass $requestdata;
    
    public function __construct(private int $courseid, private string $userid){
    
      $this->coursefullname = (get_course($this->courseid))->fullname;    

      $this->requestdata = \enrol_approvalenrol\local\approvalenrolrequests::get_requests_data([
        'userid' => $this->userid,
        'courseid' => $this->courseid
      ], single: true)?:NULL;

    }

    /**
     * Fetch User approval Request status
     * @return int 
     */
    public function get_request_status():int {
        //Check if request exists
        if($this->requestdata === null){
            return self::NO_APPROVAL_REQUEST;
        }
        //Check if user is unenrolled
        if($this->requestdata->is_unenrolled) {
            return self::ENROL_STATUS_UNENROLED;
        }

        //check if user is suspended in the course
        if($this->requestdata->is_revoked) {
            return self::ENROL_STATUS_REVOKED;
        }

        return $this->requestdata->approval_status;
    }
    /**
     * Create user enrolment record in the Approval Requests table and send the email to notify the Approver
     * @param \stdClass $request
     * @param bool $shouldnotify if true an email notification is trigger to approver, else skip the notification part.
     * @return int
     */
    public function create_request($request, $shouldnotify):int{
         global $PAGE;

         if(is_null($request)) {
            $request = self::PENDING_REQUEST;
         }

         $id = \enrol_approvalenrol\local\approvalenrolrequests::create_enrol_approval_requests($this->courseid, $request, $this->userid);
         
         if($id){
            if($shouldnotify) {
                $this->notify_approveruser();
            }
         }else {
            throw new \moodle_exception('Record not Inserted', 'enrol_approvalenrol');
        }
         return $id;
    }

    /**
     * Update User Request as per $data array
     * @param $dataarray
     * @return true
     */
    public function update_request(array $dataarray, bool $shouldnotify):bool {

        if(is_null($this->requestdata)) {
            debugging('Cannot update request data', DEBUG_DEVELOPER);
        }

        $updaterequest = new \stdClass();
        $updaterequest->id = $this->requestdata->id;

        foreach($dataarray as $field => $data) {
            $updaterequest->{$field} = $data;
        }
        try {
        \enrol_approvalenrol\local\approvalenrolrequests::update_enrol_approval_requestsdata($updaterequest);
        if($shouldnotify) {
            $this->notify_approveruser();
        }
        return true;
        } catch(\moodle_exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
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



