<?php
defined('MOODLE_INTERNAL') || die();
define('NO_APPROVAL_REQUEST', 0);
define('REQUEST_ACCEPTED',1);
define('PENDING_REQUEST', 2);
define('REQUEST_REJECTED',3);

class approval_enrol {
    public function __construct(private int $courseid, private string $email, private string $firstname, private string $lastname){}
   
    public function has_made_enrolment_request(){
        global $DB;
        $status = $DB->get_record('user_enrol_approval_requests', [
                'email' => $this->email,
                'courseid' => $this->courseid,
            ], 'approval_status');
        
        if(!$status){
            return NO_APPROVAL_REQUEST;
        }
        
        return $status->approval_status;
    }
    public function create_request(){
         global $DB;
         try {
             $id = $DB->insert_record('user_enrol_approval_requests', [
                 'email' => $this->email,
                 'courseid' => $this->courseid,
                 'firstname' => $this->firstname,
                 'lastname' => $this->lastname,
                 'approval_status' => 2
             ]);
             if($id){
                self::sent_email_to_user();
             }
         }catch(Exception $e){
            throw new \moodle_exception($e->getMessage());
         }
         return $id;
    }
    public static function sent_email_to_user(){
        global $CFG,$USER;
        require_once($CFG->libdir . '/moodlelib.php');

        $touser = \core_user::get_noreply_user();
        $fromuser = \core_user::get_user($USER->id);
        $subject = 'Course Enrolment Approval Request';

        //dummy text
        $text = "Hi this is the approval request from the email $fromuser->email";
        
        if(!email_to_user($touser,$fromuser,$subject,$messagetext)){
            throw new Exception('Email could not be sent kindly check');
        }
    }
}

function get_approval_user_requests(){
    global $DB,$OUTPUT;
    $requests = $DB->get_records('user_enrol_approval_requests',[
        'approval_status' => PENDING_REQUEST
    ]);
    $requestarray = [];
    $sn=1;
    foreach($requests as $request){
            $tableobject = new stdClass();
            $tableobject->index = $sn;
            $tableobject->name = $request->firstname." ".$request->lastname;
            $tableobject->email = $request->email;
            $approverequrl = new moodle_url('/enrol/approvalenrol/approverequestprocess.php',[
                'courseid' => $request->courseid,
                'userid' => $request->userid,
                'requeststatus' => REQUEST_ACCEPTED
            ]);;
            $approverstatus = $OUTPUT->pix_icon('check-solid','Approve Request','enrol_approvalenrol',['class'=>'approve','id'=>'approve-id:'. $request->userid,'data-courseid' => $request->courseid, 'data-username' => $tableobject->name]);
            $rejectstatus = $OUTPUT->pix_icon('xmark-solid','Reject Request','enrol_approvalenrol',['class'=>'reject','id'=>'reject-id:'. $request->userid,'data-courseid' => $request->courseid, 'data-username' => $tableobject->name]);

            $tableobject->actions = html_writer::link($approverequrl,$approverstatus)." ".html_writer::link($approverequrl,$rejectstatus);
            
            $requestarray[] = $tableobject;
            $sn++;   
    }
    return $requestarray;
}

