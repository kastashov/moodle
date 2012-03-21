<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot."/local/sso/libsso.php");

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // webpa instance ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('webpa', $id)) {
       print_error('Course Module ID was incorrect');
    }

    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
       print_error('Course is misconfigured');
    }

    if (! $webpa = $DB->get_record('webpa', array('id' => $cm->instance))) {
       print_error('Course module is incorrect');
    }

} else {
   print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

global $USER;

$site = sso_site_for_name('webpa');
$identifier = $CFG->webpa_identifier;
$ident = sso_sign_on($site,$CFG->webpa_externalid,$USER->$identifier);

if($ident===false)
{
    print_error("There was an error trying to sign in to WebPA. Please refresh to try again. If you continue to get this error, please report it to the site administrator.");
}
else
{
    if(has_capability('mod/webpa:create',get_context_instance(CONTEXT_MODULE,$cm->id))) {
        $url = "{$CFG->webpa_server}/login_check.php?sso_ident=$ident&redirect=".urlencode("tutors/assessments/?tab=open");
    } else {
        $url = "{$CFG->webpa_server}/login_check.php?sso_ident=$ident&redirect=".urlencode("students/assessments/take/index.php?a=$webpa->assessment");
    }
    redirect($url);
}

