<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot."/local/sso/libsso.php");
require_once($CFG->libdir.'/gradelib.php');

$id = required_param('id', PARAM_INT); // course_module ID, or
$sheet = optional_param('sheet', null, PARAM_INT); //timestamp of mark sheet
$getsheets = optional_param('getsheets',null,PARAM_INT);

if(!($sheet || $getsheets))
{
print_error('Sheet not specified.');
}

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
   print_error('You must specify a course_module ID');
}

require_login($course, true, $cm);

$ctxt = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/webpa:viewmarks',$ctxt);

$site = sso_site_for_name('webpa');
if($getsheets)
{
	$rslt = sso_api_call($site,$CFG->webpa_server."/api/api.php",array('externalid' => $CFG->webpa_externalid, 'action' => 'mark-sheets', 'assessment' => $webpa->assessment));
	if ($rslt['sheets']) {
		foreach($rslt['sheets'] as $k => $v)
		{
			$ret[strval(strtotime($k) * 1000)] = $v;
		}
		$rslt['sheets'] = $ret;
	}
	echo json_encode($rslt);
}
else
{	
	$rslt = sso_api_call($site,$CFG->webpa_server."/api/api.php",array('externalid' => $CFG->webpa_externalid, 'action' => 'marks', 'assessment' => $webpa->assessment, 'sheet' => $sheet));

	$grades = array();
	foreach($rslt['grades'] as $k => $v)
	{
		$user = $DB->get_record('user',array($CFG->webpa_identifier => $k));
		if($user)
		{
			$grade['userid'] = $user->id;
			$grade['rawgrade'] = 0.0 + $v;
			$grade['rawgrademax'] = 100;
			if (isset($rslt['comments'][$k]))
				$grade['feedback'] = "<ul><li>".implode("</li><li>",$rslt['comments'][$k])."</li></ul>";
			$grades[$user->id] = $grade;
		}
	}
	$rslt = grade_update('mod/webpa',$course->id,'mod','webpa',$webpa->id,0,$grades,array('itemname'=>$webpa->name));
	if($rslt==GRADE_UPDATE_OK)
	{
		redirect("$CFG->wwwroot/grade/report/grader/index.php?id=$course->id");
	}
	else
	{
	    print_error('<h2>Error $rslt submitting grades to gradebook.</h2>');
	}
}
