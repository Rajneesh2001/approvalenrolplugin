<?php

require_once("../../config.php");

defined('MOODLE_INTERNAL') || die;

$courseid = required_param('courseid', PARAM_INT);

require_login($courseid);

if(empty($courseid)) {
    throw new moodle_exception('invalidcourse', 'enrol_approvalenrol');
}

$url = new moodle_url('/enrol/approvalenrol/select_approver.php');

$PAGE->set_url($url);
$PAGE->set_heading(get_string('select_approver', 'enrol_approvalenrol'));
$PAGE->set_title(get_string('select_approver', 'enrol_approvalenrol'));
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_course::instance($courseid));

$form = new enrol_approvalenrol\approver_select_form();

echo $OUTPUT->header();

echo $form->display();

echo $OUTPUT->footer();