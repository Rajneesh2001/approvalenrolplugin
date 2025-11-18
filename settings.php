<?php

defined('MOODLE_INTERNAL') || die();

if($ADMIN->fulltree){ 
      global $DB;
      
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

      $usersarray = \enrol_approvalenrol\local\approvalenrolrequests::fetch_approvers_candidates();
      $options[0] = '';
      foreach ($usersarray as $user) {
         $options[$user->id] = $user->email;
      }
      $settings->add(new admin_setting_configselect_autocomplete(
         'enrol_approvalenrol/approvers',
         get_string('approver', 'enrol_approvalenrol'),
         get_string('approver_desc', 'enrol_approvalenrol'),
         '',
         $options
      ));
      $settings->add(new admin_setting_configcheckbox(
         'enrol_approvalenrol/enableapproverreporting',
         get_string('enableapproverreporting', 'enrol_approvalenrol'),
         get_string('enableapproverreporting:desc', 'enrol_approvalenrol'),
         false
      ));
   }

