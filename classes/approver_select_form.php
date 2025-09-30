<?php

namespace enrol_approvalenrol;

require_once("$CFG->libdir/formslib.php");

class approver_select_form extends \moodleform
{

    public function __construct(private int $courseid)
    {
        parent::__construct();
    }
    
    public function definition()
    {

        $mform = $this->_form;

        $candidates = (\enrol_approvalenrol\local\approvalenrolrequests::fetch_approvers_candidates());
        $options[0] = '';
        foreach ($candidates as $candidate) {
            $options[$candidate->id] = $candidate->name . " " . $candidate->email;
        }
        $mform->addElement(
            'autocomplete',
            'userid',
            get_string('select_approver', 'enrol_approvalenrol'),
            $options,
            [
                'noselectionstring' => get_string('choose'),
                'class' => 'highlighted-rule',
                'style' => 'background-color: #eef',
            ]
        );
        $approverid = \enrol_approvalenrol\local\approvalenrolrequests::get_course_approver_field($this->courseid, 'userid');
        if(!empty($approverid)){
            $mform->setDefault('userid', $approverid);
        }
        $mform->addRule('userid', get_string('not_empty_userid', 'enrol_approvalenrol'), 'required', 'client');

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $this->courseid);
        $approverid = \enrol_approvalenrol\local\approvalenrolrequests::get_course_approver_field($this->courseid, 'userid');

        $mform->addElement('submit', 'approver_submit', get_string('submit', 'enrol_approvalenrol'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if(\enrol_approvalenrol\local\approvalenrolrequests::is_course_approver_exists($data['userid'], $data['courseid'])) {
            $errors['userid'] = get_string('same_approver_error', 'enrol_approvalenrol');
        }
        return $errors;
    }
}
