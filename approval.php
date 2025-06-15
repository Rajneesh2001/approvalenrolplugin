<?php
require_once("../../config.php");
require_once("$CFG->dirroot/enrol/approvalenrol/locallib.php");
defined('MOODLE_INTERNAL') || die();
require_login();
$url = new moodle_url('/enrol/approvalenrol/approval.php');
$context = context_system::instance();
$courseid = required_param('courseid',PARAM_INT);
$titleheading = get_string('nodename', 'enrol_approvalenrol');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($titleheading);
$PAGE->set_heading($titleheading);
$PAGE->requires->css(new moodle_url('/enrol/approvalenrol/styles.css'));
$PAGE->requires->js_call_amd('enrol_approvalenrol/approvalrequests', 'init');
echo $OUTPUT->header();
$requests = \enrol_approvalenrol\approval_enrol::get_approval_user_requests(\enrol_approvalenrol\approval_enrol::PENDING_REQUEST, $courseid);
$data = [];
$sn=1;
foreach($requests as $request){
        $tableobject = new stdClass();
        $tableobject->index = $sn;
        $tableobject->name = $request->firstname." ".$request->lastname;
        $tableobject->email = $request->email;
        $tableobject->actions = \enrol_approvalenrol\output\approval_enrol_renderer::render_request_action($request, $tableobject);
        $data[] = $tableobject;
        $sn++;   
}
$table = new html_table();
$table->head = ['#','Name','Email','Actions'];
$table->data = $data;
$loader = '';
$loader.= html_writer::start_div('loader_container');
$loader.= html_writer::div('','loader');
$loader.= html_writer::end_div();
echo $loader;   
echo html_writer::table($table);
echo $OUTPUT->footer();