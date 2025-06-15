<?php
require_once("../../config.php");
require_once("$CFG->dirroot/enrol/approvalenrol/locallib.php");
defined('MOODLE_INTERNAL') || die();
require_login();
global $PAGE, $OUTPUT, $CFG;
$courseid = required_param('courseid', PARAM_INT);
if (!$courseid) {
    throw new moodle_exception('Course Id cannot be 0');
}

$url = new moodle_url('/enrol/approvalenrol/approval_dashboard.php', ['courseid' => $courseid]);


// Setting PAGE Object

$titleheading = get_string('approve_req_dashboard', 'enrol_approvalenrol');
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title($titleheading);
$PAGE->set_heading($titleheading);
$PAGE->requires->css(new moodle_url('/enrol/approvalenrol/styles.css'));
$data = \enrol_approvalenrol\approval_enrol::enrol_approvalenrol_requestcounts($courseid);
$chartcontext = [
    [
        'name' => get_string('approved_counts', 'enrol_approvalenrol'),
        'y' => (int)$data['approved_counts'],
        'color' => 'orange'
    ],
    [
        'name' => get_string('rejected_counts', 'enrol_approvalenrol'),  
        'y' => (int)$data['rejected_counts'],
        'color' => '#5e72e4'
    ],
    [
        'name' => get_string('pending_counts', 'enrol_approvalenrol'),
        'y' => (int)$data['pending_counts'],
        'color' => '#efd411'
    ]
];
$PAGE->requires->js_call_amd('enrol_approvalenrol/initchart', 'init', [$chartcontext]);
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('enrol_approvalenrol/approval_dashboard', $data);
echo $OUTPUT->footer();
