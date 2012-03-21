<?php

$capabilities = array(
	
	'mod/webpa:create' => array(
			'captype' => 'write',
			'contextlevel' => CONTEXT_MODULE,
			'archetypes' => array(
	            'teacher' => CAP_ALLOW,
	            'editingteacher' => CAP_ALLOW,
	            'manager' => CAP_ALLOW
	        )
		),
		
	'mod/webpa:attempt' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW
        	)
    	),
	
	'mod/webpa:viewmarks' => array(
		'captype' => 'read',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW
			)
		)
	
	);
	
