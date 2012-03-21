<?php //$Id: mod_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * This file defines the main webpa configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 */

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/local/sso/libsso.php');

class mod_webpa_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE, $CFG, $USER, $PAGE;
        $mform =& $this->_form;

		//$PAGE->require->js("$CFG->wwwroot/mod/webpa/validator.js");
		$PAGE->requires->js("/mod/webpa/jquery.js");
		$PAGE->requires->js("/mod/webpa/jquery-ui/js/jquery.ui.js");
		$PAGE->requires->js("/mod/webpa/modform.js?v=2");
		
		$PAGE->requires->css('/mod/webpa/jquery-ui/css/custom-theme/jquery.ui.css');
		
//-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('name', 'webpa'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

		$groupings = groups_get_all_groupings($COURSE->id);
		$options = array();
		foreach($groupings as $k => $v)
		{
			if(groups_get_grouping_members($k))
				$options[$k] = $v->name;
		}
		if(empty($options))
		print_error("You must have at least one grouping with members in it.");
		
		$mform->addElement('select','collection',get_string('grouping','webpa'),$options);
        $mform->addRule('collection', null, 'required', null, 'client');

		$site = sso_site_for_name('webpa');
		if ($site == false) {
			//we need to register the site
			if ( ! empty($CFG->webpa_init_password) ) {
				$site = sso_register_site("webpa",$CFG->webpa_server."/api/sso.php",$CFG->webpa_externalid,$CFG->webpa_init_password);
			} else {
				print_error("You need to set an initialisation password in settings.");
			}
		}
		
		$identifier = $CFG->webpa_identifier;
		$rslt = sso_api_call($site,$CFG->webpa_server."/api/api.php",array('externalid' => $CFG->webpa_externalid, 'action' => 'forms', 'owner' => $USER->$identifier));
		
		//$rslt may be false - this is okay, we just want an empty list
		//for that case, so we leave $forms as undefined
		$forms = @$rslt['forms'];
		
		$mform->addElement('select','form',get_string('form','webpa'),$forms,array('id' => 'form_select'));
		$mform->addRule('form','You must select a WebPA assessment form.','required');

		$site = sso_site_for_name('webpa');
		$ident = urlencode(sso_sign_on($site,$CFG->webpa_externalid,$USER->idnumber));
		$tempid = md5(mt_rand());
		$redirect = urlencode("tutors/forms/create/");
		
		$strcreate = get_string('create','webpa');
		$mform->addElement('static','createlabel','',"<a href=\"#\" onclick=\"$('<div>When you have finished creating your form, click OK.</div>').dialog({'title':'New Form','buttons':{'OK':function(){reloadForms('$CFG->wwwroot/mod/webpa/forms.php');$(this).dialog('close');}},'modal':true}); window.open('$CFG->webpa_server/login_check.php?sso_ident=$ident&redirect=$redirect','webpa','width=1024,height=768,scrollbars=yes')\">$strcreate</a>");
		
		$mform->addElement('htmleditor', 'description', get_string('description', 'webpa'));
        $mform->setType('description', PARAM_RAW);
		
		$mform->addElement('header','options',get_string('options','webpa'));
		
		$mform->addElement('date_time_selector','open',get_string('open','webpa'));
		$mform->addElement('date_time_selector','close',get_string('close','webpa'));
		$mform->addRule(array('open','close'),'Close date must be after open date','callback','someValidation','server');
		
		
		$mform->addElement('select','type',get_string('type','webpa'),array(1 => 'Self and peer assessment', 0 => 'Peer assessment only'));
		
		$mform->addElement('checkbox','allow_feedback',get_string('allow_feedback','webpa'));
		$mform->addHelpButton('allow_feedback','options','webpa');
		$mform->addElement('checkbox','student_feedback',get_string('student_feedback','webpa'));
		$mform->addHelpButton('student_feedback','options','webpa');
		$mform->addElement('text', 'feedback_name', get_string('feedback_name','webpa'));
		

//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
		$features = new stdClass;
        $features->groups = true;
        $features->groupings = true;
        $features->groupmembersonly = true;
        $this->standard_coursemodule_elements($features);
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }

	function definition_after_data() {
		
		parent::definition_after_data();
		$mform =& $this->_form;

		global $CFG, $USER, $DB;

		//if we're updating, we don't want to see the author screen
		if($id = $mform->getElementValue('update'))
		{
			$cm = get_coursemodule_from_id('webpa', $id);
			$webpa = $DB->get_record('webpa', array('id' => $cm->instance));
			
			$site = sso_site_for_name('webpa');
			$ident = urlencode(sso_sign_on($site,$CFG->webpa_externalid,$USER->idnumber));
			$redirect = urlencode("tutors/assessments/edit/edit_assessment.php?a=$webpa->assessment");
			
			$stredit = get_string('edit','webpa');
			$el = $mform->createElement('static','editlabel','',"<a target=\"_blank\" href=\"$CFG->webpa_server/login_check.php?sso_ident=$ident&amp;redirect=$redirect\">$stredit</a>");
			
			$mform->insertElementBefore($el,'createlabel');
			$mform->removeElement('collection');
			$mform->removeElement('createlabel');
			$mform->removeElement('form');
			$mform->removeElement('type');
			$mform->removeElement('options');
			$mform->removeElement('open');
			$mform->removeElement('close');
			$mform->removeElement('allow_feedback');
			$mform->removeElement('student_feedback');
			$mform->removeElement('feedback_name');
		}
		
	}
}

function someValidation ($value) {
	$open = $value[0];
	$close = $value[1];
	
	$opendate = mktime($open['hour'],$open['minute'],0,$open['month'],$open['day'],$open['year']);
	$closedate = mktime($close['hour'],$close['minute'],0,$close['month'],$close['day'],$close['year']);
	
	return ($opendate < $closedate);
}

