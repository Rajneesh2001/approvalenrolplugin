<?php

namespace enrol_approvalenrol\event;
use enrol_approvalenrol\event\approval_requests_updated;
use \core\event\config_log_created;
use \core\event\user_enrolment_deleted;


class observer {
     public static function process_request(approval_requests_updated $event){   
      $eventdata = (object)$event->get_data();
      $coursefullname = $event->get_coursefullname();
      $sender = (\core_user::get_noreply_user());
      $receiver = \core_user::get_user($eventdata->other['user']);
      $courseurl = (new \moodle_url('/course/view.php', ['id' => $event->courseid]))->out();
      $signaturename = get_config('enrol_approvalenrol', 'fromname')?:'The Moodle Team';
      if($eventdata){
         if($eventdata->other['requeststatus'] == '1'){
            $subject = get_string('requestapproved', 'enrol_approvalenrol');
            $message = <<<HTML
            <p>Hi <b>$receiver->firstname,</b></p>
            <p>We are pleased to inform you that your request to enrol in the Course <b>$coursefullname</b>  has been approved.<br>
            You can now access the course and begin your learning journey.<br>
            <a href="$courseurl">Click on this link to view the course</a>
            </p><br>
            <p><b>Best regards,<br>
            $signaturename</b></p>
            HTML;
         }else{
            $subject = get_string('requestdenied', 'enrol_approvalenrol');
            $message = <<<HTML
            <p>Hi <b>$receiver->firstname</b></p>
            <p>After Carefull consideration we regret to tell you your request for course <b>$coursefullname</b> enrolment request has been rejected. </p><br>
            <p>
            <b>Best regards,<br>
            $signaturename</b>
            </p>
            HTML;
      }
      }
            \enrol_approvalenrol\local\helper::send_message($sender, $receiver, $subject, $message);
     }

     /**
      * Reacts to email approval verify plugin config changes
      *
      * @param \core\event\config_log_created $event
      * @return void
      */
     public static function save_configdata(config_log_created $event):void {
         global $DB;
         $eventdata = $event->get_data();
         $config = $eventdata['other'];
         $contextsystem = \context_system::instance();
         if ($config['plugin'] !== 'enrol_approvalenrol') {
            return;
         }

         if (!$approverroleid = $DB->get_field('role', 'id', ['shortname' => 'approver'])) {
               debugging(get_string('noapproverrole', 'enrol_approvalenrol'), DEBUG_DEVELOPER);
               return;
         }

         // When 'approvers' is changed, $config->value contains the user ID for role assignment. 
         if ($config['name'] === 'approvers') {
            role_assign($approverroleid, $config['value'], $contextsystem->id, 'enrol_approvalenrol');
         }
         // For 'enableapproverreporting', $config->value is a boolean: 1 = enabled, 0 = disabled.
         if ($config['name'] === 'enableapproverreporting' && (int)$config['value'] !== (int)$config['oldvalue'] ) {
            $permission = (int)$config['value'] === 1 ? CAP_ALLOW : CAP_PROHIBIT;
            try {
            assign_capability('enrol/approvalenrol:manage', $permission, $approverroleid, $contextsystem->id, true);
            accesslib_clear_all_caches(true);
            } catch (\Exception $e) {
               debugging('Failed to assign approver capability '. $e->getMessage(), DEBUG_DEVELOPER);
               return;
            }
         }
     }

     public static function user_unenrolled(\core\event\user_enrolment_deleted $event):void {
         $eventdata = $event->get_data();
         $userid = $eventdata['other']['userenrolment']['userid'] ?? NULL;
         if(empty($eventdata['courseid']) || empty($userid)) {
            return;
         }
         \enrol_approvalenrol\local\approvalenrolrequests::remove_courseapprover($eventdata['courseid'], $userid);

         return;
     }

     /**
      * Toggle is_revoke setting according to user enrolment status
      * @param \core\event\user_enrolment_updated $event
      * @return void
      */
     public static function update_user_enrolment(\core\event\user_enrolment_updated $event):void {
       $eventdata = $event->get_data();

       $enrolment = $event->get_record_snapshot('user_enrolments', $event->objectid);

       
       $request = \enrol_approvalenrol\local\approvalenrolrequests::get_requests_data([
            'userid' => $enrolment->userid,
            'courseid' => $eventdata['courseid']
        ], single: true);

      if ($request === null || $request === false) {
        debugging('No approval request found for suspended user', DEBUG_DEVELOPER);
        return;
       }
    
      
      $request->is_revoked = $enrolment->status;

      \enrol_approvalenrol\local\approvalenrolrequests::update_enrol_approval_requestsdata($request);

      return;
     }
}
