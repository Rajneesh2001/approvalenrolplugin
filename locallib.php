<?php
namespace enrol_approvalenrol;

defined('MOODLE_INTERNAL') || die();

class approval_enrol {
    public const NO_APPROVAL_REQUEST = 0;
    public const REQUEST_ACCEPTED = 1;
    public const PENDING_REQUEST = 2;
    public const REQUEST_REJECTED = 3;
    public const REQUEST_ALL = 4;

    public function __construct(private int $courseid, private string $email, private string $firstname, private string $lastname){}
   
    public function has_made_enrolment_request():int{
        global $DB;
        $status = $DB->get_record('user_enrol_approval_requests', [
                'email' => $this->email,
                'courseid' => $this->courseid,
            ], 'approval_status');
        
        if(!$status){
            return self::NO_APPROVAL_REQUEST;
        }
        
        return $status->approval_status;
    }
    public function create_request():int{
         global $DB;
         if($this->has_made_enrolment_request() !== self::NO_APPROVAL_REQUEST){
            return 0;
         }
         try {
             $id = $DB->insert_record('user_enrol_approval_requests', [
                 'email' => $this->email,
                 'courseid' => $this->courseid,
                 'firstname' => $this->firstname,
                 'lastname' => $this->lastname,
                 'approval_status' => self::PENDING_REQUEST,
             ]);
             if($id){
                self::send_email_to_user();
             }
         }catch(Exception $e){
            throw new \moodle_exception($e->getMessage());
         }
         return $id;
    }
    public static function send_email_to_user():void{
        global $CFG,$USER;
        require_once($CFG->libdir . '/moodlelib.php');

        $touser = \core_user::get_noreply_user();
        $fromuser = \core_user::get_user($USER->id);
        $subject = 'Course Enrolment Approval Request';

        //dummy text
        $text = "Hi this is the approval request from the email {$fromuser->email}";
        
        if(!email_to_user($touser,$fromuser,$subject,$text)){
            throw new Exception('Email could not be sent kindly check');
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
            throw new \moodle_exception('Course Id must be positive');
        }

        $params = ['courseid' => $courseid];
        if($requeststatus !== self::REQUEST_ALL){
            $params['approval_status'] = $requeststatus;
        }

        try{
            $requests = $DB->get_records('user_enrol_approval_requests', $params);
            return $requests?:[];
        }catch(Exception $e) {
            throw new \moodle_exception('Failed to retrive approval requests: ' . $e->getMessage());
        }
    }

    public static function enrol_approvalenrol_requestcounts($courseid):array{
        global $DB;
        $requestcounts = ['approved_counts' => 0,'rejected_counts' => 0,'pending_counts' => 0,'total_counts' => 0];
        $sql = "SELECT case when approval_status = 1 then 'approved_counts'
                when approval_status = 3 then 'rejected_counts'
                else 'pending_counts' end AS status
                ,count(approval_status) AS request_counts from {user_enrol_approval_requests} 
                where courseid = :courseid group by approval_status";

        $requestscountarray = $DB->get_records_sql($sql, [
                                    'courseid' => $courseid,
                                ]);
        foreach($requestscountarray as $requests){
            $requestcounts[$requests->status] = $requests->request_counts?:0;
            $requestcounts['total_counts'] += $requests->request_counts;
        }

        return $requestcounts;
    }
}



