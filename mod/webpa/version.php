<?php // $Id: version.php,v 1.5.2.2 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * Code fragment to define the version of webpa
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author  Your Name <your@email.address>
 * @version $Id: version.php,v 1.5.2.2 2009/03/19 12:23:11 mudrd8mz Exp $
 * @package mod/webpa
 */

$module->release        = '1.0.1';  
$module->version        = 2011063001;  // The current module version (Date: YYYYMMDDXX)
$module->requires       = 2010121400;
$module->cron           = 0;           // Period for cron to check this module (secs)
$module->dependencies   = array('local_sso' => 2011082900); // local/sso should be installed as a dependency
