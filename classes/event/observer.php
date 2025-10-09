<?php

namespace enrol_approvalenrol\event;
use enrol_approvalenrol\event\approval_requests_updated;
use \core\event\config_log_created;


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
     public static function save_config_data(config_log_created $event):void {
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

         if ($config['name'] !== 'enableapproverreporting') {
            return;
         }

         if ( (int)$config['value'] !== (int)$config['oldvalue'] ) {
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
}
