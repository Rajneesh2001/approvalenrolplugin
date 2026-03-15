<?php

namespace enrol_approvalenrol\task;
require_once($CFG->dirroot . '/enrol/approvalenrol/locallib.php');
use \enrol_approvalenrol\approval_enrol;
use \enrol_approvalenrol\local\approvalenrolrequests;

class expire_pending_requests extends \core\task\scheduled_task {

    private const EXPIRY_DAY = 7;

    public function get_name() {
        return get_string('taskexpirepending', 'enrol_approvalenrol');
    }


    public function execute() {

        $expiredate = get_config('enrol_approvalenrol', 'expirependingdays');
        
        $expiredate = (is_numeric($expiredate) && $expiredate > 2) ? (int)$expiredate: self::EXPIRY_DAY;

        $requestactions = get_config('enrol_approvalenrol', 'expirependingactions');

        $applicableactions = [approval_enrol::REQUEST_EXPIRED, approval_enrol::REQUEST_ACCEPTED, approval_enrol::REQUEST_REJECTED];

        $requestactions = is_numeric($requestactions) && in_array($requestactions, $applicableactions)?(int)$requestactions: approval_enrol::REQUEST_EXPIRED;
        
        try{
        approvalenrolrequests::bulk_update_expirypending_requests($requestactions, $expiredate);

        
        } catch(\Exception $e) {
            debugging('expirytasks fail: ' . $e->getMessage(), DEBUG_DEVELOPER);
            mtrace('Error: expiry task failed. Check logs for detail');
        }
    }

}