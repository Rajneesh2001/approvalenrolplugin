<?php
use \enrol_approvalenrol\approval_enrol;

/**
 * Extends the Moodle course navigation by adding nodes for the Approval Enrollment plugin.
 * 
 * This function checks if the 'approvalenrol' method is enabled for the specific course.
 * if enabled, it appends three custom nodes to the navigation tree:
 * 1. A node for the main approval page.
 * 2. A node for the approval request dashboard.
 * 3. A node for the selecting an approver.
 * 
 * @param navigation_node  $parentnode The parent navigation node to which new items are added.
 * @param stdClass $course The course object containing the course ID and other details.
 * @return void
 * @throw coding_exception If the navigation node cannot be created (handled by Moodle core).
 * 
 */
function enrol_approvalenrol_extend_navigation_course($parentnode,$course){
    if (\enrol_approvalenrol\local\approvalenrolrequests::is_enrol_approvalenrol_enabled($course->id)) {
        
        // Add "Approval" node
        $parentnode->add(
         get_string('nodename','enrol_approvalenrol'),
         new moodle_url('/enrol/approvalenrol/approval.php',['courseid' => $course->id, 'status' => '2']),
         navigation_node::TYPE_CUSTOM,
         NULL,
         'approvalenrol',
         NULL
        );
       
        // Add "Approval Dashboard" node
        $parentnode->add(
            get_string('approve_req_dashboard', 'enrol_approvalenrol'),
            new moodle_url('/enrol/approvalenrol/approval_dashboard.php',['courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM,
            NULL,
            'approvalenrol__dashboard',
            NULL
        );

        // Add "Select Approver" node
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
    
    /**
     * We don't invent our own UI/validation code path.
     *
     * @return boolean
     */
    public function use_standard_editing_ui(){
        return true;
    }

    /**
     * Currently always return true
     */
    public function can_add_instance($courseid){
        return true;
    }
    
    /**
     * Perform custom validation of the data used to edit the instance.
     * 
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param object $instance The instance loaded from the DB
     * @param context $context The context of the instance we are editing
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @return void
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = [];
        
        if($data['customint1'] == $data['customint2']) {
            $errors['customint1'] = get_string('autoapprove_error', 'enrol_approvalenrol');
        }

        return $errors;
    }

    /**
     * This function is used to enrol the user in the course and then to show a success message.
     * 
     * @param \stdClass
     * @return void
     */
    public function enrol_self(stdClass $instance) {
        global $USER;
        $fullname = $USER->firstname. ' '. $USER->lastname;
        $this->enrol_user($instance, $USER->id, self::STUDENT_ROLE, time());
        core\notification::success(get_string('enrol_success_message', 'enrol_approvalenrol', $fullname));
    }

    /** Add elements to the edit instance form
     * 
     * @param \stdClass $instance
     * @param MoodleQuickForm $mform
     * @param context $context
     * @return void
     */
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

    /**
     * This function creates the course enrolment form, checks the form if submitted,
     * creates approval requests and enrols users if necessary.
     * 
     * @param \stdClass $instance
     * @return string html text, usually a form in a text box
     */
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
        // Users with manage cap may tweak period and status.
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

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance){
        return true;
    }

    /**
     * Checks if users can enrol themselves.
     * 
     * @param \stdClass $instance
     * @param bool $checkusernerolment.Default true.
     * 
     * @return bool
     */
    public function can_self_enrol($instance,$checkuserenrolment = true){
        return true;
    }

    /**
     * Does this plugin support some way to self enrol?
     * This function doesn't check user capabilities. Use can_self_enrol to check capabilities.
     *
     * @param stdClass $instance enrolment instance
     * @return bool - true means "Enrol me in this course" link could be available
     */
    public function is_self_enrol_available($instance){
        return true;
    }
    public function allow_unenrol(stdclass $instance){
        // Users with unenrol cap may unenrol other users manually manually.
        return true;
    }
    // public function upsert_user_data($form){
    //     $formdata = $form->get_data();
    // }

    /**
     * Returns array of status (yes or no)
     * 
     * @return array
     */
    protected function get_status() {
        return [
            self::ENROL_INSTANCE_DISABLED => get_string('no', 'enrol_approvalenrol'),
            self::ENROL_INSTANCE_ENABLED => get_string('yes', 'enrol_approvalenrol')
        ];
    }
}