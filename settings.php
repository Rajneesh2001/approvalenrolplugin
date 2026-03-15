<?php

defined('MOODLE_INTERNAL') || die();
use enrol_approvalenrol\approval_enrol;

if($ADMIN->fulltree){ 
      global $DB,$CFG;

      require_once($CFG->dirroot . '/enrol/approvalenrol/locallib.php');
      
      $settings->add(new admin_setting_configtext(
         'enrol_approvalenrol/fromname',
         get_string('fromname','enrol_approvalenrol'),
         get_string('fromname_desc', 'enrol_approvalenrol'),
         get_string('defaultfromname', 'enrol_approvalenrol'),
         PARAM_TEXT
      ));

      $opitons = [];
      for($i=3;$i<15;$i++) {
         $options[$i] = $i;
      }
      $settings->add(new admin_setting_configselect(
         'enrol_approvalenrol/expirependingdays',
         get_string('expirependingdays', 'enrol_approvalenrol'),
         get_string('expirependingdays_desc', 'enrol_approvalenrol'),
         7,
         $options
      ));
      
      $options = [
         approval_enrol::REQUEST_ACCEPTED => get_string('requestapproved', 'enrol_approvalenrol'),
         approval_enrol::REQUEST_REJECTED => get_string('requestdenied', 'enrol_approvalenrol'),
         approval_enrol::REQUEST_EXPIRED => get_string('requestexpired', 'enrol_approvalenrol')
      ];

      $settings->add(new admin_setting_configselect(
         'enrol_approvalenrol/expirependingactions',
         get_string('expirependingactions', 'enrol_approvalenrol'),
         get_string('expirependingactions_desc', 'enrol_approvalenrol'),
         approval_enrol::REQUEST_EXPIRED,
         $options
      ));


   }

