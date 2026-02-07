<?php

namespace enrol_approvalenrol;
use \enrol_approvalenrol\approval_enrol;
use \enrol_approvalenrol\local\approvalenrolrequests;

class approvaldashboard {

    private string $approvalrequeststatus;

    public function __construct(private string $coursefullname, private int $courseid, private int $numericstatus = 4) {
        $this->approvalrequeststatus = $this->get_request_status($this->numericstatus);
    }
    
    protected $approvalstatusarray = [
        '1' => 'Approved',
        '2' => 'Pending',
        '3' => 'Rejected',
        '4' => 'Total'
    ];


    public function get_title() {
        return get_string('approval_requests', 'enrol_approvalenrol', ['status' => ($this->numericstatus !=4 ? $this->approvalrequeststatus: ''), 'fullname' => $this->coursefullname]);
    }

    /**
     * Retrieves user enrolment requests for specific course based on approval requests.
     * @param int $requeststatus 
     * @param int $page
     * 
     * @return array $requests
     */

    public function get_approval_user_requests($requeststatus, $page):array{
        global $DB;

        if($this->courseid <=0 ){
            throw new \moodle_exception(get_string('invalid_courseid', 'enrol_approvalenrol'));
        }
        $params = ['courseid' => $this->courseid];
        $params['page'] = $page;
        if($requeststatus !== approval_enrol::REQUEST_ALL){
            $params['approval_status'] = $requeststatus;
        }

        try{
            $requests['data'] = approvalenrolrequests::get_requests_data($params);
            $requests['counts'] = $this->get_request_counts($this->courseid);
            return $requests?:[];
        }catch(\Exception $e) {
            throw new \moodle_exception('Failed to retrieve approval requests: ' . $e->getMessage());
        }
    }

    /**
     * Retrieves the following:
     * approved_counts: the count of users whose enrolment requests were accepted
     * rejected_counts: the count of users whose enrolment requests were rejected
     * pending_counts: the counts of users whose enrolment requests are still pending
     * total_counts: Total Enrolment Requests.
     * 
     * Retrieves enrolment request counts by approval status.
     * 
     * @return array Array containing:
     *               - approved_counts: Count of approved requests
     *               - rejected_counts: Count of rejected requests
     *               - pending_counts: Count of pending requests
     *               - total_counts: Total number of requests
     */
    public function get_request_counts():array{
        global $DB;

        $requestcounts = [
        'approved' => 0,
        'rejected' => 0,
        'pending' => 0,
        'total' => 0
        ];

        $sql = "SELECT case when approval_status = :accepted then 'approved'
                when approval_status = :rejected then 'rejected'
                else 'pending' end AS status
                ,count(approval_status) AS request_counts from {".
                approval_enrol::TABLE ."} 
                where courseid = :courseid group by approval_status";
        $requestsarray = $DB->get_records_sql($sql, [
                                    'courseid' => $this->courseid,
                                    'accepted' => approval_enrol::REQUEST_ACCEPTED,
                                    'rejected' => approval_enrol::REQUEST_REJECTED,
                                ]);
        foreach($requestsarray as $requests){
            $requestcounts[$requests->status] = $requests->request_counts?:0;
            $requestcounts['total'] += $requests->request_counts;
        } 

        return $requestcounts;
    }

    /**
     * Get count for the current approval status
     * 
     * @return int Count for the specified status, or 0 if not found
     * @throws \moodle_exception if approval status is invalid
     */
    public function get_requestcounts_by_status() {

            $recordcounts = $this->get_request_counts();
            if(!in_array(\core_text::strtolower($this->approvalrequeststatus), array_keys($recordcounts))) {
                throw new \moodle_exception('invalidstatus', 'enrol_approvalenrol');
            }
            
            return $recordcounts[\core_text::strtolower($this->approvalrequeststatus)]??0;
        
    }

    public function get_request_status($status) {
        return $this->approvalstatusarray[$status];
    }

}