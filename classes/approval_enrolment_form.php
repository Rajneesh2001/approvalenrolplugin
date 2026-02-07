<?php


require_once("../config.php");
require_once("$CFG->libdir/formslib.php");
defined('MOODLE_INTERNAL') || die;


class approval_enrolment_form extends moodleform
{
    protected $instance;
    public function definition(){
        $mform = $this->_form;
        $instance = $this->_customdata['instance'];

        $mform->addElement('hidden','id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);


        $mform->addElement('submit', 'submitbutton', get_string('req_enrol', 'enrol_approvalenrol'));
    }

}