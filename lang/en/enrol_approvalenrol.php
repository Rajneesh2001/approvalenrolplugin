p<?php
$string['pluginname'] = 'Email Approval Verify';
$string['approvalenrol:enrol'] = 'Approvar can enable user enrolment ';
$string['approvalenrol:unenrol'] = 'Approvar can disbled user enrolment';
$string['submit'] = 'Enrol Me';
$string['firstname'] = 'First Name';
$string['lastname'] = 'Last Name';
$string['email'] = 'Email';
$string['enrol_success_message'] = 'User Enrolled Successfully';
$string['msg'] = 'Wait for Approval';
$string['nodename'] = 'Approval Requests';
$string['approval_requests']  = 'Requests {$a}';
$string['total_requests'] = 'Total Requests';
$string['event_approval_requests_updated'] = 'Approval Requests Update';
$string['messageprovider:approval_notifications'] = 'Notify Users About Their Approval Request';
$string['requestapproved'] = 'Request Approved';
$string['requestdenied'] = 'Request Denied';
$string['rejectmsg'] = 'Your enrolment request has been rejected by the approver.
Please contact the approver for further clarification or to resolve the issue.
';
$string['approve_req_dashboard'] = 'Approval Dashboard';
$string['approved_counts'] = 'Requests Approved';
$string['rejected_counts'] = 'Requests Rejected';
$string['pending_counts'] = 'Requests Pending';
$string['total_counts'] = 'Total Requests';
$string['fromname'] = 'Approver Name';
$string['fromname_desc'] = 'This Text will appear in the footer section of the email.';
$string['defaultfromname'] = 'The Moodle Team';
$string['fromemail'] = 'Approver Email';
$string['fromemail_desc'] = 'This Email will appear in the sender section of the email';
$string['defaultfromemail'] = '{$a->email}';
$string['successmessagebody'] = 'Approve Message Body';
$string['successmessagebody_desc'] = 'This content will be used as the main body of the email sent upon approval.';
$string['emailnotsend'] = 'Email not send, contact site administrator';
$string['pendingrequest'] = 'Request was already in Pending state';
$string['invalid_courseid'] = 'Invalid Course Id';
$string['approver'] = 'Approvers List';
$string['approver_desc'] = 'Select The User Email Whome you want to assign Approver role';
$string['approverrole'] = 'Approver';
$string['approverrole:desc'] = 'Grants the user permission to review and approve enrollment requests submitted by other users for a specific course.
';
$string['eventrequestcreated'] = 'Request Created';
$string['select_approver'] = 'Select Approver';
$string['invalidcourse'] = 'Course Id is not Valid';
$string['submit'] = 'Submit';
$string['select_user'] = 'Select User';
$string['nositeadminfound'] = 'No Site Admin Found, Please contact Admin';
$string['approvernotadd'] = 'Could Not add Approver, Please contact Admin';
$string['dmlerror'] = 'Insertion of approver failed: {$a}';
$string['approvercreated'] = 'Approver Created';
$string['approvercreated:desc'] = 'User {$a->userid} was successfully designated as an approver in Course {$a->courseid}.';
$string['approverupdated'] = 'Approver Updated';
$string['approverupdated:desc'] = 'The approver of Course {$a->courseid} has been successfully updated to User {$a->userid}';
$string['not_empty_userid'] = 'Userid is required';
$string['same_approver_error'] = 'Same Approver';
$string['noapproverrole'] = 'Unable to retrieve the Approver Role. Please contact your site administrator';
$string['approvalenrol:manage'] = 'View Approval Dashboard,Manage Approval Requests';
$string['cannotassigncapability'] = 'Capability {$a} cannot be assign. Please contact your site administrator';
$string['enableapproverreporting'] = 'Enable Course Approver Reporting';
$string['enableapproverreporting:desc'] = 'Allow course approvers to view and manage Approval dashboard and Requests';
$string['samevalues'] = 'Old name and New name cannot be same';
$string['pluginnotenabled'] = 'Plugin Email Approval Enrol is not enabled';
$string['course_enrol_req_sub'] = 'Course Enrolment Approval Request';
$string['course_enrol_req_body'] = 'Hi, <p>This is an approval request from the email address <b>{$a->email}</b> for the course <b>{$a->coursename}</b>.<br>
Please review the details and approve the request by clicking the link below:<br>
<a href="{$a->url}">{$a->url}</a></p>
<p> <b>Best Regards.</b> </p>
';