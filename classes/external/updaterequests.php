<?php

namespace enrol_approvalenrol\external;
require_once("$CFG->libdir/externallib.php");
use external_api;
use external_value;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use \enrol_approvalenrol\local\approvalenrolrequests;
use \enrol_approvalenrol\approval_enrol;

defined('MOODLE_INTERNAL') || die;

class updaterequests extends external_api{
    public static function execute_parameters(){
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT,'USER ID'),
            'courseid' => new external_value(PARAM_INT,'COURSE ID'),
            'requeststatus' => new external_value(PARAM_RAW,'REQUEST STATUS')
        ]);
    }
    public static function execute_returns(){
        return new external_single_structure([
            'statuscode' => new external_value(PARAM_INT,'STATUS CODE'),
            'data' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT,'ID'),
                'email' => new external_value(PARAM_RAW, 'EMAIL'),
                'firstname' => new external_value(PARAM_TEXT,'FIRST NAME'),
                'lastname' => new external_value(PARAM_TEXT,'LAST NAME'),
                'approval_status' => new external_value(PARAM_TEXT,'REQUEST STATUS'),
                'courseid' => new external_value(PARAM_INT,'COURSE ID'),
                'userid' => new external_value(PARAM_INT,'USER ID'),
            ]),),
            'requeststatus' => new external_value(PARAM_TEXT,'REQUESTS',VALUE_OPTIONAL),
            'errormessage' => new external_value(PARAM_TEXT,'ERROR MESSAGE', VALUE_OPTIONAL)
        ]);
    }

    public static function execute($userid,$courseid,$requeststatus){
        global $DB,$CFG;
        require_once("$CFG->dirroot/enrol/approvalenrol/locallib.php");
        
        // Start Transaction
        $transaction = $DB->start_delegated_transaction();
        try{
            $DB->execute(
                'UPDATE {'. approval_enrol::$table .'} SET approval_status = :status WHERE userid = :userid and courseid= :courseid',
                ['status' => $requeststatus, 'userid' => $userid, 'courseid' => $courseid]
            );
            
        // Commit Transaction
            $updatedusersdata = approvalenrolrequests::get_requests_data(['courseid' => $courseid, 'approval_status' => approval_enrol::PENDING_REQUEST]);

            $transaction->allow_commit();

            $event = \enrol_approvalenrol\event\approval_requests_updated::create([
                'courseid' => $courseid,
                'context' => \context_system::instance(),
                'other' => [
                    'requeststatus' => $requeststatus,
                    'user'=> $userid
                ]
            ]);
            //Set the course name in the event
            $event->set_coursefullname(get_course($courseid)->fullname);
            $event->trigger();
            return ['statuscode' => 200, 'data' => $updatedusersdata,'successmessage' => get_string('successmsg','enrol_approvalenrol')];
        }catch(\Exception $e){
            return ['statuscode' => 504, 'errormessage' => $e->getMessage()];
        }
    }
}