<?php
require_once("../../config.php");
require_once("$CFG->dirroot/enrol/approvalenrol/locallib.php");
defined('MOODLE_INTERNAL') || die();
use \enrol_approvalenrol\approval_enrol;
use \enrol_approvalenrol\output\approval_enrol_renderer;

$courseid = required_param('courseid',PARAM_INT);
$status = required_param('status', PARAM_INT);
$page = optional_param('page', '0', PARAM_RAW);
$url = new moodle_url('/enrol/approvalenrol/approval.php', ['courseid' => $courseid, 'status' => $status, 'page' => $page]);
if (!$courseid) {
    throw new moodle_exception('Course Id cannot be 0');
}
if(!$status){
    throw new moodle_exception('Status cannot be empty');
}
$course = get_course($courseid);

if($status == approval_enrol::PENDING_REQUEST){
    $pendingrequest = true;
    //Ensure login and permissions
    require_login($course); //Ensure user is logged in and set up more nav
    $context = context_course::instance($courseid); 
}else{
    $context =context_system::instance();
}
$approvaldashboard = new \enrol_approvalenrol\approvaldashboard($course->fullname, $course->id, $status);
$titleheading = $approvaldashboard->get_title();
$PAGE->set_title($titleheading);
$PAGE->set_heading($titleheading);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->requires->css(new moodle_url('/enrol/approvalenrol/styles.css'));
$PAGE->requires->js_call_amd('enrol_approvalenrol/approvalrequests', 'init');

$requests = $approvaldashboard->get_approval_user_requests($status, $page*10);
$output = html_writer::tag('a', 'Go to Approval Dashboard',['class' => 'btn btn-secondary btn__back','href'=>new moodle_url('/enrol/approvalenrol/approval_dashboard.php',['courseid' => $courseid])]);
if(empty($requests)){
  $output .= approval_enrol_renderer::render_notice_message('No requests found');
}else{
$data = [];
$sn = ($page*10) + 1;
foreach($requests['data'] as $request){
        $tableobject = new stdClass();
        $tableobject->index = $sn;
        $tableobject->name = $request->firstname." ".$request->lastname;
        $tableobject->email = $request->email;
        if($status == approval_enrol::PENDING_REQUEST){
        $tableobject->actions = approval_enrol_renderer::render_request_action($request, $tableobject);
        }
        if($status == approval_enrol::REQUEST_ALL){
            $tableobject->status = $approvaldashboard->get_request_status($request->approval_status)?:'N/A';
        }
        $data[] = $tableobject;
        $sn++;   
}

$table = new html_table();
$table->head = ['#','Name','Email'];

if($status == approval_enrol::PENDING_REQUEST){
   array_push($table->head, 'Actions');
}else if($status == approval_enrol::REQUEST_ALL){
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
echo $OUTPUT->paging_bar($approvaldashboard->get_requestcounts_by_status(), $page, approval_enrol::PAGE_LIMIT, $url);
echo $OUTPUT->footer();