<?php

namespace enrol_approvalenrol\local;
require_once("$CFG->dirroot/enrol/approvalenrol/locallib.php");

class helper{

    private string $table;

    public function __construct( private \moodle_database $db){
        $this->table = \enrol_approvalenrol\approval_enrol::$table;
    }

    public static function get_helper_instance(\moodle_database $db){
        return new static($db);
    }

    /**
     * Fetch User Requests based on the conditions
     * 
     * @param array $params 
     * @param string $fields
     */
    public static function get_filtered_userrequests(\moodle_database $db,array $params, string $fields){
        $dbobject = self::get_helper_instance($db);
        return $dbobject->db->get_records($dbobject->table,
            $params,
            '',
            $fields
        );
    }

    /**
     * Use Moodle message api to send the mail as well as moodle notification to the intended user
     *
     * @param \stdclass $sender
     * @param \stdclass $receiver
     * @param string $subject 
     * @param string $msg
     * @return bool true if message Id is return by message_send() or false if there was problem
     */
    public static function send_message(\stdclass $sender, \stdclass $receiver,string $subject,string $msg){
        $message = new \core\message\message();   
        $message->component = 'enrol_approvalenrol';
        $message->name = 'approval_notifications';
        $message->notification = 1;
        $message->subject = $subject;
        $message->fullmessageformat = FORMAT_HTML;
        $message->userfrom = $sender;
        $message->userto = $receiver;
        $message->fullmessage = $msg;
        $message->fullmessagehtml = $msg;
        
        return is_int(\message_send($message))?true:false;
}

}