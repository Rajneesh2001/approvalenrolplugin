<?php

defined('MOODLE_INTERNAL') || die();

   if($ADMIN->fulltree){ 
      global $DB;     
      $settings = new admin_settingpage('enrolsettingsapprovalenrol', get_string('pluginname', 'enrol_approvalenrol'));
      $settings->add(new admin_setting_configtext(
         'enrol_approvalenrol/fromname',
         get_string('fromname','enrol_approvalenrol'),
         get_string('fromname_desc', 'enrol_approvalenrol'),
         get_string('defaultfromname', 'enrol_approvalenrol'),
         PARAM_TEXT
      ));

      $settings->add(new admin_setting_configtext(
         'enrol_approval/fromemail',
         get_string('fromemail', 'enrol_approvalenrol'),
         get_string('fromemail_desc', 'enrol_approvalenrol'),
         get_string('defaultfromemail', 'enrol_approvalenrol', \core_user::get_noreply_user()),
      ));

      $userarray = array_keys($DB->get_records('user', null, '', 'email'));
      $settings->add(new admin_setting_configselect(
         'enrol_approvalenrol/approvers',
         get_string('approver', 'enrol_approvalenrol'),
         get_string('approver_desc', 'enrol_approvalenrol'),
         '',
         $userarray
      ));

      $ADMIN->add('enrolments', $settings);
   }