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

}