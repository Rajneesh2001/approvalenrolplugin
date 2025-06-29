<?php
require_once("../../config.php");
require_once("$CFG->dirroot/enrol/approvalenrol/locallib.php");
defined('MOODLE_INTERNAL') || die();
use \enrol_approvalenrol\approval_enrol;
use \enrol_approvalenrol\output\approval_enrol_renderer;

$url = new moodle_url('/enrol/approvalenrol/approval.php');
$courseid = required_param('courseid',PARAM_INT);
if (!$courseid) {
    throw new moodle_exception('Course Id cannot be 0');
}
$course = get_course($courseid);
$status = optional_param('status', null, PARAM_INT);
if($status == approval_enrol::PENDING_REQUEST){
    $pendingrequest = true;
    //Ensure login and permissions
    require_login($course); //Ensure user is logged in and set up more nav
    $context = context_course::instance($courseid); 
}else{
    $context =context_system::instance();
}
$approvalstatusarray = [
        '1' => 'Approved',
        '2' => 'Pending',
        '3' => 'Rejected',
    ];

$titleheading = get_string($status !=4 ? 'approval_requests': 'total_requests', 'enrol_approvalenrol', $approvalstatusarray[$status]);
$PAGE->set_title($titleheading);
$PAGE->set_heading($titleheading);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->requires->css(new moodle_url('/enrol/approvalenrol/styles.css'));
$PAGE->requires->js_call_amd('enrol_approvalenrol/approvalrequests', 'init');

$requests = approval_enrol::get_approval_user_requests($status, $courseid);
$output = html_writer::tag('a', 'Go to Approval Dashboard',['class' => 'btn btn-secondary btn__back','href'=>new moodle_url('/enrol/approvalenrol/approval_dashboard.php',['courseid' => $courseid])]);
if(empty($requests)){
  $output .= html_writer::div(
        'No Records Found',
        'alert alert-info'
    );
}else{
$data = [];
$sn=1;
foreach($requests as $request){
        $tableobject = new stdClass();
        $tableobject->index = $sn;
        $tableobject->name = $request->firstname." ".$request->lastname;
        $tableobject->email = $request->email;
        if($status == approval_enrol::PENDING_REQUEST){
        $tableobject->actions = approval_enrol_renderer::render_request_action($request, $tableobject);
        }
        if($status == approval_enrol::REQUEST_ALL){
            $tableobject->status = $approvalstatusarray[$request->approval_status]?:'N/A';
        }
        $data[] = $tableobject;
        $sn++;   
}

$table = new html_table();
$table->head = ['#','Name','Email'];
if($status == approval_enrol::PENDING_REQUEST){
   array_push($table->head, 'Actions');
}else if($allrequest){
   array_push($table->head, 'Approval Status');
}
$table->data = $data;
$loader = '';
$loader.= html_writer::start_div('loader_container');
$loader.= html_writer::div('','loader');
$loader.= html_writer::end_div();
$tablehtml =  html_writer::table($table);
}
echo $OUTPUT->header();
echo $output;
if(isset($loader)) echo $loader;
if(isset($tablehtml)) echo $tablehtml;
echo $OUTPUT->footer();