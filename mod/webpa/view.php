<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

/**
 * This page prints a particular instance of webpa
 *
 * @package mod/webpa
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot."/local/sso/libsso.php");

$PAGE->requires->js('/mod/webpa/jquery.js');
$PAGE->requires->js('/mod/webpa/view.js');

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

} else if ($a) {
    if (! $webpa = $DB->get_record('webpa', array('id' => $a))) {
       print_error('Course module is incorrect');
    }
    if (! $course = $DB->get_record('course', array('id' => $webpa->course))) {
       print_error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('webpa', $webpa->id, $course->id)) {
       print_error('Course Module ID was incorrect');
    }

} else {
   print_error('You must specify a course_module ID or an instance ID');
}

$ctxt = get_context_instance(CONTEXT_MODULE,$cm->id);

require_login($course, true, $cm);

add_to_log($course->id, "webpa", "view", "view.php?id=$cm->id", "$webpa->id");

/// Print the page header
$strwebpas = get_string('modulenameplural', 'webpa');
$strwebpa  = get_string('modulename', 'webpa');

$PAGE->set_url("/mod/webpa/view.php",array("id"=>$id));
$PAGE->set_title(format_string($webpa->name));
$PAGE->set_heading($course->fullname);
$PAGE->set_cm($cm);
$PAGE->set_context($ctxt);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'webpa'));
echo $OUTPUT->header();

//print_header_simple(format_string($webpa->name), '', $navigation, '', '', true,
//              update_module_button($cm->id, $course->id, $strwebpa), navmenu($course, $cm));

/// Print the main part of the page

$gradebook = "";
if(has_capability('mod/webpa:viewmarks',get_context_instance(CONTEXT_MODULE,$cm->id)))
{
	$gradebook = <<<HTML
<br/><a onclick="showMarkSheets($id)" href="#"><img src="arrow.gif" />&nbsp;Import marks to Gradebook</a>
<div id="marks" style="display:none;">
	<table id="marks-table" style="display:none;">
		<tr>
			<th>Weighting</th>
			<td id="marks-weighting"></td>
		</tr>
		<tr>
			<th>Algorithm</th>
			<td id="marks-algorithm"></td>
		</tr>
		<tr>
			<th>Grading Type</th>
			<td id="marks-grading-type"></td>
		</tr>
		<tr>
			<th>Non-Completion Penalty</th>
			<td id="marks-penalty"></td>
		</tr>
	</table>
</div>
HTML;
}

$description = "";
if(!empty($webpa->description))
{
	$description = '<div class="description">'.$webpa->description.'</div>';
}

echo $OUTPUT->box_start('content-area');

echo <<<HTML
<h1>$webpa->name</h1>
$description
<blockquote>
<a href="login.php?id=$id" target="_blank"><img src="arrow.gif" />&nbsp;Log in to WebPA</a>
$gradebook
</blockquote>
HTML;

echo $OUTPUT->box_end();

/// Finish the page
echo $OUTPUT->footer($course);

