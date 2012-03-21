<?php

$settings->add(new admin_setting_configtext('webpa_server',"WebPA Server",'Required. Full URL of your WebPA server. API calls will be prefixed with this.','',PARAM_URL));
$settings->add(new admin_setting_configpasswordunmask('webpa_init_password',"Initialisation password",'This must be the same as the SSO initialisation password on your WebPA server.',''));
$settings->add(new admin_setting_configtext('webpa_externalid',"External ID",'An identifier for this server on the WebPA server.',''));
$settings->add(new admin_setting_configselect('webpa_identifier',"User Identifier",'Which field in a user\'s profile should be used as their username on WebPA.','username',
	array('username' => get_string('username'), 'idnumber' => get_string('idnumber'), 'id' => 'User ID')));

