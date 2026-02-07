<?php

require_once("../../config.php");

defined('MOODLE_INTERNAL') || die;

$courseid = required_param('courseid', PARAM_INT);

$course = get_course($courseid);

require_login($courseid);

if(empty($courseid)) {
    throw new moodle_exception('invalidcourse', 'enrol_approvalenrol');
}

$context = context_course::instance($courseid);
require_capability('enrol/approvalenrol:managecourseapprover', $context);

$url = new moodle_url('/enrol/approvalenrol/select_approver.php');

$PAGE->set_url($url);
$PAGE->set_heading($course->fullname);
$PAGE->set_title($course->fullname);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);

$form = new enrol_approvalenrol\approver_select_form($courseid);

if ($form->get_data()) {
    \enrol_approvalenrol\local\approvalenrolrequests::upsert_course_approver($form->get_data());
}

echo $OUTPUT->header();

echo $form->display();

echo $OUTPUT->footer();