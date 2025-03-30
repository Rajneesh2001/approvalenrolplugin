<?php

namespace enrol_approvalenrol\external;
require_once("$CFG->libdir/externallib.php");
use external_api;
use external_value;
use external_function_parameters;
use external_single_structure;

defined('MOODLE_INTERNAL') || die;

class updaterequests extends external_api{
    public static function execute_parameters(){
        return new external_function_parameters([
            'email' => new external_value(PARAM_RAW,'EMAIL')
        ]);
    }
    public static function execute_returns(){
        return new external_single_structure([
            'statuscode' => new external_value(PARAM_INT,'STATUS CODE'),
            'id' => new external_value(PARAM_INT,'ID'),
            'requeststatus' => new external_value(PARAM_TEXT,'REQUESTS')
        ]);
    }

    public static function execute($email){
        global $DB;
        if(!$DB->record_exists('user',['email' => $email])){
            throw new invalid_parameter_exception('Invalid User');
        }

        $result = $DB->get_record('user_enrol_approval_requests',['userid' => $userid]);
        return ['statuscode' => 200, 'id' => $result->id, 'requeststatus'=>$result->approval_status];
    }
}
