<?php

namespace enrol_approvalenrol\event;
use enrol_approvalenrol\event\approval_requests_updated;
use \enrol_approvalenrol\output\approval_enrol_renderer;


class observer {
     public static function process_request(approval_requests_updated $event){   
         $eventdata = (object)$event->get_data();
         if($eventdata) {
            $coursefullname = $event->get_coursefullname();
            $sender = (\core_user::get_noreply_user());
            $receiver = \core_user::get_user($eventdata->other['user']);
            $courseurl = (new \moodle_url('/course/view.php', ['id' => $event->courseid]))->out();
            $signaturename = get_config('enrol_approvalenrol', 'fromname')?:'The Moodle Team';
            $approvalrequest = $eventdata->other['requeststatus'];
            $messagedata = ['approvalstatus' => $approvalrequest, 'firstname' => $receiver->firstname, 'coursefullname' => $coursefullname, 'courseurl' => $courseurl, 'signaturename' => $signaturename];
            $message = approval_enrol_renderer::generate_approval_response($messagedata);
            $subject = ($approvalrequest == '1') ? get_string('requestapproved', 'enrol_approvalenrol'):get_string('requestdenied', 'enrol_approvalenrol');
            \enrol_approvalenrol\local\helper::send_message($sender, $receiver, $subject, $message);
         } else {
            debugging("Eventdata approval_requests_updated can't be fetched");
         }
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
