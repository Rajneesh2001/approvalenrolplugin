<?php

namespace enrol_approvalenrol\event;
use enrol_approvalenrol\event\approval_requests_updated;


class observer {
     public static function process_request(approval_requests_updated $event){
      $eventdata = (object)$event->get_data();
      $coursefullname = $event->get_coursefullname();
      $sender = \core_user::get_noreply_user();
      $receiver = \core_user::get_user($eventdata->other['user']);
      $courseurl = (new \moodle_url('/course/view.php', ['id' => $event->courseid]))->out();
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
            The Moodle Team</b></p>
            HTML;
         }else{
            $subject = get_string('requestdenied', 'enrol_approvalenrol');
            $message = <<<HTML
            <p>Hi <b>$receiver->firstname</b></p>
            <p>After Carefull consideration we regret to tell you your request for course <b>$coursefullname</b> enrolment request has been rejected. </p><br>
            <p>
            <b>Best regards,<br>
            The Moodle Team</b>
            </p>
            HTML;
      }
      }
      static::send_message($sender, $receiver, $subject, $message);
     }

   private static function send_message(&$sender, &$receiver, &$subject, &$msg){
   $message = new \core\message\message();
   $message->component = 'enrol_approvalenrol';
   $message->name = 'approval_notifications';
   $message->notification = 1;
   $message->subject = $subject;
   $message->fullmessageformat = FORMAT_HTML;
   $message->userfrom = $sender;
   $message->userto = $receiver;
   $message->fullmessage = $msg;
   $message->fullmessagehtml = $msg;
   
   message_send($message);
}
}
