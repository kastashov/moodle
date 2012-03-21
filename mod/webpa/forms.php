<?php

require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->dirroot/local/sso/libsso.php");

error_reporting(0);

require_login();

$site = sso_site_for_name("webpa");
$identifier = $CFG->webpa_identifier;
$rslt = sso_api_call($site,$CFG->webpa_server."/api/api.php",array('externalid' => $CFG->webpa_externalid, 'action' => 'forms', 'owner' => $USER->$identifier));
$forms = $rslt['forms'];
echo json_encode($forms);

