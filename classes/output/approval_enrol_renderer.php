<?php

namespace enrol_approvalenrol\output;

class approval_enrol_renderer {

    public static function render_request_action(\stdClass $request, \stdClass $bodydata):string {
        global $OUTPUT;

        $approverequrl = new \moodle_url('/enrol/approvalenrol/approverequestprocess.php',[
                'courseid' => $request->courseid,
                'userid' => $request->userid,
                'requeststatus' => \enrol_approvalenrol\approval_enrol::REQUEST_ACCEPTED,
            ]);;
        
        $approverstatus = $OUTPUT->pix_icon('check-solid','Approve Request','enrol_approvalenrol',      ['class'=>'approve','id'=>'approve-id:'. $request->userid,'data-courseid' => $request->courseid, 'data-username' => $bodydata->name]);

        $rejectstatus = $OUTPUT->pix_icon('xmark-solid','Reject Request','enrol_approvalenrol',['class'=>'reject','id'=>'reject-id:'. $request->userid,'data-courseid' => $request->courseid, 'data-username' => $bodydata->name]);

        return \html_writer::link($approverequrl,$approverstatus)." ".\html_writer::link($approverequrl,$rejectstatus);
    }

    public static function render_notice_message(string $message):string{
        return \html_writer::div(
            $message,
            'alert alert-info'
        );
    }

}