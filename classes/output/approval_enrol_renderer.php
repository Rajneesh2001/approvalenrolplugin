<?php

namespace enrol_approvalenrol\output;

class approval_enrol_renderer {

    public static function render_request_action(\stdClass $request, \stdClass $bodydata):string {
        global $OUTPUT;

        $actions = [
            'approve' => [
                'requeststatus' => \enrol_approvalenrol\approval_enrol::REQUEST_ACCEPTED,
                'icon' => 'check-solid',
                'alt' => 'Approve Request',
                'class' => 'approve',
                'idprefix' => 'approve-id:'
            ],
            'reject' => [
                'requeststatus' => \enrol_approvalenrol\approval_enrol::REQUEST_REJECTED,
                'icon' => 'xmark-solid',
                'alt' => 'Reject Request',
                'class' => 'reject',
                'idprefix' => 'reject-id:'
            ]
        ];
        $links = [];
        foreach ($actions as $action) {
            $url = new \moodle_url('/enrol/approvalenrol/approverequestprocess.php',[
                'courseid' => $request->courseid,
                'userid' => $request->userid,
                'requeststatus' => $action['requeststatus']
            ]);

            $icon = $OUTPUT->pix_icon($action['icon'], $action['alt'], 'enrol_approvalenrol',[
                'class' => $action['class'],
                'id' => $action['idprefix'].$request->userid,
                'data-courseid' => $request->courseid,
                'data-username' => $bodydata->name
            ]);

            $links[] = \html_writer::link($url, $icon);
        }

        return implode(' ', $links);
    }

    public static function render_notice_message(string $message):string{
        return \html_writer::div(
            $message,
            'alert alert-info'
        );
    }

    public static function generate_approval_response($data) {
        if(!isset($data['approvalstatus']) || !is_bool($data['approvalstatus'])) {
            throw new \moodle_exception('no approvalstatus received');
        }

        $firstname = isset($data['firstname'])?$data['firstname']:'FirstName';
        $coursefullname = isset($data['coursefullname'])?$data['coursefullname']:'CourseFullName';
        $courseurl = isset($data['courseurl'])?$data['courseurl']:'Courseurl';
        $signaturename = isset($data['signaturename'])?$data['signaturename']: $data['signaturename'];

        if($data['approvalstatus'] == '1') {
            
            $messagebody = "<p>We are pleased to inform you that your request to enrol in the Course <b>$coursefullname</b>  has been approved.<br>
            You can now access the course and begin your learning journey.<br>
            <a href=\"{$courseurl}\">Click on this link to view the course</a>
            </p><br>";
        } else {
            $messagebody = "<p>After Carefull consideration we regret to tell you your request for course <b>$coursefullname</b> enrolment request has been rejected. </p><br>";
        }

         $message = <<<HTML
            <p>Hi <b>$firstname</b></p>
            $messagebody
            <p>
            <b>Best regards,<br>
            $signaturename</b>
            </p>
            HTML;

      return $message;
    }



}