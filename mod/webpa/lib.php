<?php 

require_once("$CFG->dirroot/local/sso/libsso.php");
//require_once("$CFG->libroot/grouplib.php");

/**
 * Library of functions and constants for module webpa
 */

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $webpa An object from the form in mod_form.php
 * @return int The id of the newly inserted webpa record
 */
function webpa_add_instance($webpa) {

    global $CFG, $USER, $COURSE, $DB;

    # You may have to add extra stuff in here #
	
	$site = sso_site_for_name("webpa");
	
	//group collection creation
	$groups = $DB->get_records("groupings_groups", array("groupingid" => $webpa->collection));
	
	$identifier = $CFG->webpa_identifier;

	$groupdata = array();
	$groupdata['externalid'] = $CFG->webpa_externalid;
	$groupdata['action'] = 'groups-create';
	$groupdata['collection'] = groups_get_grouping_name($webpa->collection);
	$groupdata['owner'] = $USER->$identifier;
	$groupdata['groups'] = array();
	foreach($groups as $g)
	{
		$ret = array();
		$group = groups_get_members($g->groupid);
		foreach($group as $user)
		{
			$ret[] = array(
				'username' => $user->$identifier,
				'reference' => $user->$identifier,
				'email' => $user->email,
				'first_name' => $user->firstname,
				'last_name' => $user->lastname,
				'course' => $COURSE->shortname
				);
		}
		$groupname = groups_get_group_name($g->groupid);
		$groupdata['groups'][$groupname] = $ret;
	}
	
	$rslt = sso_api_call($site,$CFG->webpa_server."/api/api.php",$groupdata);
	
	if($rslt['status']=='failure')
	{
		print_r($rslt);
		die();
	}
	
	//assessment creation
	$asstdata = array();
	$asstdata['externalid'] = $CFG->webpa_externalid;
	$asstdata['action'] = 'assessment-create';
	$asstdata['name'] = $webpa->name;
	$asstdata['owner'] = $USER->$identifier;
	$asstdata['collection'] = $rslt['collection'];
	$asstdata['form'] = $webpa->form;
	$asstdata['open'] = $webpa->open;
	$asstdata['close'] = $webpa->close;
	$asstdata['introduction'] = $webpa->description;
	$asstdata['type'] = $webpa->type;
	$asstdata['allow_feedback'] = isset($webpa->allow_feedback);
	$asstdata['student_feedback'] = isset($webpa->student_feedback);
	$asstdata['feedback_name'] = $webpa->feedback_name;
	
	$rslt = sso_api_call($site,$CFG->webpa_server."/api/api.php",$asstdata);
	
	if($rslt['status']=='failure')
	{
		print_r($rslt);
		die();
	}
	
	$webpa->assessment = $rslt['assessment'];

    return $DB->insert_record('webpa', $webpa);
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $webpa An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function webpa_update_instance($webpa) {

	global $DB;

    $webpa->timemodified = time();
    $webpa->id = $webpa->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('webpa', $webpa);
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function webpa_delete_instance($id) {

	global $DB;

    if (! $webpa = $DB->get_record('webpa', array('id' => $id))) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! $DB->delete_records('webpa', array('id' => $webpa->id))) {
        $result = false;
    }

    return $result;
}


/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function webpa_user_outline($course, $user, $mod, $webpa) {
    return $return;
}


/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function webpa_user_complete($course, $user, $mod, $webpa) {
    return true;
}


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in webpa activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function webpa_print_recent_activity($course, $isteacher, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function webpa_cron () {
    return true;
}


/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of webpa. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $webpaid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function webpa_get_participants($webpaid) {
    return false;
}


/**
 * This function returns if a scale is being used by one webpa
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $webpaid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function webpa_scale_used($webpaid, $scaleid) {
    $return = false;

    //$rec = get_record("webpa","id","$webpaid","scale","-$scaleid");
    //
    //if (!empty($rec) && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}


/**
 * Checks if scale is being used by any instance of webpa.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any webpa
 */
function webpa_scale_used_anywhere($scaleid) {
	global $DB;
	
    if ($scaleid and $DB->record_exists('webpa', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

function webpa_supports($feature) {
	
	if ($feature == FEATURE_MOD_INTRO)
		return false;
	
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other webpa functions go here.  Each of them must have a name that
/// starts with webpa_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.

