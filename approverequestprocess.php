<?php

require_once("../../config.php");
require_once("$CFG->dirroot/enrol/approvalenrol/locallib.php");
defined('MOODLE_INTERNAL') || die();

$courseid = required_param('courseid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$requeststatus = required_param('requeststatus', PARAM_INT);
if(!$courseid || !$userid ){
    throw new moodle_exception('courseid and userid cannot be 0');
}
$response =  \enrol_approvalenrol\external\updaterequests::execute($userid, $courseid, $requeststatus);
if($response){
    $userdisplayname = (\core_user::get_user($userid))->firstname;
    $requeststatus =  $requeststatus?'approved':'rejected';
    \core\notification::add("User $userdisplayname request has been $requeststatus",\core\output\notification::NOTIFY_INFO);

    redirect(new moodle_url('/enrol/approvalenrol/approval.php',[
        'courseid' => $courseid,
        'status' => '2'
    ]));
}else{
}

