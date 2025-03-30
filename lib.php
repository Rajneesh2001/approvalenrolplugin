<?php

function enrol_approvalenrol_extend_navigation_course($parentnode,$course,$context){
    $parentnode->add(
         get_string('nodename','enrol_approvalenrol'),
         new moodle_url('/enrol/approvalenrol/approval.php',['courseid' => $course->id]),
         navigation_node::TYPE_CUSTOM,
         NULL,
         'approvalenrol',
         NULL
    );
}
class enrol_approvalenrol_plugin extends enrol_plugin{
    public function use_standard_editing_ui(){
        return true;
    }
    public function can_add_instance($courseid){
        return true;
    }
    public function add_instance($courseid,$fields=[]){
        $instanceid = parent::add_instance($courseid,$fields);
        return $instanceid;
    }
    public function enrol_self(stdClass $instance) {
        global $USER;
        $this->enrol_user($instance,$USER->id);
        core\notification::success(get_string('enrol_success_message', 'enrol_approvalenrol'));
    }
    public function enrol_page_hook($instance)
    {
        global $CFG, $OUTPUT, $USER;
        require_once($CFG->dirroot . '/enrol/approvalenrol/classes/approval_enrolment_form.php');
        require_once($CFG->dirroot. '/enrol/approvalenrol/locallib.php');

        $form = new approval_enrolment_form(null, ['instance' => $instance]);
        $requestdata = $form->get_data();

        $approvalenrol = new approval_enrol((int)$instance->courseid, $USER->email,$requestdata);
        $request_status = $approvalenrol->has_made_enrolment_request();

        if($request_status == PENDING_REQUEST){
            return $OUTPUT->box(get_string('msg', 'enrol_approvalenrol'));
        }

        if($form->is_submitted() && $requestdata){
            $requestid= $approvalenrol->create_request($requestdata);
        }

        ob_start();
        $form->display();
        $output = ob_get_clean();
        return $OUTPUT->box($output);

    }
    public function allow_manage($instance){
        return true;
    }
    public function can_hide_show_instance($instance){
        return parent::can_hide_show_instance($instance);
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

}