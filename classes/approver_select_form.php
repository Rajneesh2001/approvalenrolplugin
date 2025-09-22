<?php

namespace enrol_approvalenrol;
require_once("$CFG->libdir/formslib.php");

class approver_select_form extends \moodleform
{

    public function definition()
    {

        $mform = $this->_form;

        $options = [
           '',
           'Rajneesh'=>'Rajneesh',
           'raj' => 'raj'
        ];

        $mform->addElement(
            'autocomplete',
            'competency_rule',
            get_string('select_approver', 'enrol_approvalenrol'),
            $options,
            [
                'multiple' => false,
                'noselectionstring' => get_string('choose'),
                'class' => 'highlighted-rule',
                'style' => 'background-color: #eef'
            ]
        );

        $mform->addElement('submit', 'approver_submit', get_string('submit', 'enrol_approvalenrol'));
    }
}
