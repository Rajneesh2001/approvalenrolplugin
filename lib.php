<?php
use \enrol_approvalenrol\approval_enrol;

function enrol_approvalenrol_extend_navigation_course($parentnode,$course){
    if (\enrol_approvalenrol\local\approvalenrolrequests::is_enrol_approvalenrol_enabled($course->id)) {
        
        $parentnode->add(
         get_string('nodename','enrol_approvalenrol'),
         new moodle_url('/enrol/approvalenrol/approval.php',['courseid' => $course->id, 'status' => '2']),
         navigation_node::TYPE_CUSTOM,
         NULL,
         'approvalenrol',
         NULL
        );
        
        $parentnode->add(
            get_string('approve_req_dashboard', 'enrol_approvalenrol'),
            new moodle_url('/enrol/approvalenrol/approval_dashboard.php',['courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM,
            NULL,
            'approvalenrol__dashboard',
            NULL
        );
        $parentnode->add(
            get_string('select_approver', 'enrol_approvalenrol'),
            new moodle_url('/enrol/approvalenrol/select_approver.php', ['courseid' => $course->id]),
            navigation_node::NODETYPE_LEAF,
            NULL,
            'approvalenrol__approverselect',
            NULL
        );

    }
}
class enrol_approvalenrol_plugin extends enrol_plugin{

    const ENROL_INSTANCE_DISABLED = 0;
    const ENROL_INSTANCE_ENABLED = 1;
    const STUDENT_ROLE = 5;
    
    public function use_standard_editing_ui(){
        return true;
    }
    public function can_add_instance($courseid){
        return true;
    }
    
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = [];
        
        if($data['customint1'] == $data['customint2']) {
            $errors['customint1'] = get_string('autoapprove_error', 'enrol_approvalenrol');
        }

        return $errors;
    }

    public function enrol_self(stdClass $instance) {
        global $USER;
        $fullname = $USER->firstname. ' '. $USER->lastname;
        $this->enrol_user($instance, $USER->id, self::STUDENT_ROLE, time());
        core\notification::success(get_string('enrol_success_message', 'enrol_approvalenrol', $fullname));
    }

    public function edit_instance_form($instance, MoodleQuickForm $mform,  $context ) {
        $mform->addElement('advcheckbox', 'customint3', get_string('autoapprovereject', 'enrol_approvalenrol'));
        $mform->addHelpButton('customint3', 'autoapprovereject', 'enrol_approvalenrol');

        $mform->addElement('select', 'customint1', get_string('autoapprove', 'enrol_approvalenrol'), $this->get_status());
        $mform->addHelpButton('customint1', 'autoapprove', 'enrol_approvalenrol');
        $mform->hideIf('customint1', 'customint3', 'notchecked');

        $mform->addElement('select', 'customint2', get_string('autoreject', 'enrol_approvalenrol'), $this->get_status());
        $mform->addHelpButton('customint2', 'autoreject', 'enrol_approvalenrol');
        $mform->hideIf('customint2', 'customint3', 'notchecked');

    }


    public function enrol_page_hook($instance)
    {
        global $CFG, $OUTPUT, $USER;
        require_once($CFG->dirroot . '/enrol/approvalenrol/classes/approval_enrolment_form.php');
        require_once($CFG->dirroot. '/enrol/approvalenrol/locallib.php');

        $form = new approval_enrolment_form(null, ['instance' => $instance]);
        $approvalenrol = new approval_enrol((int)$instance->courseid, $USER->email,$USER->firstname,$USER->lastname, $USER->id);        
    
        if($form->is_submitted()){
            if($instance->customint3 && ($instance->customint1 || $instance->customint2)) {

                if($instance->customint1 == $instance->customint2) {
                throw new \moodle_exception('autoapprove_error', 'enrol_approvalenrol');
                }

                if($instance->customint1) {
                    $status = approval_enrol::REQUEST_ACCEPTED;
                } else if($instance->customint2) {
                    $status = approval_enrol::REQUEST_REJECTED;
                }
                $approvalenrol->create_request($status);
                
            }else{
                $approvalenrol->create_request();
            }            
        }
        $request_status = $approvalenrol->has_made_enrolment_request();
        
        switch($request_status){
            case approval_enrol::PENDING_REQUEST:
                return $OUTPUT->box(get_string('msg', 'enrol_approvalenrol'));
            case approval_enrol::REQUEST_REJECTED:
                return $OUTPUT->box(get_string('rejectmsg', 'enrol_approvalenrol'));
            case approval_enrol::REQUEST_ACCEPTED:
                $this->enrol_self($instance);
        }

        ob_start();
        $form->display();
        $output = ob_get_clean();
        return $OUTPUT->box($output);

    }
    
    public function allow_manage($instance){
        return true;
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
    */
    public function can_hide_show_instance($instance){
        $context = \context_course::instance($instance->courseid);
        return has_capability('enrol/approvalenrol:config', $context);
    }

    public function can_delete_instance($instance){
        return true;
    }

    public function can_self_enrol($instance,$checkuserenrolment = true){
        return true;
    }

    public function is_self_enrol_available($instance){
        return true;
    }
    public function allow_unenrol(stdclass $instance){
        return true;
    }
    public function upsert_user_data($form){
        $formdata = $form->get_data();
    }

    protected function get_status() {
        return [
            self::ENROL_INSTANCE_DISABLED => get_string('no', 'enrol_approvalenrol'),
            self::ENROL_INSTANCE_ENABLED => get_string('yes', 'enrol_approvalenrol')
        ];
    }
}