<?php


require_once("../config.php");
require_once("$CFG->libdir/formslib.php");

class approval_enrolment_form extends moodleform
{
    protected $instance;
    public function definition(){
        $mform = $this->_form;
        $instance = $this->_customdata['instance'];

        $mform->addElement('text', 'firstname', get_string('firstname','enrol_approvalenrol'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname','cannot be empty', 'required', null, 'client');

        $mform->addElement('text', 'lastname', get_string('lastname','enrol_approvalenrol'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname','cannot be empty', 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email','enrol_approvalenrol'));
        $mform->setType('email', PARAM_NOTAGS);
        $mform->addRule('email','Invalid Email', 'email', null, 'client');

        $mform->addElement('hidden','id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);


        $mform->addElement('submit', 'submitbutton', get_string('submit', 'enrol_approvalenrol'));
    }
    public function validation($data,$files){
        $errors = [];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $email = $data['email'];
//        $errors['email'] = 'This is an error';
        return $errors;
    }
}